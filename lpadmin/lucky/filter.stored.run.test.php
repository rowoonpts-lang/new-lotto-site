<?php

$_SERVER['SERVER_PORT'] = '80';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

include_once("_common.php");
include_once(
    G5_PATH . "/include/lotto_filter.lib.php"
);

$testDrawNo = 4000000002;
$sourceDrawNo = 1236;
$expectedCandidateCount = 836859;
$expectedExcluded = '15,18,19,25,28,31';

/*
 * 이전 실패 테스트 데이터가 있다면
 * 테스트 회차 데이터만 정리한다.
 */
sql_query(
    "delete from l_filter_candidate
      where draw_no = '{$testDrawNo}'",
    false
);

sql_query(
    "delete from l_filter_run
      where draw_no = '{$testDrawNo}'",
    false
);

$startedAt = microtime(true);

$result = lottoFilterExecuteStoredRun(
    $testDrawNo,
    $sourceDrawNo,
    'stored-run-test',
    100,
    190,
    500
);

$elapsed = microtime(true) - $startedAt;

if (
    !isset($result['success'])
    || !$result['success']
) {
    echo "FAIL: stored run execution failed.\n";

    if (isset($result['error'])) {
        echo "Error: {$result['error']}\n";
    }

    exit(1);
}

$runId = isset($result['run_id'])
    ? (int) $result['run_id']
    : 0;

if ($runId < 1) {
    echo "FAIL: invalid run id.\n";
    exit(1);
}

$run = sql_fetch(
    "select
        lfr_id,
        draw_no,
        source_draw_no,
        status,
        total_combinations,
        candidate_count,
        excluded_numbers,
        started_at,
        filtered_at,
        ranked_at,
        completed_at,
        last_error
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
        rank_no,
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
    isset($rankOne['num1']) ? (int) $rankOne['num1'] : 0,
    isset($rankOne['num2']) ? (int) $rankOne['num2'] : 0,
    isset($rankOne['num3']) ? (int) $rankOne['num3'] : 0,
    isset($rankOne['num4']) ? (int) $rankOne['num4'] : 0,
    isset($rankOne['num5']) ? (int) $rankOne['num5'] : 0,
    isset($rankOne['num6']) ? (int) $rankOne['num6'] : 0,
);

echo "===== Stored Run Integration Test =====\n";
echo "Run ID              : {$runId}\n";
echo "Draw                 : {$testDrawNo}\n";
echo "Source draw          : {$sourceDrawNo}\n";
echo "Status               : {$run['status']}\n";
echo "Total combinations   : {$run['total_combinations']}\n";
echo "Candidate count      : {$run['candidate_count']}\n";
echo "Excluded numbers     : {$run['excluded_numbers']}\n";
echo "Filtered at          : {$run['filtered_at']}\n";
echo "Ranked at            : {$run['ranked_at']}\n";
echo "Completed at         : "
    . ($run['completed_at'] ?: 'NULL')
    . "\n";
echo "Last error           : "
    . ($run['last_error'] ?: 'NULL')
    . "\n";
echo "DB candidate rows    : "
    . number_format($rowCount)
    . "\n";
echo "Min rank             : {$minRank}\n";
echo "Max rank             : {$maxRank}\n";
echo "Distinct ranks       : "
    . number_format($distinctRankCount)
    . "\n";
echo "Rank 1 score         : "
    . $rankOne['score']
    . "\n";
echo "Rank 1 combination   : "
    . implode(',', $rankOneNumbers)
    . "\n";
echo "Elapsed seconds      : "
    . number_format($elapsed, 4)
    . "\n\n";

if ($run['status'] !== 'filtered') {
    echo "FAIL: run status is not filtered.\n";
    exit(1);
}

if ((int) $run['source_draw_no'] !== $sourceDrawNo) {
    echo "FAIL: source draw mismatch.\n";
    exit(1);
}

if ((int) $run['total_combinations'] !== 8145060) {
    echo "FAIL: total combination count mismatch.\n";
    exit(1);
}

if (
    (int) $run['candidate_count']
    !== $expectedCandidateCount
) {
    echo "FAIL: run candidate count mismatch.\n";
    exit(1);
}

if ($rowCount !== $expectedCandidateCount) {
    echo "FAIL: DB candidate row count mismatch.\n";
    exit(1);
}

if ($run['excluded_numbers'] !== $expectedExcluded) {
    echo "FAIL: excluded numbers mismatch.\n";
    exit(1);
}

if (
    empty($run['filtered_at'])
    || empty($run['ranked_at'])
) {
    echo "FAIL: filtering/ranking timestamps missing.\n";
    exit(1);
}

/*
 * 아직 분배 완료 단계가 아니므로
 * completed_at은 NULL이어야 한다.
 */
if (!empty($run['completed_at'])) {
    echo "FAIL: completed_at should remain NULL.\n";
    exit(1);
}

if (!empty($run['last_error'])) {
    echo "FAIL: last_error should be empty.\n";
    exit(1);
}

if (
    $minRank !== 1
    || $maxRank !== $expectedCandidateCount
    || $distinctRankCount !== $expectedCandidateCount
) {
    echo "FAIL: stored ranks are invalid.\n";
    exit(1);
}

if (
    $rankOneNumbers
    !== array(10,17,27,32,33,45)
) {
    echo "FAIL: rank 1 combination mismatch.\n";
    exit(1);
}

echo "PASS: stored run completed successfully.\n";
echo "PASS: l_filter_run status is correct.\n";
echo "PASS: candidate count is correct.\n";
echo "PASS: excluded numbers were recorded.\n";
echo "PASS: filtered/ranked timestamps are correct.\n";
echo "PASS: rank data is continuous and unique.\n";
echo "PASS: rank 1 combination is correct.\n";

/*
 * 테스트 데이터 정리.
 */
sql_query(
    "delete from l_filter_candidate
      where draw_no = '{$testDrawNo}'",
    false
);

sql_query(
    "delete from l_filter_run
      where draw_no = '{$testDrawNo}'",
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
      where draw_no = '{$testDrawNo}'",
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

echo "Remaining candidates : "
    . $remainingCandidateCount
    . "\n";
echo "Remaining runs       : "
    . $remainingRunCount
    . "\n";

if (
    $remainingCandidateCount !== 0
    || $remainingRunCount !== 0
) {
    echo "FAIL: test data cleanup failed.\n";
    exit(1);
}

echo "PASS: test data cleaned up.\n";
