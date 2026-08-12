<?php

if (PHP_SAPI === 'cli') {
    $_SERVER['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? '80';
    $_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 'localhost';
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI']
        ?? '/api/lotto/run_filter.php';
    $_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR']
        ?? '127.0.0.1';
}

include_once __DIR__ . '/_common.php';
include_once G5_PATH . '/include/lotto_filter.lib.php';

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
        JSON_UNESCAPED_UNICODE
        | JSON_UNESCAPED_SLASHES
    );

    exit;
}

$force = in_array(
    '--force',
    $argv ?? array(),
    true
);

$lockName = 'lotto_filter_weekly_run';

$lockRow = sql_fetch(
    "select get_lock(
        '" . sql_escape_string($lockName) . "',
        0
    ) as acquired",
    false
);

$lockAcquired = isset($lockRow['acquired'])
    && (int) $lockRow['acquired'] === 1;

if (!$lockAcquired) {
    fwrite(
        STDERR,
        "필터 실행 중단: 다른 필터 작업이 이미 실행 중입니다."
        . PHP_EOL
    );

    exit(1);
}

$exitCode = 0;

try {
    do {
    $now = new DateTimeImmutable(
        'now',
        new DateTimeZone('Asia/Seoul')
    );

    /*
     * 정상 자동 실행은 일요일만 허용한다.
     * 개발/복구 목적의 --force 실행은 요일 검사를 건너뛴다.
     */
    $weekday = (int) $now->format('w');

    if (!$force && $weekday !== 0) {
        echo '실행 시간 아님: 일요일에만 필터를 실행합니다.'
            . PHP_EOL;

        break;
    }

    /*
     * 최신 저장 당첨결과를 기준 회차로 사용한다.
     */
    $latest = sql_fetch(
        "select
            draw_no,
            draw_date
         from g5_lotto_result
         order by draw_no desc
         limit 1",
        false
    );

    $sourceDrawNo = isset($latest['draw_no'])
        ? (int) $latest['draw_no']
        : 0;

    $sourceDrawDate = isset($latest['draw_date'])
        ? trim((string) $latest['draw_date'])
        : '';

    if ($sourceDrawNo < 1 || $sourceDrawDate === '') {
        throw new RuntimeException(
            '최신 당첨결과를 확인하지 못했습니다.'
        );
    }

    /*
     * 정상 일요일 실행에서는 바로 전날인 토요일 결과가
     * DB에 저장돼 있어야 한다.
     *
     * --force 실행은 개발/복구용이므로 이 검사를 건너뛴다.
     */
    if (!$force) {
        $expectedDrawDate = $now
            ->modify('-1 day')
            ->format('Y-m-d');

        if ($sourceDrawDate !== $expectedDrawDate) {
            throw new RuntimeException(
                '최신 당첨결과가 이번 주 토요일 결과가 아닙니다. '
                . '현재 최신 결과: '
                . $sourceDrawNo
                . '회 '
                . $sourceDrawDate
                . ', 예상 날짜: '
                . $expectedDrawDate
            );
        }
    }

    $targetDrawNo = $sourceDrawNo + 1;

    /*
     * 이미 정상 필터 결과가 있으면 다시 만들지 않는다.
     */
    $existingRun = sql_fetch(
        "select
            lfr_id,
            status,
            candidate_count
         from l_filter_run
         where draw_no = '{$targetDrawNo}'
         limit 1",
        false
    );

    if (
        isset($existingRun['lfr_id'])
        && (int) $existingRun['lfr_id'] > 0
        && $existingRun['status'] === 'filtered'
        && (int) $existingRun['candidate_count'] > 0
    ) {
        echo $targetDrawNo
            . '회 필터 결과가 이미 존재합니다. '
            . '후보 '
            . number_format(
                (int) $existingRun['candidate_count']
            )
            . '건'
            . PHP_EOL;

        break;
    }

    /*
     * 관리자가 저장한 총합 범위를 사용한다.
     */
    $sumRange = lottoFilterGetSumRange();

    $sumMin = isset($sumRange['min'])
        ? (int) $sumRange['min']
        : 100;

    $sumMax = isset($sumRange['max'])
        ? (int) $sumRange['max']
        : 190;

    if (
        $sumMin < 21
        || $sumMax > 255
        || $sumMin > $sumMax
    ) {
        throw new RuntimeException(
            '저장된 총합 필터 범위가 올바르지 않습니다.'
        );
    }

    echo '===== Lotto Weekly Filter ====='
        . PHP_EOL;

    echo 'Source draw : '
        . $sourceDrawNo
        . PHP_EOL;

    echo 'Target draw : '
        . $targetDrawNo
        . PHP_EOL;

    echo 'Sum range   : '
        . $sumMin
        . '~'
        . $sumMax
        . PHP_EOL;

    echo 'Started at  : '
        . $now->format('Y-m-d H:i:s')
        . PHP_EOL;

    $startedAt = microtime(true);

    $result = lottoFilterExecuteRun(
        $targetDrawNo,
        $sourceDrawNo,
        'cron',
        $sumMin,
        $sumMax
    );

    $elapsed = microtime(true) - $startedAt;

    if (
        !isset($result['success'])
        || !$result['success']
    ) {
        $error = isset($result['error'])
            ? (string) $result['error']
            : '알 수 없는 필터 실행 오류';

        throw new RuntimeException($error);
    }

    echo 'Status      : filtered'
        . PHP_EOL;

    echo 'Candidates  : '
        . number_format(
            (int) $result['candidate_count']
        )
        . PHP_EOL;

    echo 'Excluded    : '
        . implode(
            ',',
            isset($result['excluded_numbers'])
                ? $result['excluded_numbers']
                : array()
        )
        . PHP_EOL;

    echo 'Elapsed     : '
        . number_format($elapsed, 4)
        . ' seconds'
        . PHP_EOL;

    echo '필터 실행 완료'
        . PHP_EOL;

        break;
    } while (false);
} catch (Throwable $e) {
    fwrite(
        STDERR,
        '필터 실행 실패: '
        . $e->getMessage()
        . PHP_EOL
    );

    $exitCode = 1;
} finally {
    if ($lockAcquired) {
        sql_fetch(
            "select release_lock(
                '" . sql_escape_string($lockName) . "'
            ) as released",
            false
        );
    }
}

exit($exitCode);
