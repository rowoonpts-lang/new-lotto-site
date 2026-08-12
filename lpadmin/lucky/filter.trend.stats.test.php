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
    echo "FAIL: latest draw was not found.\n";
    exit(1);
}

$statistics = lottoFilterGetAllNumberStatistics(
    $latestDrawNo
);

if (count($statistics) !== 45) {
    echo "FAIL: expected 45 number statistics.\n";
    exit(1);
}

echo "===== Lotto Number Trend Statistics =====\n";
echo "Latest draw: {$latestDrawNo}\n\n";

echo str_pad('No', 4)
    . str_pad('Total', 8)
    . str_pad('AvgGap', 10)
    . str_pad('CurGap', 8)
    . str_pad('MaxGap', 8)
    . str_pad('Ratio', 9)
    . str_pad('Streak', 8)
    . str_pad('R25', 6)
    . str_pad('R5', 5)
    . str_pad('R3', 5)
    . str_pad('S1%', 8)
    . str_pad('S2%', 8)
    . str_pad('S3+%', 8)
    . "\n";

foreach ($statistics as $number => $stat) {
    echo str_pad((string) $number, 4)
        . str_pad(
            (string) $stat['total_appearances'],
            8
        )
        . str_pad(
            number_format(
                $stat['average_gap'],
                2
            ),
            10
        )
        . str_pad(
            (string) $stat['current_gap'],
            8
        )
        . str_pad(
            (string) $stat['max_gap'],
            8
        )
        . str_pad(
            number_format(
                $stat['gap_ratio'],
                2
            ),
            9
        )
        . str_pad(
            (string) $stat['current_streak'],
            8
        )
        . str_pad(
            (string) $stat['recent25_count'],
            6
        )
        . str_pad(
            (string) $stat['recent5_count'],
            5
        )
        . str_pad(
            (string) $stat['recent3_count'],
            5
        )
        . str_pad(
            number_format(
                $stat['streak1_rate'] * 100,
                1
            ),
            8
        )
        . str_pad(
            number_format(
                $stat['streak2_rate'] * 100,
                1
            ),
            8
        )
        . str_pad(
            number_format(
                $stat['streak3_plus_rate'] * 100,
                1
            ),
            8
        )
        . "\n";
}

$totalAppearances = 0;

foreach ($statistics as $stat) {
    $totalAppearances += $stat[
        'total_appearances'
    ];

    if ($stat['current_gap'] < 0) {
        echo "FAIL: negative current gap.\n";
        exit(1);
    }

    if ($stat['average_gap'] < 0) {
        echo "FAIL: negative average gap.\n";
        exit(1);
    }

    if ($stat['recent25_count'] > 25) {
        echo "FAIL: invalid recent25 count.\n";
        exit(1);
    }

    if ($stat['recent5_count'] > 5) {
        echo "FAIL: invalid recent5 count.\n";
        exit(1);
    }

    if ($stat['recent3_count'] > 3) {
        echo "FAIL: invalid recent3 count.\n";
        exit(1);
    }
}

$expectedAppearances = $latestDrawNo * 6;

if ($totalAppearances !== $expectedAppearances) {
    echo "FAIL: total appearances mismatch.\n";
    echo "Expected: {$expectedAppearances}\n";
    echo "Actual  : {$totalAppearances}\n";
    exit(1);
}

echo "\n";
echo "Total appearances : "
    . number_format($totalAppearances)
    . "\n";

echo "Expected          : "
    . number_format($expectedAppearances)
    . "\n";

echo "PASS: 45 number statistics created.\n";
echo "PASS: total appearances are correct.\n";
echo "PASS: trend statistics test completed.\n";
