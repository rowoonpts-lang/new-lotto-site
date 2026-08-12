<?php

define('_GNUBOARD_', true);

include_once(
    __DIR__ . '/../../include/lotto_filter.lib.php'
);

$tests = array(
    array(
        'name' => 'valid static filter combination',
        'numbers' => array(1, 2, 3, 10, 39, 45),
        'expected' => array(
            'sum_value' => 100,
            'ac_value' => 9,
            'odd_count' => 4,
            'low_count' => 4,
            'prime_count' => 2,
            'multiple3_count' => 3,
            'max_consecutive' => 3,
            'max_same_last_digit' => 1,
            'empty_zone_count' => 1,
        ),
        'pass' => true,
    ),
    array(
        'name' => 'reject four consecutive numbers',
        'numbers' => array(1, 2, 3, 4, 30, 40),
        'pass' => false,
    ),
    array(
        'name' => 'reject low AC value',
        'numbers' => array(4, 11, 18, 25, 32, 39),
        'pass' => false,
    ),
    array(
        'name' => 'reject sum below minimum',
        'numbers' => array(7, 8, 10, 11, 12, 30),
        'pass' => false,
    ),
);

$failures = array();

foreach ($tests as $index => $test) {
    $analysis = lottoFilterAnalyzeCombination($test['numbers']);

    if ($analysis === false) {
        $failures[] = $test['name'] . ': analysis failed.';
        continue;
    }

    if (isset($test['expected'])) {
        foreach ($test['expected'] as $key => $expected) {
            if (
                !isset($analysis[$key])
                || $analysis[$key] !== $expected
            ) {
                $actual = isset($analysis[$key])
                    ? var_export($analysis[$key], true)
                    : 'missing';

                $failures[] = $test['name']
                    . ': '
                    . $key
                    . ' expected '
                    . var_export($expected, true)
                    . ', got '
                    . $actual;
            }
        }
    }

    $passed = lottoFilterPassStaticRules($analysis);

    if ($passed !== $test['pass']) {
        $failures[] = $test['name']
            . ': expected pass='
            . ($test['pass'] ? 'true' : 'false')
            . ', got '
            . ($passed ? 'true' : 'false');
    }
}

if (count($failures) > 0) {
    foreach ($failures as $failure) {
        echo "FAIL: {$failure}\n";
    }

    exit(1);
}

foreach ($tests as $test) {
    echo "PASS: {$test['name']}\n";
}

echo "PASS: static lotto filter tests completed.\n";
