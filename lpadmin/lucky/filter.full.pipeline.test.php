<?php

$_SERVER['SERVER_PORT'] = '80';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

include_once("_common.php");
include_once(
    G5_PATH . "/include/lotto_filter.lib.php"
);

$row = sql_fetch(
    "select max(draw_no) as max_draw_no
       from g5_lotto_result",
    false
);

$latestDrawNo = isset($row['max_draw_no'])
    ? (int) $row['max_draw_no']
    : 0;

if ($latestDrawNo < 1) {
    echo "FAIL: latest draw not found.\n";
    exit(1);
}

$previousNumbers = lottoFilterGetResultNumbers(
    $latestDrawNo
);

if (!lottoFilterIsValidCombination($previousNumbers)) {
    echo "FAIL: previous numbers are invalid.\n";
    exit(1);
}

$historicalKeys =
    lottoFilterGetHistoricalTop3Keys(
        $latestDrawNo
    );

if (count($historicalKeys) < 1) {
    echo "FAIL: historical exclusion keys missing.\n";
    exit(1);
}

$numberScores =
    lottoFilterGetTrendNumberScores(
        $latestDrawNo
    );

if (count($numberScores) !== 45) {
    echo "FAIL: expected 45 number scores.\n";
    exit(1);
}

$excludedNumbers =
    lottoFilterGetRecommendedExcludedNumbers(
        $numberScores,
        6
    );

if (count($excludedNumbers) !== 6) {
    echo "FAIL: expected 6 excluded numbers.\n";
    exit(1);
}

$sumMin = 100;
$sumMax = 190;

$startedAt = microtime(true);

$result = lottoFilterRunFullPipeline(
    $sumMin,
    $sumMax,
    $historicalKeys,
    $previousNumbers,
    $numberScores,
    $excludedNumbers
);

$elapsed = microtime(true) - $startedAt;

echo "===== Lotto Full Filter Pipeline =====\n";

echo "Source draw               : "
    . $latestDrawNo
    . "\n";

echo "Target draw               : "
    . ($latestDrawNo + 1)
    . "\n";

echo "Previous numbers          : "
    . implode(',', $previousNumbers)
    . "\n";

echo "Excluded numbers          : "
    . implode(',', $excludedNumbers)
    . "\n\n";

echo "Total                     : "
    . number_format($result['total_count'])
    . "\n";

echo "Filter 1 historical       : "
    . number_format(
        $result['historical_pass_count']
    )
    . "\n";

echo "Filter 2 consecutive      : "
    . number_format(
        $result['consecutive_pass_count']
    )
    . "\n";

echo "Filter 3 carry/neighbor   : "
    . number_format(
        $result['carry_neighbor_pass_count']
    )
    . "\n";

echo "Filter 4 odd/low          : "
    . number_format(
        $result['balance_pass_count']
    )
    . "\n";

echo "Filter 5 sum              : "
    . number_format(
        $result['sum_pass_count']
    )
    . "\n";

echo "Filter 6 AC               : "
    . number_format(
        $result['ac_pass_count']
    )
    . "\n";

echo "Filter 7 same last digit  : "
    . number_format(
        $result['same_last_digit_pass_count']
    )
    . "\n";

echo "Filter 8 empty zone       : "
    . number_format(
        $result['empty_zone_pass_count']
    )
    . "\n";

echo "Filter 9 math balance     : "
    . number_format(
        $result['math_balance_pass_count']
    )
    . "\n";

echo "Filter 10 trend scored    : "
    . number_format(
        $result['trend_scored_count']
    )
    . "\n";

echo "Filter 11 exclusions      : "
    . number_format(
        $result['exclusion_pass_count']
    )
    . "\n";

echo "Final candidates          : "
    . number_format(
        $result['final_pass_count']
    )
    . "\n\n";

echo "Trend minimum             : "
    . number_format(
        $result['trend_score_min'],
        4
    )
    . "\n";

echo "Trend maximum             : "
    . number_format(
        $result['trend_score_max'],
        4
    )
    . "\n";

echo "Trend average             : "
    . number_format(
        $result['trend_score_average'],
        4
    )
    . "\n";

echo "Highest final score       : "
    . number_format(
        $result['highest_score'],
        4
    )
    . "\n";

echo "Highest final combination : "
    . implode(
        ',',
        $result['highest_combination']
    )
    . "\n";

echo "Elapsed seconds           : "
    . number_format($elapsed, 4)
    . "\n\n";

if ($result['total_count'] !== 8145060) {
    echo "FAIL: total combination count mismatch.\n";
    exit(1);
}

if (
    $result['final_core_pass_count']
    !== 2033386
) {
    echo "FAIL: filters 1-9 regression detected.\n";
    exit(1);
}

if (
    $result['trend_scored_count']
    !== $result['final_core_pass_count']
) {
    echo "FAIL: not every core candidate was scored.\n";
    exit(1);
}

if (
    $result['exclusion_pass_count']
    > $result['trend_scored_count']
) {
    echo "FAIL: exclusion pass count is invalid.\n";
    exit(1);
}

if (
    $result['final_pass_count']
    !== $result['exclusion_pass_count']
) {
    echo "FAIL: final count mismatch.\n";
    exit(1);
}

if (
    $result['trend_score_min'] < 0
    || $result['trend_score_max'] > 100
) {
    echo "FAIL: trend score range invalid.\n";
    exit(1);
}

if (
    !lottoFilterIsValidCombination(
        $result['highest_combination']
    )
) {
    echo "FAIL: highest combination invalid.\n";
    exit(1);
}

if (
    !lottoFilterPassRecommendedExclusions(
        $result['highest_combination'],
        $excludedNumbers
    )
) {
    echo "FAIL: highest combination contains exclusion.\n";
    exit(1);
}

echo "PASS: total combination count is correct.\n";
echo "PASS: filters 1-9 regression is clean.\n";
echo "PASS: every core candidate received a trend score.\n";
echo "PASS: filter 11 exclusions were applied.\n";
echo "PASS: full 1-11 pipeline completed.\n";
