<?php

if (PHP_SAPI === 'cli') {
    $_SERVER['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? 80;
    $_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 'localhost';
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI']
        ?? '/lpadmin/lucky/filter.run.test.php';
    $_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR']
        ?? '127.0.0.1';
}

include_once("_common.php");
include_once(
    G5_PATH . '/include/lotto_filter.lib.php'
);

$testDrawNo = 4000000003;
$sourceDrawNo = 1236;
$createdBy = 'cli_filter_test';

$sumMin = 100;
$sumMax = 190;

$expectedCandidateCount = 836859;
$expectedExcludedNumbers = '15,18,19,25,28,31';

$expectedRankOne = array(
    10,
    17,
    27,
    32,
    33,
    45,
);

/*
 * 이전 실패 테스트 데이터가 남아 있으면
 * 이 테스트 전용 회차만 정리한다.
 */
sql_query(
    "delete from l_filter_candidate
      where draw_no = '{$testDrawNo}'",
    false
);

sql_query(
    "delete from l_filter_run
      where draw_no = '{$testDrawNo}'
        and created_by = 'cli_filter_test'",
    false
);

echo "===== Lotto Filter Run DB Test =====\n";
echo "Test draw        : {$testDrawNo}\n";
echo "Source draw      : {$sourceDrawNo}\n";

$startedAt = microtime(true);

$result = lottoFilterExecuteRun(
    $testDrawNo,
    $sourceDrawNo,
    $createdBy,
    $sumMin,
    $sumMax
);

$elapsed = microtime(true) - $startedAt;

if (empty($result['success'])) {
    fwrite(
        STDERR,
        "FAIL: lottoFilterExecuteRun failed.\n"
    );

    if (!empty($result['error'])) {
        fwrite(
            STDERR,
            "Error: {$result['error']}\n"
        );
    }

    exit(1);
}

$runId = isset($result['run_id'])
    ? (int) $result['run_id']
    : 0;

if ($runId < 1) {
    fwrite(
        STDERR,
        "FAIL: invalid run id.\n"
    );
    exit(1);
}

$row = sql_fetch(
    "select *
       from l_filter_run
      where lfr_id = '{$runId}'
      limit 1",
    false
);

$candidateCheck = sql_fetch(
    "select
        count(*) as row_count,
        min(rank_no) as min_rank,
        max(rank_no) as max_rank,
        count(distinct rank_no) as distinct_rank_count
     from l_filter_candidate
     where draw_no = '{$testDrawNo}'",
    false
);

$rankOne = sql_fetch(
    "select
        score,
        num1,
        num2,
        num3,
        num4,
        num5,
        num6
     from l_filter_candidate
     where draw_no = '{$testDrawNo}'
       and rank_no = 1
     limit 1",
    false
);

$rowCount = isset($candidateCheck['row_count'])
    ? (int) $candidateCheck['row_count']
    : 0;

$minRank = isset($candidateCheck['min_rank'])
    ? (int) $candidateCheck['min_rank']
    : 0;

$maxRank = isset($candidateCheck['max_rank'])
    ? (int) $candidateCheck['max_rank']
    : 0;

$distinctRankCount =
    isset($candidateCheck['distinct_rank_count'])
        ? (int) $candidateCheck['distinct_rank_count']
        : 0;

$rankOneNumbers = array(
    isset($rankOne['num1'])
        ? (int) $rankOne['num1']
        : 0,
    isset($rankOne['num2'])
        ? (int) $rankOne['num2']
        : 0,
    isset($rankOne['num3'])
        ? (int) $rankOne['num3']
        : 0,
    isset($rankOne['num4'])
        ? (int) $rankOne['num4']
        : 0,
    isset($rankOne['num5'])
        ? (int) $rankOne['num5']
        : 0,
    isset($rankOne['num6'])
        ? (int) $rankOne['num6']
        : 0,
);

$failures = array();

if ((int) $row['draw_no'] !== $testDrawNo) {
    $failures[] = 'draw_no mismatch';
}

if (
    (int) $row['source_draw_no']
    !== $sourceDrawNo
) {
    $failures[] = 'source_draw_no mismatch';
}

if ($row['status'] !== 'filtered') {
    $failures[] = 'status is not filtered';
}

if (
    (int) $row['total_combinations']
    !== 8145060
) {
    $failures[] =
        'total_combinations mismatch';
}

if (
    (int) $row['candidate_count']
    !== $expectedCandidateCount
) {
    $failures[] =
        'candidate_count mismatch';
}

if (
    $row['excluded_numbers']
    !== $expectedExcludedNumbers
) {
    $failures[] =
        'excluded_numbers mismatch';
}

if ($rowCount !== $expectedCandidateCount) {
    $failures[] =
        'candidate row count mismatch';
}

if (
    $minRank !== 1
    || $maxRank !== $expectedCandidateCount
    || $distinctRankCount
        !== $expectedCandidateCount
) {
    $failures[] =
        'candidate rank validation failed';
}

if ($rankOneNumbers !== $expectedRankOne) {
    $failures[] =
        'rank 1 combination mismatch';
}

if (empty($row['started_at'])) {
    $failures[] = 'started_at is empty';
}

if (empty($row['filtered_at'])) {
    $failures[] = 'filtered_at is empty';
}

if (empty($row['ranked_at'])) {
    $failures[] = 'ranked_at is empty';
}

if (!empty($row['completed_at'])) {
    $failures[] =
        'completed_at must still be null';
}

if (!empty($row['last_error'])) {
    $failures[] =
        'last_error must be empty';
}

if ($row['created_by'] !== $createdBy) {
    $failures[] = 'created_by mismatch';
}

echo "Run ID           : {$runId}\n";
echo "Status           : {$row['status']}\n";

echo "Total            : "
    . number_format(
        (int) $row['total_combinations']
    )
    . "\n";

echo "Candidate        : "
    . number_format(
        (int) $row['candidate_count']
    )
    . "\n";

echo "Excluded         : "
    . $row['excluded_numbers']
    . "\n";

echo "Min rank         : {$minRank}\n";
echo "Max rank         : {$maxRank}\n";

echo "Distinct ranks   : "
    . number_format($distinctRankCount)
    . "\n";

echo "Rank 1 score     : "
    . (
        isset($rankOne['score'])
            ? $rankOne['score']
            : ''
    )
    . "\n";

echo "Rank 1 numbers   : "
    . implode(',', $rankOneNumbers)
    . "\n";

echo "Elapsed seconds  : "
    . number_format($elapsed, 4)
    . "\n";

if ($failures) {
    echo "Test data kept for inspection.\n";

    foreach ($failures as $failure) {
        fwrite(
            STDERR,
            "FAIL: {$failure}\n"
        );
    }

    exit(1);
}

echo "PASS: filter run was recorded correctly.\n";
echo "PASS: status is filtered.\n";
echo "PASS: candidate count is correct.\n";
echo "PASS: excluded numbers are correct.\n";
echo "PASS: candidate ranks are valid.\n";
echo "PASS: rank 1 combination is correct.\n";
echo "PASS: completed_at remains null.\n";

/*
 * 후보가 run보다 먼저 삭제되어야 한다.
 */
sql_query(
    "delete from l_filter_candidate
      where draw_no = '{$testDrawNo}'
        and lfr_id = '{$runId}'",
    false
);

sql_query(
    "delete from l_filter_run
      where lfr_id = '{$runId}'
        and created_by = 'cli_filter_test'",
    false
);

$remainingCandidate = sql_fetch(
    "select count(*) as cnt
       from l_filter_candidate
      where draw_no = '{$testDrawNo}'",
    false
);

$remainingRun = sql_fetch(
    "select count(*) as cnt
       from l_filter_run
      where lfr_id = '{$runId}'",
    false
);

$remainingCandidateCount =
    isset($remainingCandidate['cnt'])
        ? (int) $remainingCandidate['cnt']
        : 0;

$remainingRunCount =
    isset($remainingRun['cnt'])
        ? (int) $remainingRun['cnt']
        : 0;

if (
    $remainingCandidateCount !== 0
    || $remainingRunCount !== 0
) {
    fwrite(
        STDERR,
        "FAIL: test database cleanup failed.\n"
    );
    exit(1);
}

echo "PASS: test database rows were cleaned up.\n";
