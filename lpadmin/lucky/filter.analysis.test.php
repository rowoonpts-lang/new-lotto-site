<?php

define('_GNUBOARD_', true);

include_once(__DIR__ . '/../../include/lotto_filter.lib.php');

$tests = array(
    array(
        'numbers' => array(1, 2, 3, 4, 5, 6),
        'expected' => array(
            'sum_value' => 21,
            'odd_count' => 3,
            'prime_count' => 3,
            'multiple3_count' => 2,
            'max_consecutive' => 6,
        ),
    ),
    array(
        'numbers' => array(3, 11, 22, 28, 37, 45),
        'expected' => array(
            'sum_value' => 146,
            'odd_count' => 4,
            'prime_count' => 3,
            'multiple3_count' => 2,
            'max_consecutive' => 1,
        ),
    ),
    array(
        'numbers' => array(7, 8, 10, 11, 12, 30),
        'expected' => array(
            'sum_value' => 78,
            'odd_count' => 2,
            'prime_count' => 2,
            'multiple3_count' => 2,
            'max_consecutive' => 3,
        ),
    ),
);

foreach ($tests as $index => $test) {
    $result = lottoFilterAnalyzeCombination($test['numbers']);

    if ($result === false) {
        fwrite(STDERR, 'FAIL: test ' . ($index + 1) . " returned false.\n");
        exit(1);
    }

    foreach ($test['expected'] as $key => $expected) {
        if ((int) $result[$key] !== (int) $expected) {
            fwrite(
                STDERR,
                'FAIL: test '
                . ($index + 1)
                . " {$key} expected {$expected}, got {$result[$key]}\n"
            );
            exit(1);
        }
    }

    echo 'PASS: test ' . ($index + 1) . "\n";
}

$invalidTests = array(
    array(1, 2, 3, 4, 5),
    array(1, 2, 3, 4, 5, 5),
    array(0, 2, 3, 4, 5, 6),
    array(1, 2, 3, 4, 5, 46),
);

foreach ($invalidTests as $index => $numbers) {
    if (lottoFilterAnalyzeCombination($numbers) !== false) {
        fwrite(
            STDERR,
            'FAIL: invalid test '
            . ($index + 1)
            . " was accepted.\n"
        );
        exit(1);
    }

    echo 'PASS: invalid test ' . ($index + 1) . "\n";
}

echo "PASS: lotto combination analysis tests completed.\n";
