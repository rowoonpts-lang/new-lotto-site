<?php

if (PHP_SAPI === 'cli') {
    $_SERVER['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? '80';
    $_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 'localhost';
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI']
        ?? '/api/lotto/update_result.php';
    $_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR']
        ?? '127.0.0.1';
}

include_once __DIR__ . '/_common.php';
include_once G5_PATH . '/include/lotto_result.lib.php';

date_default_timezone_set('Asia/Seoul');

$is_cli = PHP_SAPI === 'cli';

$http_host = isset($_SERVER['HTTP_HOST'])
    ? preg_replace('/:[0-9]+$/', '', $_SERVER['HTTP_HOST'])
    : '';

$is_local_web = !$is_cli
    && in_array($http_host, array('127.0.0.1', 'localhost'), true);

$force = ($is_cli && in_array('--force', $argv ?? array(), true))
    || ($is_local_web && isset($_GET['force']) && $_GET['force'] === '1');

$now = new DateTimeImmutable('now', new DateTimeZone('Asia/Seoul'));
$weekday = (int) $now->format('w'); // 0: 일요일, 6: 토요일
$hour = (int) $now->format('G');

$is_schedule_window = ($weekday === 6 && $hour >= 21) || $weekday === 0;

if (!$force && !$is_schedule_window) {
    $message = '실행 시간 아님: 토요일 21시 이후 또는 일요일에만 실행합니다.';
    lotto_result_log($message);

    if ($is_cli) {
        echo $message . PHP_EOL;
        exit(0);
    }

    http_response_code(200);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array(
        'success' => true,
        'status' => 'skipped',
        'message' => $message,
    ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    $saved = lotto_result_get_latest_saved();
    $saved_draw = isset($saved['draw_no']) ? (int) $saved['draw_no'] : 0;

    $context = lotto_result_open_latest();

    try {
        $remote_draw = (int) $context['draw_no'];

        if ($saved_draw >= $remote_draw) {
            $message = $remote_draw . '회 결과가 이미 저장되어 있습니다.';
            lotto_result_log($message);

            $response = array(
                'success' => true,
                'status' => 'exists',
                'draw_no' => $remote_draw,
                'message' => $message,
            );
        } else {
            $result = lotto_result_fetch_draw($context);
            $save_status = lotto_result_save($result);

            if ($save_status !== 'inserted') {
                throw new RuntimeException('예상하지 못한 저장 상태: ' . $save_status);
            }

            $message = $remote_draw . '회 당첨결과를 저장했습니다.';
            lotto_result_log($message);

            $response = array(
                'success' => true,
                'status' => 'inserted',
                'draw_no' => $remote_draw,
                'draw_date' => $result['draw_date'],
                'message' => $message,
            );
        }
    } finally {
        lotto_result_close_context($context);
    }

    if ($is_cli) {
        echo $response['message'] . PHP_EOL;
        exit(0);
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(
        $response,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
} catch (Throwable $e) {
    $message = '당첨결과 동기화 실패: ' . $e->getMessage();
    lotto_result_log($message);

    if ($is_cli) {
        fwrite(STDERR, $message . PHP_EOL);
        exit(1);
    }

    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array(
        'success' => false,
        'status' => 'error',
        'message' => $message,
    ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}
