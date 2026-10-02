<?php

if (!defined('_GNUBOARD_')) {
    exit;
}

function lottoPushLoadConfig()
{
    $configFile = G5_DATA_PATH . '/lotto_push_config.php';

    if (!is_file($configFile)) {
        return array(
            'success' => false,
            'status' => 'config_missing',
            'error' => '푸시 알림 설정 파일이 없습니다.',
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
        return array(
            'success' => false,
            'status' => 'config_invalid',
            'error' => '푸시 알림 설정이 올바르지 않습니다.',
        );
    }

    return array(
        'success' => true,
        'public_key' => trim((string) LOTTO_PUSH_VAPID_PUBLIC_KEY),
        'private_key' => trim((string) LOTTO_PUSH_VAPID_PRIVATE_KEY),
        'subject' => trim((string) LOTTO_PUSH_VAPID_SUBJECT),
    );
}

function lottoPushLoadRuntime()
{
    $autoload = G5_PATH . '/vendor/autoload.php';

    if (!is_file($autoload)) {
        return array(
            'success' => false,
            'status' => 'autoload_missing',
            'error' => 'Web Push Composer 라이브러리를 찾을 수 없습니다.',
        );
    }

    require_once $autoload;

    $requiredClasses = array(
        'Minishlink\\WebPush\\WebPush',
        'Minishlink\\WebPush\\Subscription',
        'GuzzleHttp\\Client',
    );

    foreach ($requiredClasses as $className) {
        if (!class_exists($className)) {
            return array(
                'success' => false,
                'status' => 'runtime_missing',
                'error' => 'Web Push 실행 라이브러리가 준비되지 않았습니다.',
            );
        }
    }

    return array(
        'success' => true,
    );
}

function lottoPushRequestTokenIsValid($token, $expire = 7200)
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

function lottoPushNormalizeTargetUrl($targetUrl)
{
    $targetUrl = trim((string) $targetUrl);

    if ($targetUrl === '') {
        return '/app/';
    }

    if (
        strlen($targetUrl) > 255
        || strpos($targetUrl, '/app/') !== 0
        || strpos($targetUrl, '//') === 0
    ) {
        return '';
    }

    return $targetUrl;
}

function lottoPushSafeResultMessage($message)
{
    $message = trim((string) $message);

    if ($message === '') {
        return '';
    }

    return mb_substr($message, 0, 500, 'UTF-8');
}

function lottoPushCreateSender(array $config)
{
    $auth = array(
        'VAPID' => array(
            'subject' => $config['subject'],
            'publicKey' => $config['public_key'],
            'privateKey' => $config['private_key'],
        ),
    );

    $defaultOptions = array(
        'TTL' => 300,
        'urgency' => 'normal',
    );

    $client = new \GuzzleHttp\Client(
        array(
            'timeout' => 20,
            \GuzzleHttp\RequestOptions::ALLOW_REDIRECTS => false,
        )
    );

    return new \Minishlink\WebPush\WebPush(
        $auth,
        $defaultOptions,
        $client
    );
}

function lottoPushSendToMember(
    $mbId,
    $title,
    $body,
    $targetUrl = '/app/',
    $sendCategory = 'general',
    $drawNo = 0,
    $eventKey = ''
) {
    $mbId = trim((string) $mbId);
    $title = trim((string) $title);
    $body = trim((string) $body);
    $sendCategory = trim((string) $sendCategory);
    $drawNo = max(0, (int) $drawNo);
    $eventKey = trim((string) $eventKey);
    $targetUrl = lottoPushNormalizeTargetUrl($targetUrl);

    if ($mbId === '') {
        return array(
            'success' => false,
            'status' => 'member_required',
            'error' => '푸시 알림 회원정보가 없습니다.',
        );
    }

    if ($title === '' || $body === '') {
        return array(
            'success' => false,
            'status' => 'message_required',
            'error' => '푸시 알림 제목과 내용을 확인해주세요.',
        );
    }

    if (strlen($title) > 120 || strlen($body) > 1000) {
        return array(
            'success' => false,
            'status' => 'message_too_long',
            'error' => '푸시 알림 내용이 너무 깁니다.',
        );
    }

    if ($targetUrl === '') {
        return array(
            'success' => false,
            'status' => 'invalid_target_url',
            'error' => '푸시 알림 이동 주소가 올바르지 않습니다.',
        );
    }

    if ($sendCategory === '' || strlen($sendCategory) > 30) {
        return array(
            'success' => false,
            'status' => 'invalid_category',
            'error' => '푸시 알림 유형이 올바르지 않습니다.',
        );
    }

    $runtime = lottoPushLoadRuntime();

    if (empty($runtime['success'])) {
        return $runtime;
    }

    $config = lottoPushLoadConfig();

    if (empty($config['success'])) {
        return $config;
    }

    $mbIdSql = sql_real_escape_string($mbId);

    $subscriptionResult = sql_query(
        "select
            lps_id,
            endpoint,
            p256dh_key,
            auth_key
         from l_push_subscription
         where mb_id = '{$mbIdSql}'
           and is_active = 1
         order by lps_id asc",
        false
    );

    if ($subscriptionResult === false) {
        return array(
            'success' => false,
            'status' => 'subscription_query_failed',
            'error' => '푸시 구독정보를 조회하지 못했습니다.',
        );
    }

    $subscriptions = array();

    while ($row = sql_fetch_array($subscriptionResult)) {
        $subscriptions[] = $row;
    }

    if (empty($subscriptions)) {
        return array(
            'success' => false,
            'status' => 'no_subscription',
            'error' => '활성화된 앱 알림 기기가 없습니다.',
            'sent_count' => 0,
            'failed_count' => 0,
            'expired_count' => 0,
        );
    }

    if ($eventKey === '') {
        try {
            $eventKey = bin2hex(random_bytes(16));
        } catch (Exception $e) {
            $eventKey = uniqid('push_', true);
        }
    }

    $eventHash = hash(
        'sha256',
        $sendCategory
        . '|'
        . $drawNo
        . '|'
        . $mbId
        . '|'
        . $eventKey
    );

    $eventHashSql = sql_real_escape_string($eventHash);
    $sendCategorySql = sql_real_escape_string($sendCategory);
    $targetUrlSql = sql_real_escape_string($targetUrl);

    $payload = json_encode(
        array(
            'title' => $title,
            'body' => $body,
            'url' => $targetUrl,
        ),
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
    );

    if ($payload === false) {
        return array(
            'success' => false,
            'status' => 'payload_failed',
            'error' => '푸시 알림 내용을 만들지 못했습니다.',
        );
    }

    try {
        $webPush = lottoPushCreateSender($config);
    } catch (Throwable $e) {
        return array(
            'success' => false,
            'status' => 'sender_init_failed',
            'error' => lottoPushSafeResultMessage($e->getMessage()),
        );
    }

    $sentCount = 0;
    $failedCount = 0;
    $expiredCount = 0;

    foreach ($subscriptions as $row) {
        $lpsId = isset($row['lps_id'])
            ? (int) $row['lps_id']
            : 0;

        $endpoint = isset($row['endpoint'])
            ? trim((string) $row['endpoint'])
            : '';

        $p256dhKey = isset($row['p256dh_key'])
            ? trim((string) $row['p256dh_key'])
            : '';

        $authKey = isset($row['auth_key'])
            ? trim((string) $row['auth_key'])
            : '';

        if (
            $lpsId < 1
            || $endpoint === ''
            || $p256dhKey === ''
            || $authKey === ''
        ) {
            $failedCount++;
            continue;
        }

        $historyInsert = sql_query(
            "insert ignore into l_push_history
             set
                lps_id = '{$lpsId}',
                mb_id = '{$mbIdSql}',
                send_category = '{$sendCategorySql}',
                draw_no = " . ($drawNo > 0 ? "'{$drawNo}'" : 'null') . ",
                event_hash = '{$eventHashSql}',
                target_url = '{$targetUrlSql}',
                push_status = 'pending',
                result_message = '',
                queued_at = now(),
                updated_at = now()",
            false
        );

        if ($historyInsert === false) {
            $failedCount++;
            continue;
        }

        $historyRow = sql_fetch(
            "select lph_id, push_status
             from l_push_history
             where lps_id = '{$lpsId}'
               and event_hash = '{$eventHashSql}'
             limit 1",
            false
        );

        $lphId = isset($historyRow['lph_id'])
            ? (int) $historyRow['lph_id']
            : 0;

        if ($lphId < 1) {
            $failedCount++;
            continue;
        }

        if (
            isset($historyRow['push_status'])
            && $historyRow['push_status'] !== 'pending'
        ) {
            continue;
        }

        $httpStatus = null;
        $resultMessage = '';
        $isExpired = false;
        $isSuccess = false;

        try {
            $subscription = \Minishlink\WebPush\Subscription::create(
                array(
                    'endpoint' => $endpoint,
                    'keys' => array(
                        'p256dh' => $p256dhKey,
                        'auth' => $authKey,
                    ),
                    'contentEncoding' => 'aes128gcm',
                )
            );

            $report = $webPush->sendOneNotification(
                $subscription,
                $payload
            );

            $isSuccess = $report->isSuccess();
            $isExpired = $report->isSubscriptionExpired();
            $resultMessage = lottoPushSafeResultMessage(
                $report->getReason()
            );

            $response = $report->getResponse();

            if ($response) {
                $httpStatus = (int) $response->getStatusCode();
            }
        } catch (Throwable $e) {
            $resultMessage = lottoPushSafeResultMessage(
                $e->getMessage()
            );
        }

        $httpStatusSql = $httpStatus !== null
            ? "'" . (int) $httpStatus . "'"
            : 'null';

        $resultMessageSql = sql_real_escape_string($resultMessage);

        if ($isSuccess) {
            $historyUpdated = sql_query(
                "update l_push_history
                 set
                    push_status = 'sent',
                    http_status = {$httpStatusSql},
                    result_message = '{$resultMessageSql}',
                    sent_at = now(),
                    updated_at = now()
                 where lph_id = '{$lphId}'",
                false
            );

            $subscriptionUpdated = sql_query(
                "update l_push_subscription
                 set
                    last_success_at = now(),
                    last_seen_at = now(),
                    last_error = ''
                 where lps_id = '{$lpsId}'",
                false
            );

            if (
                $historyUpdated === false
                || $subscriptionUpdated === false
            ) {
                $failedCount++;
                continue;
            }

            $sentCount++;
            continue;
        }

        $pushStatus = $isExpired ? 'expired' : 'failed';
        $pushStatusSql = sql_real_escape_string($pushStatus);

        $historyUpdated = sql_query(
            "update l_push_history
             set
                push_status = '{$pushStatusSql}',
                http_status = {$httpStatusSql},
                result_message = '{$resultMessageSql}',
                updated_at = now()
             where lph_id = '{$lphId}'",
            false
        );

        $subscriptionUpdated = sql_query(
            "update l_push_subscription
             set
                is_active = " . ($isExpired ? '0' : 'is_active') . ",
                last_failure_at = now(),
                last_error = '{$resultMessageSql}'
             where lps_id = '{$lpsId}'",
            false
        );

        if (
            $historyUpdated === false
            || $subscriptionUpdated === false
        ) {
            $failedCount++;
            continue;
        }

        if ($isExpired) {
            $expiredCount++;
        }

        $failedCount++;
    }

    if ($sentCount > 0 && $failedCount === 0) {
        $status = 'sent';
    } elseif ($sentCount > 0) {
        $status = 'partial_failed';
    } else {
        $status = 'failed';
    }

    return array(
        'success' => $sentCount > 0 && $failedCount === 0,
        'status' => $status,
        'sent_count' => $sentCount,
        'failed_count' => $failedCount,
        'expired_count' => $expiredCount,
    );
}
