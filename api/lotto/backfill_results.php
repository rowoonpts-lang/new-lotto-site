<?php
include_once __DIR__ . '/_common.php';
include_once G5_PATH . '/include/lotto_result.lib.php';

date_default_timezone_set('Asia/Seoul');
set_time_limit(0);
ignore_user_abort(true);

$is_cli = PHP_SAPI === 'cli';

$http_host = isset($_SERVER['HTTP_HOST'])
    ? preg_replace('/:[0-9]+$/', '', $_SERVER['HTTP_HOST'])
    : '';

$is_local_web = !$is_cli
    && in_array($http_host, array('127.0.0.1', 'localhost'), true);

// 개발 중에는 로컬 HTTP 또는 CLI에서만 실행합니다.
if (!$is_cli && !$is_local_web) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(array(
        'success' => false,
        'status' => 'forbidden',
        'message' => '로컬 환경에서만 실행할 수 있습니다.',
    ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$batch_count = 10;

if ($is_cli) {
    foreach ($argv ?? array() as $argument) {
        if (preg_match('/^--batches=([0-9]+)$/', $argument, $matches)) {
            $batch_count = (int) $matches[1];
        }
    }
} elseif (isset($_GET['batches'])) {
    $batch_count = (int) $_GET['batches'];
}

$batch_count = max(1, min(20, $batch_count));

$result_url = 'https://www.dhlottery.co.kr/lt645/result';
$cookie_file = tempnam(sys_get_temp_dir(), 'lottogpt_backfill_');

if ($cookie_file === false) {
    throw new RuntimeException('임시 쿠키 파일을 만들지 못했습니다.');
}

$response = array(
    'success' => false,
    'status' => 'error',
    'requested_batches' => $batch_count,
    'completed_batches' => 0,
    'inserted' => 0,
    'existing' => 0,
    'failed' => 0,
    'oldest_draw' => null,
    'message' => '',
);

try {
    // 결과 JSON 주소는 Referer만으로 조회할 수 있으므로
    // 연결 지연이 잦은 결과 페이지 사전 요청은 생략합니다.
    $saved = lotto_result_get_latest_saved();

    if (!isset($saved['draw_no']) || (int) $saved['draw_no'] < 1) {
        throw new RuntimeException('기준이 될 저장 회차가 없습니다.');
    }

    $table = lotto_result_table_name();
    $oldest = sql_fetch(
        " select draw_no from `{$table}` order by draw_no asc limit 1 ",
        false
    );

    $cursor = isset($oldest['draw_no'])
        ? (int) $oldest['draw_no']
        : (int) $saved['draw_no'];

    for ($batch = 1; $batch <= $batch_count; $batch++) {
        if ($cursor <= 1) {
            break;
        }

        $query = http_build_query(array(
            'srchDir' => 'older',
            'srchCursorLtEpsd' => $cursor,
        ));

        $json_url = 'https://www.dhlottery.co.kr'
            . '/lt645/selectPstLt645InfoNew.do?'
            . $query;

        $json_body = lotto_result_request(
            $json_url,
            $cookie_file,
            $result_url,
            1
        );

        $payload = json_decode($json_body, true);

        if (!is_array($payload) || json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(
                $cursor . '회 이전 결과가 올바른 JSON 형식이 아닙니다.'
            );
        }

        $list = isset($payload['data']['list'])
            && is_array($payload['data']['list'])
                ? $payload['data']['list']
                : array();

        if (!$list) {
            break;
        }

        $batch_draws = array();

        foreach ($list as $item) {
            $draw_no = isset($item['ltEpsd'])
                ? (int) $item['ltEpsd']
                : 0;

            if ($draw_no < 1 || $draw_no >= $cursor) {
                $response['failed']++;
                continue;
            }

            try {
                $result = lotto_result_validate_item($item, $draw_no);
                $result['source_url'] = $result_url;

                $save_status = lotto_result_save($result);

                if ($save_status === 'inserted') {
                    $response['inserted']++;
                } elseif ($save_status === 'exists') {
                    $response['existing']++;
                }

                $batch_draws[] = $draw_no;
            } catch (Throwable $item_error) {
                $response['failed']++;

                lotto_result_log(
                    $draw_no . '회 과거 데이터 저장 실패: '
                    . $item_error->getMessage()
                );
            }
        }

        if (!$batch_draws) {
            break;
        }

        $cursor = min($batch_draws);
        $response['completed_batches']++;
        $response['oldest_draw'] = $cursor;

        // 동행복권 서버에 연속 요청 부담을 줄입니다.
        usleep(500000);
    }

    $response['success'] = true;
    $response['status'] = $cursor <= 1 ? 'completed' : 'partial';
    $response['oldest_draw'] = $cursor;
    $response['message'] = sprintf(
        '과거 회차 저장: 신규 %d건, 기존 %d건, 실패 %d건, 현재 최소 %d회',
        $response['inserted'],
        $response['existing'],
        $response['failed'],
        $cursor
    );

    lotto_result_log($response['message']);
} catch (Throwable $e) {
    $response['message'] = '과거 회차 수집 실패: ' . $e->getMessage();
    lotto_result_log($response['message']);
} finally {
    @unlink($cookie_file);
}

if ($is_cli) {
    $output = $response['message'] . PHP_EOL;
    $response['success'] ? fwrite(STDOUT, $output) : fwrite(STDERR, $output);
    exit($response['success'] ? 0 : 1);
}

header('Content-Type: application/json; charset=utf-8');
http_response_code($response['success'] ? 200 : 500);

echo json_encode(
    $response,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);
