<?php

if (PHP_SAPI === 'cli') {
    $_SERVER['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? '80';
    $_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 'localhost';
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI']
        ?? '/api/lotto/run_distribution_all.php';
    $_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR']
        ?? '127.0.0.1';
}

include_once __DIR__ . '/_common.php';
include_once G5_PATH . '/include/lotto_distribution.lib.php';
include_once G5_PATH . '/include/lotto_free_distribution.lib.php';

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

$options = getopt('', array(
    'draw:',
    'day::',
    'force',
));

$drawNo = isset($options['draw'])
    ? (int) $options['draw']
    : 0;

$now = new DateTimeImmutable(
    'now',
    new DateTimeZone('Asia/Seoul')
);

$weekday = (int) $now->format('w');
$distributionDay = isset($options['day'])
    ? (int) $options['day']
    : $weekday;

$force = array_key_exists('force', $options);

if ($drawNo < 1) {
    fwrite(
        STDERR,
        "사용법: php api/lotto/run_distribution_all.php "
        . "--draw=1237 [--day=5] [--force]"
        . PHP_EOL
    );

    exit(1);
}

if ($distributionDay < 1 || $distributionDay > 6) {
    fwrite(
        STDERR,
        '배분 요일은 1(월)부터 6(토) 사이여야 합니다.'
        . PHP_EOL
    );
    exit(1);
}

if (!$force && $distributionDay !== $weekday) {
    fwrite(
        STDERR,
        '오늘 요일과 배분 요일이 다릅니다. 개발 검증이면 --force를 사용하세요.'
        . PHP_EOL
    );
    exit(1);
}

$dayColumns = array(
    1 => 'num_mon',
    2 => 'num_tue',
    3 => 'num_wed',
    4 => 'num_thur',
    5 => 'num_fri',
    6 => 'num_sat',
);

$dayColumn = $dayColumns[$distributionDay];
$today = $now->format('Y-m-d');
$yesterday = $now->modify('-1 day')->format('Y-m-d');
$sixMonthsAgo = $now->modify('-6 months')->format('Y-m-d');

$run = sql_fetch(
    "select
        lfr_id,
        status,
        candidate_count,
        distributed_at
     from l_filter_run
     where draw_no = '{$drawNo}'
     limit 1",
    false
);

if (
    !isset($run['lfr_id'])
    || (int) $run['lfr_id'] < 1
    || !isset($run['status'])
    || $run['status'] !== 'filtered'
    || !isset($run['candidate_count'])
    || (int) $run['candidate_count'] < 1
) {
    fwrite(
        STDERR,
        '배분 가능한 필터 결과가 없습니다.' . PHP_EOL
    );
    exit(1);
}

if (trim((string) $run['distributed_at']) !== '') {
    echo $drawNo
        . '회 전체 배분이 이미 완료되었습니다. '
        . $run['distributed_at']
        . PHP_EOL;
    exit(0);
}

$lockName = 'lotto_distribution_' . $drawNo;
$lockRow = sql_fetch(
    "select get_lock(
        '" . sql_real_escape_string($lockName) . "',
        0
    ) as acquired",
    false
);

$lockAcquired = isset($lockRow['acquired'])
    && (int) $lockRow['acquired'] === 1;

if (!$lockAcquired) {
    fwrite(
        STDERR,
        '배분 실행 중단: 같은 회차의 다른 배분 작업이 실행 중입니다.'
        . PHP_EOL
    );
    exit(1);
}

$exitCode = 0;

try {
    echo '===== Lotto Full Distribution =====' . PHP_EOL;
    echo 'Draw        : ' . $drawNo . PHP_EOL;
    echo 'Day         : ' . $distributionDay . PHP_EOL;
    echo 'Started at  : ' . $now->format('Y-m-d H:i:s') . PHP_EOL;

    /*
     * 기존 자동화 흐름에 맞춰 유료회원 regular 배분을 먼저 처리한다.
     * 가입일시, 아이디 순으로 처리하여 같은 조건에서 순서가 고정되게 한다.
     */
    $paidResult = sql_query(
        "select
            a.mb_id,
            a.mb_name,
            a.mb_type,
            a.mb_datetime,
            b.num_mon,
            b.num_tue,
            b.num_wed,
            b.num_thur,
            b.num_fri,
            b.num_sat,
            b.use_num,
            b.recent_turn,
            b.{$dayColumn} as day_count
         from g5_member a
         inner join g5_member_etc b
            on b.mb_id = a.mb_id
         where a.mb_leave_date = ''
           and a.mb_type <> ''
           and a.mb_type <> '무료회원'
           and b.start_date <= '{$today}'
           and b.end_date >= '{$today}'
           and b.{$dayColumn} > 0
           and (
                b.recent_auto_date is null
                or b.recent_auto_date <> '{$today}'
           )
         order by a.mb_datetime asc, a.mb_id asc",
        false
    );

    if ($paidResult === false) {
        throw new RuntimeException(
            '유료회원 배분 대상 조회에 실패했습니다.'
        );
    }

    $paidProcessed = 0;
    $paidCombinationCount = 0;

    while ($paid = sql_fetch_array($paidResult)) {
        $weekTotal =
            (int) $paid['num_mon']
            + (int) $paid['num_tue']
            + (int) $paid['num_wed']
            + (int) $paid['num_thur']
            + (int) $paid['num_fri']
            + (int) $paid['num_sat'];

        $recentTurn = isset($paid['recent_turn'])
            ? (int) $paid['recent_turn']
            : 0;

        $useNum = $recentTurn === $drawNo
            ? (int) $paid['use_num']
            : 0;

        $leftCount = max(
            0,
            $weekTotal - $useNum
        );

        $requestCount = min(
            (int) $paid['day_count'],
            $leftCount
        );

        if ($requestCount < 1) {
            continue;
        }

        $result = lottoDistributionDistributeMember(
            $drawNo,
            $paid['mb_id'],
            $paid['mb_type'],
            $requestCount,
            $distributionDay,
            'cron'
        );

        if (
            !isset($result['success'])
            || !$result['success']
        ) {
            throw new RuntimeException(
                '유료회원 '
                . $paid['mb_id']
                . ' 배분 실패: '
                . (
                    isset($result['error'])
                        ? $result['error']
                        : '알 수 없는 오류'
                )
            );
        }

        $paidProcessed++;
        $paidCombinationCount += (int) $result['count'];

        echo '[PAID] '
            . $paid['mb_id']
            . ' '
            . (int) $result['count']
            . '개 rank '
            . (int) $result['start_rank_no']
            . '~'
            . (int) $result['end_rank_no']
            . PHP_EOL;
    }

    /*
     * 무료회원은 기존 process.05.php 기준으로 최근 6개월 가입자 중
     * 오늘/어제 미배분 회원을 가입일시, 아이디 순으로 처리한다.
     * SMS 관련 상태는 이번 배분 단계에서 사용하지 않는다.
     */
    $freeResult = sql_query(
        "select
            a.mb_id,
            a.mb_name,
            a.mb_datetime,
            b.free_num_qty,
            b.free_num_date
         from g5_member a
         inner join g5_member_etc b
            on b.mb_id = a.mb_id
         where a.mb_type = '무료회원'
           and a.mb_leave_date = ''
           and date(a.mb_datetime) >= '{$sixMonthsAgo}'
           and (
                b.recent_free_date is null
                or (
                    b.recent_free_date <> '{$today}'
                    and b.recent_free_date <> '{$yesterday}'
                )
           )
         order by a.mb_datetime asc, a.mb_id asc",
        false
    );

    if ($freeResult === false) {
        throw new RuntimeException(
            '무료회원 배분 대상 조회에 실패했습니다.'
        );
    }

    $freeProcessed = 0;
    $freeCombinationCount = 0;

    while ($free = sql_fetch_array($freeResult)) {
        $requestCount = 10;
        $freeNumDate = trim((string) $free['free_num_date']);
        $freeNumQty = (int) $free['free_num_qty'];

        if ($freeNumDate >= $today && $freeNumQty > 0) {
            $requestCount = $freeNumQty;
        }

        $result = lottoFreeDistributionDistributeMember(
            $drawNo,
            $free['mb_id'],
            $requestCount,
            $distributionDay,
            'cron'
        );

        if (
            !isset($result['success'])
            || !$result['success']
        ) {
            throw new RuntimeException(
                '무료회원 '
                . $free['mb_id']
                . ' 배분 실패: '
                . (
                    isset($result['error'])
                        ? $result['error']
                        : '알 수 없는 오류'
                )
            );
        }

        $freeProcessed++;
        $freeCombinationCount += (int) $result['count'];

        echo '[FREE] '
            . $free['mb_id']
            . ' '
            . (int) $result['count']
            . '개 rank '
            . (int) $result['start_rank_no']
            . '~'
            . (int) $result['end_rank_no']
            . PHP_EOL;
    }

    /*
     * 실제로 처리할 대상이 남아 있지 않은 경우에만 회차 배분 완료 시각을 기록한다.
     */
    $paidRemaining = sql_fetch(
        "select count(*) as cnt
         from g5_member a
         inner join g5_member_etc b
            on b.mb_id = a.mb_id
         where a.mb_leave_date = ''
           and a.mb_type <> ''
           and a.mb_type <> '무료회원'
           and b.start_date <= '{$today}'
           and b.end_date >= '{$today}'
           and b.{$dayColumn} > 0
           and (
                b.recent_auto_date is null
                or b.recent_auto_date <> '{$today}'
           )
           and (
                (
                    coalesce(b.num_mon, 0)
                    + coalesce(b.num_tue, 0)
                    + coalesce(b.num_wed, 0)
                    + coalesce(b.num_thur, 0)
                    + coalesce(b.num_fri, 0)
                    + coalesce(b.num_sat, 0)
                ) - case
                    when b.recent_turn = '{$drawNo}'
                        then coalesce(b.use_num, 0)
                    else 0
                end
           ) > 0",
        false
    );

    $freeRemaining = sql_fetch(
        "select count(*) as cnt
         from g5_member a
         inner join g5_member_etc b
            on b.mb_id = a.mb_id
         where a.mb_type = '무료회원'
           and a.mb_leave_date = ''
           and date(a.mb_datetime) >= '{$sixMonthsAgo}'
           and (
                b.recent_free_date is null
                or (
                    b.recent_free_date <> '{$today}'
                    and b.recent_free_date <> '{$yesterday}'
                )
           )",
        false
    );

    $paidRemainingCount = isset($paidRemaining['cnt'])
        ? (int) $paidRemaining['cnt']
        : -1;

    $freeRemainingCount = isset($freeRemaining['cnt'])
        ? (int) $freeRemaining['cnt']
        : -1;

    if ($paidRemainingCount !== 0 || $freeRemainingCount !== 0) {
        throw new RuntimeException(
            '배분 대상이 남아 있어 전체 완료 처리하지 않습니다. '
            . '유료 '
            . $paidRemainingCount
            . '명, 무료 '
            . $freeRemainingCount
            . '명'
        );
    }

    $completeResult = sql_query(
        "update l_filter_run
         set distributed_at = now()
         where draw_no = '{$drawNo}'
           and distributed_at is null",
        false
    );

    if ($completeResult === false) {
        throw new RuntimeException(
            '회차 배분 완료 시각 저장에 실패했습니다.'
        );
    }

    $cursor = sql_fetch(
        "select last_rank_no, cycle_no
         from l_distribution_cursor
         where draw_no = '{$drawNo}'
         limit 1",
        false
    );

    echo 'Paid members : ' . $paidProcessed . PHP_EOL;
    echo 'Paid combos  : ' . $paidCombinationCount . PHP_EOL;
    echo 'Free members : ' . $freeProcessed . PHP_EOL;
    echo 'Free combos  : ' . $freeCombinationCount . PHP_EOL;
    echo 'Cursor rank  : '
        . (
            isset($cursor['last_rank_no'])
                ? (int) $cursor['last_rank_no']
                : 0
        )
        . PHP_EOL;
    echo '전체 배분 완료' . PHP_EOL;
} catch (Throwable $e) {
    fwrite(
        STDERR,
        '전체 배분 실패: '
        . $e->getMessage()
        . PHP_EOL
    );
    $exitCode = 1;
} finally {
    if ($lockAcquired) {
        sql_fetch(
            "select release_lock(
                '" . sql_real_escape_string($lockName) . "'
            ) as released",
            false
        );
    }
}

exit($exitCode);
