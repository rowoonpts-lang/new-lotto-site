<?php

include_once __DIR__ . '/_common.php';
include_once G5_PATH . '/include/lotto_app_access.lib.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

function lottoPushSubscriptionRespond($statusCode, $payload)
{
    http_response_code((int) $statusCode);

    echo json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
    );

    exit;
}

function lottoPushSubscriptionTokenIsValid($token, $expire = 7200)
{
    $token = trim((string) $token);
    $dot = strpos($token, '.');

    if ($token === '' || $dot === false) {
        return false;
    }

    $time = (int) substr($token, 0, $dot);
    $hmac = substr($token, $dot + 1);

    if (
        $time < 1
        || $hmac === ''
        || abs(time() - $time) > (int) $expire
    ) {
        return false;
    }

    $expected = hash_hmac(
        'sha256',
        _get_token_secret() . '|csrf_token|' . $time,
        _get_token_key()
    );

    return hash_equals($expected, $hmac);
}

lottoAppResolvePaidMember();

if (!$is_member) {
    lottoPushSubscriptionRespond(
        401,
        array(
            'success' => false,
            'status' => 'login_required',
            'message' => '로그인이 필요합니다.',
        )
    );
}

if (!lottoAppIsPaidMember($member)) {
    lottoPushSubscriptionRespond(
        403,
        array(
            'success' => false,
            'status' => 'paid_member_required',
            'message' => '유료회원 전용 서비스입니다.',
        )
    );
}

$configFile = G5_DATA_PATH . '/lotto_push_config.php';

if (!is_file($configFile)) {
    lottoPushSubscriptionRespond(
        500,
        array(
            'success' => false,
            'status' => 'config_missing',
            'message' => '푸시 알림 설정 파일이 없습니다.',
        )
    );
}

include_once $configFile;

if (
    !defined('LOTTO_PUSH_VAPID_PUBLIC_KEY')
    || trim((string) LOTTO_PUSH_VAPID_PUBLIC_KEY) === ''
    || !defined('LOTTO_PUSH_VAPID_PRIVATE_KEY')
    || trim((string) LOTTO_PUSH_VAPID_PRIVATE_KEY) === ''
    || !defined('LOTTO_PUSH_VAPID_SUBJECT')
    || trim((string) LOTTO_PUSH_VAPID_SUBJECT) === ''
) {
    lottoPushSubscriptionRespond(
        500,
        array(
            'success' => false,
            'status' => 'config_invalid',
            'message' => '푸시 알림 설정이 올바르지 않습니다.',
        )
    );
}

$method = isset($_SERVER['REQUEST_METHOD'])
    ? strtoupper((string) $_SERVER['REQUEST_METHOD'])
    : '';

if ($method === 'GET') {
    lottoPushSubscriptionRespond(
        200,
        array(
            'success' => true,
            'public_key' => LOTTO_PUSH_VAPID_PUBLIC_KEY,
            'token' => get_token(),
        )
    );
}

if ($method !== 'POST') {
    header('Allow: GET, POST');

    lottoPushSubscriptionRespond(
        405,
        array(
            'success' => false,
            'status' => 'method_not_allowed',
            'message' => '지원하지 않는 요청 방식입니다.',
        )
    );
}

$contentType = isset($_SERVER['CONTENT_TYPE'])
    ? strtolower(trim((string) $_SERVER['CONTENT_TYPE']))
    : '';

if (strpos($contentType, 'application/json') !== 0) {
    lottoPushSubscriptionRespond(
        415,
        array(
            'success' => false,
            'status' => 'invalid_content_type',
            'message' => 'JSON 요청만 사용할 수 있습니다.',
        )
    );
}

$rawBody = file_get_contents('php://input');
$payload = json_decode((string) $rawBody, true);

if (!is_array($payload)) {
    lottoPushSubscriptionRespond(
        400,
        array(
            'success' => false,
            'status' => 'invalid_json',
            'message' => '요청 데이터가 올바르지 않습니다.',
        )
    );
}

$token = isset($payload['token'])
    ? (string) $payload['token']
    : '';

if (!lottoPushSubscriptionTokenIsValid($token)) {
    lottoPushSubscriptionRespond(
        403,
        array(
            'success' => false,
            'status' => 'invalid_token',
            'message' => '요청 인증정보가 올바르지 않습니다.',
        )
    );
}

$action = isset($payload['action'])
    ? trim((string) $payload['action'])
    : '';

if (
    $action !== 'subscribe'
    && $action !== 'unsubscribe'
) {
    lottoPushSubscriptionRespond(
        400,
        array(
            'success' => false,
            'status' => 'invalid_action',
            'message' => '푸시 알림 요청 방식이 올바르지 않습니다.',
        )
    );
}

$endpoint = isset($payload['endpoint'])
    ? trim((string) $payload['endpoint'])
    : '';

if (
    $endpoint === ''
    || strlen($endpoint) > 4096
    || filter_var($endpoint, FILTER_VALIDATE_URL) === false
    || strtolower((string) parse_url($endpoint, PHP_URL_SCHEME)) !== 'https'
) {
    lottoPushSubscriptionRespond(
        400,
        array(
            'success' => false,
            'status' => 'invalid_endpoint',
            'message' => '푸시 구독 주소가 올바르지 않습니다.',
        )
    );
}

$mbId = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

if ($mbId === '') {
    lottoPushSubscriptionRespond(
        401,
        array(
            'success' => false,
            'status' => 'member_invalid',
            'message' => '회원정보를 확인할 수 없습니다.',
        )
    );
}

$mbIdSql = sql_real_escape_string($mbId);
$endpointHash = hash('sha256', $endpoint);
$endpointSql = sql_real_escape_string($endpoint);

if ($action === 'unsubscribe') {
    $updateResult = sql_query(
        "update l_push_subscription
         set
            is_active = 0,
            last_seen_at = now()
         where endpoint_hash = '{$endpointHash}'
           and mb_id = '{$mbIdSql}'",
        false
    );

    if ($updateResult === false) {
        lottoPushSubscriptionRespond(
            500,
            array(
                'success' => false,
                'status' => 'database_error',
                'message' => '푸시 알림 해제정보를 저장하지 못했습니다.',
            )
        );
    }

    lottoPushSubscriptionRespond(
        200,
        array(
            'success' => true,
            'status' => 'unsubscribed',
        )
    );
}

$keys = isset($payload['keys']) && is_array($payload['keys'])
    ? $payload['keys']
    : array();

$p256dhKey = isset($keys['p256dh'])
    ? trim((string) $keys['p256dh'])
    : '';

$authKey = isset($keys['auth'])
    ? trim((string) $keys['auth'])
    : '';

if (
    $p256dhKey === ''
    || $authKey === ''
    || strlen($p256dhKey) > 255
    || strlen($authKey) > 255
) {
    lottoPushSubscriptionRespond(
        400,
        array(
            'success' => false,
            'status' => 'invalid_keys',
            'message' => '푸시 구독 키가 올바르지 않습니다.',
        )
    );
}

$expirationSql = 'NULL';

if (
    isset($payload['expirationTime'])
    && $payload['expirationTime'] !== null
    && $payload['expirationTime'] !== ''
) {
    if (!is_numeric($payload['expirationTime'])) {
        lottoPushSubscriptionRespond(
            400,
            array(
                'success' => false,
                'status' => 'invalid_expiration',
                'message' => '푸시 구독 만료정보가 올바르지 않습니다.',
            )
        );
    }

    $expirationTime = (int) $payload['expirationTime'];

    if ($expirationTime < 0) {
        lottoPushSubscriptionRespond(
            400,
            array(
                'success' => false,
                'status' => 'invalid_expiration',
                'message' => '푸시 구독 만료정보가 올바르지 않습니다.',
            )
        );
    }

    $expirationSql = (string) $expirationTime;
}

$p256dhKeySql = sql_real_escape_string($p256dhKey);
$authKeySql = sql_real_escape_string($authKey);

$userAgent = isset($_SERVER['HTTP_USER_AGENT'])
    ? substr((string) $_SERVER['HTTP_USER_AGENT'], 0, 255)
    : '';

$userAgentSql = sql_real_escape_string($userAgent);

$insertResult = sql_query(
    "insert into l_push_subscription
     set
        mb_id = '{$mbIdSql}',
        endpoint_hash = '{$endpointHash}',
        endpoint = '{$endpointSql}',
        p256dh_key = '{$p256dhKeySql}',
        auth_key = '{$authKeySql}',
        expiration_time = {$expirationSql},
        user_agent = '{$userAgentSql}',
        is_active = 1,
        last_seen_at = now(),
        last_error = ''
     on duplicate key update
        mb_id = values(mb_id),
        endpoint = values(endpoint),
        p256dh_key = values(p256dh_key),
        auth_key = values(auth_key),
        expiration_time = values(expiration_time),
        user_agent = values(user_agent),
        is_active = 1,
        last_seen_at = now(),
        last_error = ''",
    false
);

if ($insertResult === false) {
    lottoPushSubscriptionRespond(
        500,
        array(
            'success' => false,
            'status' => 'database_error',
            'message' => '푸시 구독정보를 저장하지 못했습니다.',
        )
    );
}

lottoPushSubscriptionRespond(
    200,
    array(
        'success' => true,
        'status' => 'subscribed',
    )
);
