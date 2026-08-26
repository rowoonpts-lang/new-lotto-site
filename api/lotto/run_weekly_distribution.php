<?php

if (PHP_SAPI === 'cli') {
    $_SERVER['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? '80';
    $_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 'localhost';
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI']
        ?? '/api/lotto/run_weekly_distribution.php';
    $_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR']
        ?? '127.0.0.1';
}

include_once __DIR__ . '/_common.php';
include_once G5_PATH . '/include/lotto_distribution.lib.php';

date_default_timezone_set('Asia/Seoul');

if (sql_query("set time_zone = '+09:00'", false) === false) {
    fwrite(
        STDERR,
        'DB 세션 시간대를 한국시간으로 설정하지 못했습니다.'
        . PHP_EOL
    );

    exit(1);
}

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
    'force',
    'dry-run',
));

$drawNo = isset($options['draw'])
    ? (int) $options['draw']
    : 0;

$force = array_key_exists('force', $options);
$dryRun = array_key_exists('dry-run', $options);

if ($drawNo < 1) {
    fwrite(
        STDERR,
        "사용법: php api/lotto/run_weekly_distribution.php "
        . "--draw=1239 [--dry-run] [--force]"
        . PHP_EOL
    );

    exit(1);
}

$now = new DateTimeImmutable(
    'now',
    new DateTimeZone('Asia/Seoul')
);

if (
    !$force
    && (int) $now->format('w') !== 0
) {
    fwrite(
        STDERR,
        '주간 일괄배분은 일요일에만 실행할 수 있습니다.'
        . PHP_EOL
    );

    exit(1);
}

$result = lottoDistributionRunWeeklyPaid(
    $drawNo,
    $now,
    $dryRun,
    'cron'
);

if (
    !isset($result['success'])
    || !$result['success']
) {
    fwrite(
        STDERR,
        '주간 유료회원 일괄배분 실패: '
        . (
            isset($result['error'])
                ? $result['error']
                : '알 수 없는 오류'
        )
        . PHP_EOL
    );

    exit(1);
}

echo '===== Lotto Weekly Paid Distribution =====' . PHP_EOL;
echo 'Draw        : ' . $drawNo . PHP_EOL;
echo 'Week        : '
    . $result['week_start']
    . ' ~ '
    . $result['week_end']
    . PHP_EOL;
echo 'Saturday    : excluded' . PHP_EOL;
echo 'Free member : excluded' . PHP_EOL;
echo 'Mode        : '
    . ($dryRun ? 'DRY RUN' : 'LIVE')
    . PHP_EOL;

foreach ($result['members'] as $member) {
    if ($member['status'] === 'skipped') {
        echo '[SKIP] '
            . $member['mb_id']
            . ' weekly distribution already exists'
            . PHP_EOL;

        continue;
    }

    if ($member['status'] === 'dry_run') {
        echo '[DRY] '
            . $member['mb_id']
            . ' '
            . (int) $member['count']
            . '개'
            . PHP_EOL;

        continue;
    }

    echo '[PAID] '
        . $member['mb_id']
        . ' '
        . (int) $member['count']
        . '개 rank '
        . (int) $member['start_rank_no']
        . '~'
        . (int) $member['end_rank_no']
        . PHP_EOL;
}

echo 'Eligible    : '
    . (int) $result['eligible_members']
    . PHP_EOL;

echo 'Processed   : '
    . (int) $result['processed_members']
    . PHP_EOL;

echo 'Skipped     : '
    . (int) $result['skipped_members']
    . PHP_EOL;

echo 'Combinations: '
    . (int) $result['combination_count']
    . PHP_EOL;

if ($dryRun) {
    echo 'DRY RUN 완료 - DB 배분 데이터는 변경하지 않았습니다.'
        . PHP_EOL;
} else {
    echo '주간 유료회원 일괄배분 완료' . PHP_EOL;
}

exit(0);
