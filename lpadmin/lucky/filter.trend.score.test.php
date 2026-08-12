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

$scores = lottoFilterGetTrendNumberScores(
    $latestDrawNo
);

if (count($scores) !== 45) {
    echo "FAIL: expected 45 score rows.\n";
    exit(1);
}

$trendRows = array_values($scores);

usort(
    $trendRows,
    function ($a, $b) {
        if ($a['trend_score'] == $b['trend_score']) {
            return $a['number'] <=> $b['number'];
        }

        return $a['trend_score'] < $b['trend_score']
            ? 1
            : -1;
    }
);

$riskRows = array_values($scores);

usort(
    $riskRows,
    function ($a, $b) {
        if ($a['risk_score'] == $b['risk_score']) {
            return $a['number'] <=> $b['number'];
        }

        return $a['risk_score'] < $b['risk_score']
            ? 1
            : -1;
    }
);

$excluded = lottoFilterGetRecommendedExcludedNumbers(
    $scores,
    6
);

echo "===== Filter 10 Trend Scores =====\n";
echo "Latest draw: {$latestDrawNo}\n\n";

echo "TOP 15 TREND NUMBERS\n";

foreach (array_slice($trendRows, 0, 15) as $row) {
    echo sprintf(
        "%2d  score=%6.2f  gap=%2d  ratio=%4.2f  R25=%2d  streak=%d\n",
        $row['number'],
        $row['trend_score'],
        $row['current_gap'],
        $row['gap_ratio'],
        $row['recent25_count'],
        $row['current_streak']
    );
}

echo "\n";
echo "TOP 12 RISK NUMBERS\n";

foreach (array_slice($riskRows, 0, 12) as $row) {
    echo sprintf(
        "%2d  risk=%6.2f  R25=%2d  R5=%d  R3=%d  streak=%d\n",
        $row['number'],
        $row['risk_score'],
        $row['recent25_count'],
        $row['recent5_count'],
        $row['recent3_count'],
        $row['current_streak']
    );
}

echo "\n";
echo "Recommended excluded numbers: "
    . implode(',', $excluded)
    . "\n";

if (count($excluded) !== 6) {
    echo "FAIL: expected exactly 6 excluded numbers.\n";
    exit(1);
}

if (count(array_unique($excluded)) !== 6) {
    echo "FAIL: excluded numbers are duplicated.\n";
    exit(1);
}

foreach ($scores as $row) {
    if (
        $row['trend_score'] < 0
        || $row['trend_score'] > 100
    ) {
        echo "FAIL: invalid trend score.\n";
        exit(1);
    }

    if (
        $row['risk_score'] < 0
        || $row['risk_score'] > 100
    ) {
        echo "FAIL: invalid risk score.\n";
        exit(1);
    }
}

echo "PASS: 45 number scores created.\n";
echo "PASS: score ranges are valid.\n";
echo "PASS: 6 excluded numbers selected.\n";
