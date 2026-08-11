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
include_once(G5_PATH . '/include/lotto_filter.lib.php');

$maxRow = sql_fetch(
    "select max(draw_no) as max_draw_no from l_filter_run"
);

$maxDrawNo = isset($maxRow['max_draw_no'])
    ? (int) $maxRow['max_draw_no']
    : 0;

$testDrawNo = $maxDrawNo + 1000000;

if ($testDrawNo < 1000000) {
    $testDrawNo = 1000000;
}

$sourceDrawNo = 0;
$createdBy = 'cli_filter_test';
$sumMin = 100;
$sumMax = 180;

echo "===== Lotto Filter Run DB Test =====\n";
echo "Test draw        : {$testDrawNo}\n";

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

$runId = (int) $result['run_id'];

$row = sql_fetch(
    "
    select *
    from l_filter_run
    where lfr_id = '{$runId}'
    "
);

$failures = array();

if ((int) $row['draw_no'] !== $testDrawNo) {
    $failures[] = 'draw_no mismatch';
}

if ($row['status'] !== 'filtered') {
    $failures[] = 'status is not filtered';
}

if ((int) $row['total_combinations'] !== 8145060) {
    $failures[] = 'total_combinations mismatch';
}

if ((int) $row['candidate_count'] !== 6662971) {
    $failures[] = 'candidate_count mismatch';
}

if (empty($row['started_at'])) {
    $failures[] = 'started_at is empty';
}

if (empty($row['filtered_at'])) {
    $failures[] = 'filtered_at is empty';
}

if (!empty($row['completed_at'])) {
    $failures[] = 'completed_at must still be null';
}

if ($row['created_by'] !== $createdBy) {
    $failures[] = 'created_by mismatch';
}

echo "Run ID           : {$runId}\n";
echo "Status           : {$row['status']}\n";
echo "Total            : "
    . number_format((int) $row['total_combinations'])
    . "\n";
echo "Candidate        : "
    . number_format((int) $row['candidate_count'])
    . "\n";
echo "Elapsed seconds  : "
    . number_format($elapsed, 4)
    . "\n";

if ($failures) {
    echo "Test row kept for inspection.\n";

    foreach ($failures as $failure) {
        fwrite(
            STDERR,
            "FAIL: {$failure}\n"
        );
    }

    exit(1);
}

sql_query(
    "
    delete from l_filter_run
    where lfr_id = '{$runId}'
      and created_by = 'cli_filter_test'
    "
);

$deletedRow = sql_fetch(
    "
    select count(*) as cnt
    from l_filter_run
    where lfr_id = '{$runId}'
    "
);

if ((int) $deletedRow['cnt'] !== 0) {
    fwrite(
        STDERR,
        "FAIL: test row cleanup failed.\n"
    );
    exit(1);
}

echo "PASS: filter run was recorded correctly.\n";
echo "PASS: status is filtered.\n";
echo "PASS: completed_at remains null.\n";
echo "PASS: test database row was cleaned up.\n";
