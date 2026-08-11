<?php

if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * 새 로또 필터 설정값을 조회한다.
 *
 * @param string $key
 * @param mixed  $default
 * @return mixed
 */
function lottoFilterGetSetting($key, $default = null)
{
    $key = trim((string) $key);

    if ($key === '') {
        return $default;
    }

    $keySql = sql_real_escape_string($key);

    $row = sql_fetch(
        "select setting_value
           from l_filter_setting
          where setting_key = '{$keySql}'
          limit 1",
        false
    );

    if (!isset($row['setting_value'])) {
        return $default;
    }

    return $row['setting_value'];
}

/**
 * 현재 총합수 필터 범위를 조회한다.
 *
 * @return array
 */
function lottoFilterGetSumRange()
{
    $min = (int) lottoFilterGetSetting('sum_min', 100);
    $max = (int) lottoFilterGetSetting('sum_max', 180);

    return array(
        'min' => $min,
        'max' => $max,
    );
}

/**
 * 총합수 조건에 맞는지 검사한다.
 *
 * @param int $sum
 * @param int $min
 * @param int $max
 * @return bool
 */
function lottoFilterPassSum($sum, $min, $max)
{
    $sum = (int) $sum;
    $min = (int) $min;
    $max = (int) $max;

    return $sum >= $min && $sum <= $max;
}

/**
 * 1~45 중 6개 전체 조합을 순회하면서
 * 총합수 필터를 통과한 개수만 계산한다.
 *
 * 후보 조합을 메모리에 저장하지 않는다.
 *
 * @param int $sumMin
 * @param int $sumMax
 * @return array
 */
function lottoFilterCountBySum($sumMin, $sumMax)
{
    $sumMin = (int) $sumMin;
    $sumMax = (int) $sumMax;

    $totalCount = 0;
    $candidateCount = 0;

    for ($n1 = 1; $n1 <= 40; $n1++) {
        for ($n2 = $n1 + 1; $n2 <= 41; $n2++) {
            for ($n3 = $n2 + 1; $n3 <= 42; $n3++) {
                for ($n4 = $n3 + 1; $n4 <= 43; $n4++) {
                    for ($n5 = $n4 + 1; $n5 <= 44; $n5++) {
                        for ($n6 = $n5 + 1; $n6 <= 45; $n6++) {
                            $totalCount++;

                            $sum = $n1
                                + $n2
                                + $n3
                                + $n4
                                + $n5
                                + $n6;

                            if (
                                lottoFilterPassSum(
                                    $sum,
                                    $sumMin,
                                    $sumMax
                                )
                            ) {
                                $candidateCount++;
                            }
                        }
                    }
                }
            }
        }
    }

    return array(
        'total_count' => $totalCount,
        'candidate_count' => $candidateCount,
    );
}

/**
 * 로또 번호가 정상적인 6개 조합인지 검사한다.
 *
 * @param array $numbers
 * @return bool
 */
function lottoFilterIsValidCombination(array $numbers)
{
    if (count($numbers) !== 6) {
        return false;
    }

    $normalized = array_map('intval', $numbers);

    foreach ($normalized as $number) {
        if ($number < 1 || $number > 45) {
            return false;
        }
    }

    if (count(array_unique($normalized)) !== 6) {
        return false;
    }

    return true;
}

/**
 * 소수 여부를 검사한다.
 *
 * @param int $number
 * @return bool
 */
function lottoFilterIsPrime($number)
{
    $number = (int) $number;

    if ($number < 2) {
        return false;
    }

    if ($number === 2) {
        return true;
    }

    if ($number % 2 === 0) {
        return false;
    }

    $limit = (int) floor(sqrt($number));

    for ($i = 3; $i <= $limit; $i += 2) {
        if ($number % $i === 0) {
            return false;
        }
    }

    return true;
}

/**
 * 연속된 번호의 최대 길이를 계산한다.
 *
 * 예:
 * 1,2,3,10,20,30 => 3
 *
 * @param array $numbers
 * @return int
 */
function lottoFilterGetMaxConsecutive(array $numbers)
{
    $numbers = array_map('intval', $numbers);
    sort($numbers, SORT_NUMERIC);

    $maxLength = 1;
    $currentLength = 1;

    for ($i = 1; $i < count($numbers); $i++) {
        if ($numbers[$i] === $numbers[$i - 1] + 1) {
            $currentLength++;

            if ($currentLength > $maxLength) {
                $maxLength = $currentLength;
            }
        } else {
            $currentLength = 1;
        }
    }

    return $maxLength;
}

/**
 * 로또 6개 조합의 기본 분석값을 계산한다.
 *
 * 현재 확정된 분석값만 계산한다.
 *
 * @param array $numbers
 * @return array|false
 */
function lottoFilterAnalyzeCombination(array $numbers)
{
    if (!lottoFilterIsValidCombination($numbers)) {
        return false;
    }

    $numbers = array_map('intval', $numbers);
    sort($numbers, SORT_NUMERIC);

    $oddCount = 0;
    $primeCount = 0;
    $multiple3Count = 0;

    foreach ($numbers as $number) {
        if ($number % 2 !== 0) {
            $oddCount++;
        }

        if (lottoFilterIsPrime($number)) {
            $primeCount++;
        }

        if ($number % 3 === 0) {
            $multiple3Count++;
        }
    }

    return array(
        'numbers' => $numbers,
        'sum_value' => array_sum($numbers),
        'odd_count' => $oddCount,
        'prime_count' => $primeCount,
        'multiple3_count' => $multiple3Count,
        'max_consecutive' => lottoFilterGetMaxConsecutive($numbers),
    );
}
