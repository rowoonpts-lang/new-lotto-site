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
    echo "FAIL: number scores missing.\n";
    exit(1);
}

$excluded = lottoFilterGetRecommendedExcludedNumbers(
    $scores,
    6
);

if (count($excluded) !== 6) {
    echo "FAIL: excluded number count mismatch.\n";
    exit(1);
}

/*
 * 현재 trend 상위 번호를 이용한 예제.
 */
$trendTest = array(
    5,
    10,
    23,
    27,
    33,
    45,
);

$trendScore =
    lottoFilterCalculateCombinationTrendScore(
        $trendTest,
        $scores
    );

if ($trendScore <= 0 || $trendScore > 100) {
    echo "FAIL: invalid combination trend score.\n";
    exit(1);
}

/*
 * 제외수 하나가 포함되면 반드시 탈락.
 */
$excludedTest = array(
    $excluded[0],
    1,
    2,
    3,
    4,
    5,
);

$excludedTest = array_values(
    array_unique($excludedTest)
);

/*
 * 혹시 중복으로 6개가 안 될 경우 채운다.
 */
for ($number = 1; count($excludedTest) < 6; $number++) {
    if (!in_array($number, $excludedTest, true)) {
        $excludedTest[] = $number;
    }
}

if (
    lottoFilterPassRecommendedExclusions(
        $excludedTest,
        $excluded
    )
) {
    echo "FAIL: excluded number combination passed.\n";
    exit(1);
}

/*
 * 제외수가 전혀 없는 조합을 만든다.
 */
$cleanTest = array();

for ($number = 1; $number <= 45; $number++) {
    if (
        !in_array($number, $excluded, true)
        && count($cleanTest) < 6
    ) {
        $cleanTest[] = $number;
    }
}

if (
    !lottoFilterPassRecommendedExclusions(
        $cleanTest,
        $excluded
    )
) {
    echo "FAIL: clean combination was rejected.\n";
    exit(1);
}

echo "Latest draw              : {$latestDrawNo}\n";
echo "Excluded numbers         : "
    . implode(',', $excluded)
    . "\n";

echo "Trend test combination   : "
    . implode(',', $trendTest)
    . "\n";

echo "Trend combination score  : "
    . number_format($trendScore, 4)
    . "\n";

echo "Clean combination        : "
    . implode(',', $cleanTest)
    . "\n";

echo "PASS: combination trend score calculated.\n";
echo "PASS: excluded number combination rejected.\n";
echo "PASS: clean combination accepted.\n";
