<?php

if (PHP_SAPI === 'cli') {
    $_SERVER['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? '80';
    $_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 'localhost';
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI']
        ?? '/api/lotto/run_distribution.php';
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
    'member:',
    'day::',
    'count::',
    'force',
));

$drawNo = isset($options['draw'])
    ? (int) $options['draw']
    : 0;

$mbId = isset($options['member'])
    ? trim((string) $options['member'])
    : '';

$now = new DateTimeImmutable(
    'now',
    new DateTimeZone('Asia/Seoul')
);

$weekday = (int) $now->format('w');
$distributionDay = isset($options['day'])
    ? (int) $options['day']
    : $weekday;

$force = array_key_exists('force', $options);

if ($drawNo < 1 || $mbId === '') {
    fwrite(
        STDERR,
        "사용법: php api/lotto/run_distribution.php "
        . "--draw=1237 --member=sample01 [--day=5] [--count=5] [--force]"
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
$mbIdSql = sql_real_escape_string($mbId);

$memberRow = sql_fetch(
    "select
        a.mb_id,
        a.mb_name,
        a.mb_type,
        a.mb_datetime,
        a.mb_leave_date,
        b.start_date,
        b.end_date,
        b.num_mon,
        b.num_tue,
        b.num_wed,
        b.num_thur,
        b.num_fri,
        b.num_sat,
        b.use_num,
        b.recent_auto_date,
        b.recent_free_date,
        b.free_num_qty,
        b.free_num_date,
        b.recent_turn
     from g5_member a
     inner join g5_member_etc b
        on b.mb_id = a.mb_id
     where a.mb_id = '{$mbIdSql}'
     limit 1",
    false
);

if (
    !isset($memberRow['mb_id'])
    || trim((string) $memberRow['mb_id']) === ''
) {
    fwrite(STDERR, '회원을 찾을 수 없습니다.' . PHP_EOL);
    exit(1);
}

if (trim((string) $memberRow['mb_leave_date']) !== '') {
    fwrite(STDERR, '탈퇴 회원은 배분할 수 없습니다.' . PHP_EOL);
    exit(1);
}

$memberType = trim((string) $memberRow['mb_type']);

if ($memberType === '') {
    fwrite(STDERR, '회원 등급을 확인할 수 없습니다.' . PHP_EOL);
    exit(1);
}

$today = $now->format('Y-m-d');
$isFreeMember = $memberType === '무료회원';
$requestCount = 0;
$distributionMode = $isFreeMember ? 'free' : 'regular';

if ($isFreeMember) {
    $joinDate = substr(
        trim((string) $memberRow['mb_datetime']),
        0,
        10
    );

    $sixMonthsAgo = $now
        ->modify('-6 months')
        ->format('Y-m-d');

    if ($joinDate === '' || $joinDate < $sixMonthsAgo) {
        fwrite(
            STDERR,
            '무료회원 자동배분 대상 가입기간을 벗어났습니다.'
            . PHP_EOL
        );
        exit(1);
    }

    $recentFreeDate = trim(
        (string) $memberRow['recent_free_date']
    );
    $yesterday = $now
        ->modify('-1 day')
        ->format('Y-m-d');

    if (
        !$force
        && (
            $recentFreeDate === $today
            || $recentFreeDate === $yesterday
        )
    ) {
        fwrite(
            STDERR,
            '오늘 또는 어제 이미 무료번호를 받은 회원입니다.'
            . PHP_EOL
        );
        exit(1);
    }

    $allowedCount = 10;
    $freeNumDate = trim(
        (string) $memberRow['free_num_date']
    );
    $freeNumQty = isset($memberRow['free_num_qty'])
        ? (int) $memberRow['free_num_qty']
        : 0;

    if ($freeNumDate >= $today && $freeNumQty > 0) {
        $allowedCount = $freeNumQty;
    }

    $requestCount = isset($options['count'])
        ? (int) $options['count']
        : $allowedCount;

    if (
        $requestCount < 1
        || $requestCount > $allowedCount
    ) {
        fwrite(
            STDERR,
            '요청 수량은 무료회원 배분 가능 수량을 초과할 수 없습니다.'
            . PHP_EOL
        );
        exit(1);
    }
} else {
    $startDate = trim((string) $memberRow['start_date']);
    $endDate = trim((string) $memberRow['end_date']);

    if (
        $startDate === ''
        || $endDate === ''
        || $startDate > $today
        || $endDate < $today
    ) {
        fwrite(
            STDERR,
            '유료 서비스 기간에 포함되지 않는 회원입니다.'
            . PHP_EOL
        );
        exit(1);
    }

    $dayCount = isset($memberRow[$dayColumn])
        ? (int) $memberRow[$dayColumn]
        : 0;

    $requestCount = isset($options['count'])
        ? (int) $options['count']
        : $dayCount;

    if ($dayCount < 1) {
        fwrite(
            STDERR,
            '해당 요일의 배분 수량이 0입니다.'
            . PHP_EOL
        );
        exit(1);
    }

    if ($requestCount < 1 || $requestCount > $dayCount) {
        fwrite(
            STDERR,
            '요청 수량은 회원의 해당 요일 배분 수량을 초과할 수 없습니다.'
            . PHP_EOL
        );
        exit(1);
    }

    $weekTotal =
        (int) $memberRow['num_mon']
        + (int) $memberRow['num_tue']
        + (int) $memberRow['num_wed']
        + (int) $memberRow['num_thur']
        + (int) $memberRow['num_fri']
        + (int) $memberRow['num_sat'];

    $recentTurn = isset($memberRow['recent_turn'])
        ? (int) $memberRow['recent_turn']
        : 0;

    $useNum = $recentTurn === $drawNo
        ? (
            isset($memberRow['use_num'])
                ? (int) $memberRow['use_num']
                : 0
        )
        : 0;

    $leftNum = max(0, $weekTotal - $useNum);

    if ($requestCount > $leftNum) {
        fwrite(
            STDERR,
            '남은 주간 조합 수보다 요청 수량이 많습니다.'
            . PHP_EOL
        );
        exit(1);
    }

    if (
        !$force
        && trim((string) $memberRow['recent_auto_date']) === $today
    ) {
        fwrite(
            STDERR,
            '오늘 이미 자동 배분 처리된 회원입니다.'
            . PHP_EOL
        );
        exit(1);
    }
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
    echo '===== Lotto Member Distribution =====' . PHP_EOL;
    echo 'Draw        : ' . $drawNo . PHP_EOL;
    echo 'Member      : ' . $mbId . PHP_EOL;
    echo 'Member type : ' . $memberType . PHP_EOL;
    echo 'Mode        : ' . $distributionMode . PHP_EOL;
    echo 'Day         : ' . $distributionDay . PHP_EOL;
    echo 'Count       : ' . $requestCount . PHP_EOL;
    echo 'Started at  : ' . $now->format('Y-m-d H:i:s') . PHP_EOL;

    if ($isFreeMember) {
        $result = lottoFreeDistributionDistributeMember(
            $drawNo,
            $mbId,
            $requestCount,
            $distributionDay,
            'cron'
        );
    } else {
        $result = lottoDistributionDistributeMember(
            $drawNo,
            $mbId,
            $memberType,
            $requestCount,
            $distributionDay,
            'cron'
        );
    }

    if (
        !isset($result['success'])
        || !$result['success']
    ) {
        throw new RuntimeException(
            isset($result['error'])
                ? (string) $result['error']
                : '알 수 없는 배분 오류'
        );
    }

    echo 'Ranks       : '
        . (int) $result['start_rank_no']
        . '~'
        . (int) $result['end_rank_no']
        . PHP_EOL;

    foreach ($result['distributed'] as $row) {
        echo '#'
            . (int) $row['rank_no']
            . ' '
            . implode(',', $row['numbers'])
            . PHP_EOL;
    }

    echo '배분 완료' . PHP_EOL;
} catch (Throwable $e) {
    fwrite(
        STDERR,
        '배분 실행 실패: '
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
