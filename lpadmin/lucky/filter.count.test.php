<?php

if (PHP_SAPI === 'cli') {
    $_SERVER['SERVER_PORT'] = $_SERVER['SERVER_PORT'] ?? 80;
    $_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'] ?? 'localhost';
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $_SERVER['REQUEST_URI'] = $_SERVER['REQUEST_URI'] ?? '/lpadmin/lucky/filter.count.test.php';
    $_SERVER['REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

include_once("_common.php");
include_once(G5_PATH."/include/lotto_filter.lib.php");

$range = lottoFilterGetSumRange();

$sumMin = (int) $range['min'];
$sumMax = (int) $range['max'];

if ($sumMin < 21 || $sumMax > 255 || $sumMin > $sumMax) {
    fwrite(
        STDERR,
        "Invalid sum filter range: {$sumMin} ~ {$sumMax}\n"
    );
    exit(1);
}

$startedAt = microtime(true);
$memoryBefore = memory_get_usage(true);

$result = lottoFilterCountBySum(
    $sumMin,
    $sumMax
);

$elapsed = microtime(true) - $startedAt;
$memoryAfter = memory_get_usage(true);
$peakMemory = memory_get_peak_usage(true);

echo "===== Lotto Filter Count Test =====\n";
echo "Sum range       : {$sumMin} ~ {$sumMax}\n";
echo "Total           : "
    . number_format($result['total_count'])
    . "\n";
echo "Passed          : "
    . number_format($result['candidate_count'])
    . "\n";
echo "Elapsed seconds : "
    . number_format($elapsed, 4)
    . "\n";
echo "Memory before   : "
    . number_format($memoryBefore)
    . " bytes\n";
echo "Memory after    : "
    . number_format($memoryAfter)
    . " bytes\n";
echo "Peak memory     : "
    . number_format($peakMemory)
    . " bytes\n";

if ((int) $result['total_count'] !== 8145060) {
    fwrite(
        STDERR,
        "FAIL: total combination count mismatch.\n"
    );
    exit(1);
}

echo "PASS: total combination count is correct.\n";

if ($sumMin === 100 && $sumMax === 180) {
    if ((int) $result['candidate_count'] !== 6662971) {
        fwrite(
            STDERR,
            "FAIL: candidate count mismatch for sum range 100 ~ 180.\n"
        );
        exit(1);
    }

    echo "PASS: candidate count for 100 ~ 180 is correct.\n";
}
