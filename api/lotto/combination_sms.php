<?php

include_once __DIR__ . '/_common.php';
include_once G5_PATH . '/include/lotto_combination_sms.lib.php';

date_default_timezone_set('Asia/Seoul');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

function lottoCombinationSmsRespond($statusCode, $payload)
{
    http_response_code((int) $statusCode);
    echo json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
        | JSON_PRETTY_PRINT
    );
    exit;
}

$configFile = G5_DATA_PATH . '/lotto_cron_config.php';

if (!is_file($configFile)) {
    lottoCombinationSmsRespond(
        500,
        array(
            'success' => false,
            'status' => 'config_missing',
            'message' => '자동실행 설정 파일이 없습니다.',
        )
    );
}

include_once $configFile;

if (
    !defined('LOTTO_CRON_TOKEN')
    || trim((string) LOTTO_CRON_TOKEN) === ''
) {
    lottoCombinationSmsRespond(
        500,
        array(
            'success' => false,
            'status' => 'config_invalid',
            'message' => '자동실행 인증 설정이 올바르지 않습니다.',
        )
    );
}

$providedToken = isset($_POST['token'])
    ? trim((string) $_POST['token'])
    : '';

if (
    $providedToken === ''
    || !hash_equals((string) LOTTO_CRON_TOKEN, $providedToken)
) {
    lottoCombinationSmsRespond(
        401,
        array(
            'success' => false,
            'status' => 'unauthorized',
            'message' => '인증에 실패했습니다.',
        )
    );
}

$now = new DateTimeImmutable(
    'now',
    new DateTimeZone('Asia/Seoul')
);

$result = lottoCombinationSmsQueueWeekday($now);

$statusCode = !empty($result['success']) ? 200 : 500;
lottoCombinationSmsRespond($statusCode, $result);
