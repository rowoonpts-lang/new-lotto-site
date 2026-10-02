<?php

include_once __DIR__ . '/_common.php';
include_once G5_PATH . '/include/lotto_app_access.lib.php';
include_once G5_PATH . '/include/lotto_push.lib.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

function lottoPushTestRespond($statusCode, $payload)
{
    http_response_code((int) $statusCode);

    echo json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
    );

    exit;
}

lottoAppResolvePaidMember();

if (!$is_member) {
    lottoPushTestRespond(
        401,
        array(
            'success' => false,
            'status' => 'login_required',
            'message' => '로그인이 필요합니다.',
        )
    );
}

if (!lottoAppIsPaidMember($member)) {
    lottoPushTestRespond(
        403,
        array(
            'success' => false,
            'status' => 'paid_member_required',
            'message' => '유료회원 전용 서비스입니다.',
        )
    );
}

$method = isset($_SERVER['REQUEST_METHOD'])
    ? strtoupper((string) $_SERVER['REQUEST_METHOD'])
    : '';

if ($method !== 'POST') {
    header('Allow: POST');

    lottoPushTestRespond(
        405,
        array(
            'success' => false,
            'status' => 'method_not_allowed',
            'message' => 'POST 요청만 사용할 수 있습니다.',
        )
    );
}

$contentType = isset($_SERVER['CONTENT_TYPE'])
    ? strtolower(trim((string) $_SERVER['CONTENT_TYPE']))
    : '';

if (strpos($contentType, 'application/json') !== 0) {
    lottoPushTestRespond(
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
    lottoPushTestRespond(
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

if (!lottoPushRequestTokenIsValid($token)) {
    lottoPushTestRespond(
        403,
        array(
            'success' => false,
            'status' => 'invalid_token',
            'message' => '요청 인증정보가 올바르지 않습니다.',
        )
    );
}

$mbId = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

if ($mbId === '') {
    lottoPushTestRespond(
        401,
        array(
            'success' => false,
            'status' => 'member_invalid',
            'message' => '회원정보를 확인할 수 없습니다.',
        )
    );
}

$result = lottoPushSendToMember(
    $mbId,
    'LottoGPT 테스트 알림',
    '안드로이드 앱 알림이 정상적으로 연결되었습니다.',
    '/app/',
    'test'
);

if (empty($result['success'])) {
    lottoPushTestRespond(
        500,
        array(
            'success' => false,
            'status' => isset($result['status'])
                ? (string) $result['status']
                : 'send_failed',
            'message' => isset($result['error'])
                ? (string) $result['error']
                : '테스트 알림을 보내지 못했습니다.',
            'sent_count' => isset($result['sent_count'])
                ? (int) $result['sent_count']
                : 0,
            'failed_count' => isset($result['failed_count'])
                ? (int) $result['failed_count']
                : 0,
            'expired_count' => isset($result['expired_count'])
                ? (int) $result['expired_count']
                : 0,
        )
    );
}

lottoPushTestRespond(
    200,
    array(
        'success' => true,
        'status' => 'sent',
        'message' => '테스트 알림을 보냈습니다.',
        'sent_count' => isset($result['sent_count'])
            ? (int) $result['sent_count']
            : 0,
    )
);
