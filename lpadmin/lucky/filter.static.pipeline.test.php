<?php

define('_GNUBOARD_', true);

include_once(
    __DIR__ . '/../../include/lotto_filter.lib.php'
);

$sumMin = 100;
$sumMax = 190;

$startedAt = microtime(true);

$result = lottoFilterRunStaticPipeline(
    $sumMin,
    $sumMax
);

$elapsed = microtime(true) - $startedAt;

echo "===== Lotto Static Filter Pipeline =====\n";
echo "Sum range                 : {$sumMin} ~ {$sumMax}\n";
echo "Total                     : "
    . number_format($result['total_count'])
    . "\n";

echo "Filter 2 consecutive      : "
    . number_format($result['consecutive_pass_count'])
    . "\n";

echo "Filter 4 odd/low balance  : "
    . number_format($result['balance_pass_count'])
    . "\n";

echo "Filter 5 sum              : "
    . number_format($result['sum_pass_count'])
    . "\n";

echo "Filter 6 AC               : "
    . number_format($result['ac_pass_count'])
    . "\n";

echo "Filter 7 same last digit  : "
    . number_format(
        $result['same_last_digit_pass_count']
    )
    . "\n";

echo "Filter 8 empty zone       : "
    . number_format($result['empty_zone_pass_count'])
    . "\n";

echo "Filter 9 prime/multiple3  : "
    . number_format(
        $result['math_balance_pass_count']
    )
    . "\n";

echo "Final static candidates   : "
    . number_format($result['final_static_pass_count'])
    . "\n";

echo "Elapsed seconds           : "
    . number_format($elapsed, 4)
    . "\n";

$expectedTotal = 8145060;

if ($result['total_count'] !== $expectedTotal) {
    echo "FAIL: total combination count mismatch.\n";
    exit(1);
}

$orderedKeys = array(
    'total_count',
    'consecutive_pass_count',
    'balance_pass_count',
    'sum_pass_count',
    'ac_pass_count',
    'same_last_digit_pass_count',
    'empty_zone_pass_count',
    'math_balance_pass_count',
    'final_static_pass_count',
);

$previous = null;

foreach ($orderedKeys as $key) {
    $current = $result[$key];

    if (
        $previous !== null
        && $current > $previous
    ) {
        echo "FAIL: stage count increased at {$key}.\n";
        exit(1);
    }

    $previous = $current;
}

echo "PASS: total combination count is correct.\n";
echo "PASS: stage counts decrease monotonically.\n";
echo "PASS: static pipeline completed.\n";
