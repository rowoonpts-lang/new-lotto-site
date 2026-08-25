<?php

include_once __DIR__ . '/_common.php';

date_default_timezone_set('Asia/Seoul');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

$configFile = G5_DATA_PATH . '/lotto_cron_config.php';

if (!is_file($configFile)) {
    http_response_code(500);

    echo json_encode(
        array(
            'success' => false,
            'status' => 'config_missing',
            'message' => '자동실행 설정 파일이 없습니다.',
        ),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

include_once $configFile;

if (
    !defined('LOTTO_CRON_TOKEN')
    || trim((string) LOTTO_CRON_TOKEN) === ''
) {
    http_response_code(500);

    echo json_encode(
        array(
            'success' => false,
            'status' => 'config_invalid',
            'message' => '자동실행 인증 설정이 올바르지 않습니다.',
        ),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

$providedToken = isset($_POST['token'])
    ? trim((string) $_POST['token'])
    : '';

if (
    $providedToken === ''
    || !hash_equals(
        (string) LOTTO_CRON_TOKEN,
        $providedToken
    )
) {
    http_response_code(401);

    echo json_encode(
        array(
            'success' => false,
            'status' => 'unauthorized',
            'message' => '인증에 실패했습니다.',
        ),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

$job = isset($_GET['job'])
    ? trim((string) $_GET['job'])
    : 'health';

if ($job !== 'health') {
    http_response_code(400);

    echo json_encode(
        array(
            'success' => false,
            'status' => 'invalid_job',
            'message' => '아직 활성화되지 않은 작업입니다.',
        ),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

$now = new DateTimeImmutable(
    'now',
    new DateTimeZone('Asia/Seoul')
);

$dbCheck = sql_fetch(
    "select 1 as ok",
    false
);

echo json_encode(
    array(
        'success' => true,
        'status' => 'ok',
        'job' => 'health',
        'database' => (
            isset($dbCheck['ok'])
            && (int) $dbCheck['ok'] === 1
        ),
        'server_time' => $now->format(
            'Y-m-d H:i:s'
        ),
        'timezone' => 'Asia/Seoul',
    ),
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);
