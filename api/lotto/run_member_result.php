<?php

if (PHP_SAPI === 'cli') {
    $_SERVER['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? '80';
    $_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 'localhost';
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI']
        ?? '/api/lotto/run_member_result.php';
    $_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR']
        ?? '127.0.0.1';
}

include_once __DIR__ . '/_common.php';
include_once G5_PATH . '/include/lotto_member_result.lib.php';

date_default_timezone_set('Asia/Seoul');
set_time_limit(0);
ignore_user_abort(true);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(
        array(
            'success' => false,
            'status' => 'forbidden',
            'message' => 'CLI에서만 실행할 수 있습니다.',
        ),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

$options = getopt('', array('draw:'));
$drawNo = isset($options['draw']) ? (int) $options['draw'] : 0;

if ($drawNo < 1) {
    fwrite(
        STDERR,
        '사용법: php api/lotto/run_member_result.php --draw=1237'
        . PHP_EOL
    );
    exit(1);
}

$lockName = 'lotto_member_result_' . $drawNo;
$lockRow = sql_fetch(
    "select get_lock('" . sql_real_escape_string($lockName) . "', 0) as acquired",
    false
);

$lockAcquired = isset($lockRow['acquired'])
    && (int) $lockRow['acquired'] === 1;

if (!$lockAcquired) {
    fwrite(
        STDERR,
        '결과 계산 중단: 같은 회차의 다른 결과 작업이 실행 중입니다.'
        . PHP_EOL
    );
    exit(1);
}

$exitCode = 0;

try {
    echo '===== Lotto Member Result =====' . PHP_EOL;
    echo 'Draw        : ' . $drawNo . PHP_EOL;
    echo 'Started at  : ' . date('Y-m-d H:i:s') . PHP_EOL;

    $result = lottoMemberResultProcessDraw($drawNo);

    if (!isset($result['success']) || !$result['success']) {
        $status = isset($result['status'])
            ? (string) $result['status']
            : 'failed';
        $message = isset($result['error'])
            ? (string) $result['error']
            : '알 수 없는 결과 계산 오류';

        fwrite(
            STDERR,
            'Status      : ' . $status . PHP_EOL
            . '결과 계산 중단: ' . $message . PHP_EOL
        );

        $exitCode = 1;
    } else {
        echo 'Combinations: '
            . (int) $result['combination_count']
            . PHP_EOL;
        echo 'Checked     : '
            . (int) $result['checked_count']
            . PHP_EOL;
        echo 'Members     : '
            . (int) $result['member_count']
            . PHP_EOL;
        echo 'Winners     : '
            . (int) $result['winning_count']
            . PHP_EOL;

        foreach ($result['rank_counts'] as $rank => $count) {
            echo 'Rank ' . (int) $rank . '      : '
                . (int) $count
                . PHP_EOL;
        }

        echo '회원 당첨결과 계산 완료' . PHP_EOL;
    }
} catch (Throwable $e) {
    fwrite(
        STDERR,
        '결과 계산 실패: ' . $e->getMessage() . PHP_EOL
    );
    $exitCode = 1;
} finally {
    if ($lockAcquired) {
        sql_fetch(
            "select release_lock('"
            . sql_real_escape_string($lockName)
            . "') as released",
            false
        );
    }
}

exit($exitCode);
