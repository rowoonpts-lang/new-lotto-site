<?php

$_SERVER['SERVER_PORT'] = '80';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

include_once("_common.php");
include_once(G5_PATH . "/include/lotto_filter.lib.php");

$row = sql_fetch(
    "select max(draw_no) as max_draw_no
       from g5_lotto_result",
    false
);

$latestDrawNo = isset($row['max_draw_no'])
    ? (int) $row['max_draw_no']
    : 0;

if ($latestDrawNo < 1) {
    echo "FAIL: lotto result data is empty.\n";
    exit(1);
}

$previousNumbers = lottoFilterGetResultNumbers(
    $latestDrawNo
);

if (count($previousNumbers) !== 6) {
    echo "FAIL: previous draw numbers were not loaded.\n";
    exit(1);
}

$historicalKeys = lottoFilterGetHistoricalTop3Keys(
    $latestDrawNo
);

if (count($historicalKeys) < 1) {
    echo "FAIL: historical winning keys were not created.\n";
    exit(1);
}

/*
 * 최신 회차 1등 조합은 반드시 필터 1에서 탈락해야 한다.
 */
if (
    lottoFilterPassHistoricalTop3(
        $previousNumbers,
        $historicalKeys
    )
) {
    echo "FAIL: latest first-prize combination was accepted.\n";
    exit(1);
}

/*
 * 최신 회차 당첨번호 5개 + 비당첨번호 1개 역시
 * 2등 또는 3등 가능 조합이므로 반드시 탈락해야 한다.
 */
$fiveMatchTest = array_slice(
    $previousNumbers,
    0,
    5
);

$previousLookup = array_fill_keys(
    $previousNumbers,
    true
);

$outsideNumber = 0;

for ($number = 1; $number <= 45; $number++) {
    if (!isset($previousLookup[$number])) {
        $outsideNumber = $number;
        break;
    }
}

$fiveMatchTest[] = $outsideNumber;

if (
    lottoFilterPassHistoricalTop3(
        $fiveMatchTest,
        $historicalKeys
    )
) {
    echo "FAIL: historical five-match combination was accepted.\n";
    exit(1);
}

$sumMin = 100;
$sumMax = 190;

$startedAt = microtime(true);

$result = lottoFilterRunCorePipeline(
    $sumMin,
    $sumMax,
    $historicalKeys,
    $previousNumbers
);

$elapsed = microtime(true) - $startedAt;

echo "===== Lotto Core Filter Pipeline =====\n";
echo "Latest result draw        : {$latestDrawNo}\n";
echo "Previous numbers          : "
    . implode(',', $previousNumbers)
    . "\n";
echo "Historical excluded keys  : "
    . number_format(count($historicalKeys))
    . "\n";

$labels = array(
    'total_count' => 'Total',
    'historical_pass_count' => 'Filter 1 historical',
    'consecutive_pass_count' => 'Filter 2 consecutive',
    'carry_neighbor_pass_count' => 'Filter 3 carry/neighbor',
    'balance_pass_count' => 'Filter 4 odd/low',
    'sum_pass_count' => 'Filter 5 sum',
    'ac_pass_count' => 'Filter 6 AC',
    'same_last_digit_pass_count' => 'Filter 7 same last digit',
    'empty_zone_pass_count' => 'Filter 8 empty zone',
    'math_balance_pass_count' => 'Filter 9 prime/multiple3',
    'final_core_pass_count' => 'Final core candidates',
);

foreach ($labels as $key => $label) {
    echo str_pad($label, 28)
        . ': '
        . number_format($result[$key])
        . "\n";
}

echo "Elapsed seconds             : "
    . number_format($elapsed, 4)
    . "\n";

if ($result['total_count'] !== 8145060) {
    echo "FAIL: total combination count mismatch.\n";
    exit(1);
}

$orderedKeys = array_keys($labels);
$previousCount = null;

foreach ($orderedKeys as $key) {
    $currentCount = $result[$key];

    if (
        $previousCount !== null
        && $currentCount > $previousCount
    ) {
        echo "FAIL: stage count increased at {$key}.\n";
        exit(1);
    }

    $previousCount = $currentCount;
}

echo "PASS: historical filter test completed.\n";
echo "PASS: total combination count is correct.\n";
echo "PASS: stage counts decrease monotonically.\n";
echo "PASS: core pipeline completed.\n";
