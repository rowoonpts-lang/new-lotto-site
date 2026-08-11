<?php

define('_GNUBOARD_', true);

include_once(__DIR__ . '/../../include/lotto_filter.lib.php');

$sumMin = 100;
$sumMax = 180;

$startedAt = microtime(true);
$memoryBefore = memory_get_usage(true);

$result = lottoFilterRunAnalysisPipeline(
    $sumMin,
    $sumMax
);

$elapsed = microtime(true) - $startedAt;
$memoryAfter = memory_get_usage(true);
$peakMemory = memory_get_peak_usage(true);

echo "===== Lotto Filter Pipeline Test =====\n";
echo "Sum range       : {$sumMin} ~ {$sumMax}\n";
echo "Total           : "
    . number_format($result['total_count'])
    . "\n";
echo "Sum passed      : "
    . number_format($result['sum_pass_count'])
    . "\n";
echo "Analyzed        : "
    . number_format($result['analyzed_count'])
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

if ((int) $result['sum_pass_count'] !== 6662971) {
    fwrite(
        STDERR,
        "FAIL: sum filter count mismatch.\n"
    );
    exit(1);
}

if ((int) $result['analyzed_count'] !== 6662971) {
    fwrite(
        STDERR,
        "FAIL: analyzed combination count mismatch.\n"
    );
    exit(1);
}

echo "PASS: pipeline total count is correct.\n";
echo "PASS: pipeline sum filter count is correct.\n";
echo "PASS: all sum-filtered combinations were analyzed.\n";
