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
    $max = (int) lottoFilterGetSetting('sum_max', 190);

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
 * AC 값을 계산한다.
 *
 * 6개 번호의 모든 양수 차이값 중
 * 서로 다른 차이값의 개수 - 5.
 *
 * @param array $numbers
 * @return int
 */
function lottoFilterGetAcValue(array $numbers)
{
    $numbers = array_map('intval', $numbers);
    sort($numbers, SORT_NUMERIC);

    $differences = array();

    for ($i = 0; $i < count($numbers) - 1; $i++) {
        for ($j = $i + 1; $j < count($numbers); $j++) {
            $difference = $numbers[$j] - $numbers[$i];
            $differences[$difference] = true;
        }
    }

    return max(0, count($differences) - 5);
}

/**
 * 같은 일의 자리 숫자가 가장 많이 나온 개수를 계산한다.
 *
 * @param array $numbers
 * @return int
 */
function lottoFilterGetMaxSameLastDigit(array $numbers)
{
    $counts = array();

    foreach ($numbers as $number) {
        $lastDigit = ((int) $number) % 10;

        if (!isset($counts[$lastDigit])) {
            $counts[$lastDigit] = 0;
        }

        $counts[$lastDigit]++;
    }

    return count($counts) > 0
        ? max($counts)
        : 0;
}

/**
 * 5개 번호대 중 빈 구간 개수를 계산한다.
 *
 * 구간:
 * 1~9
 * 10~19
 * 20~29
 * 30~39
 * 40~45
 *
 * @param array $numbers
 * @return int
 */
function lottoFilterGetEmptyZoneCount(array $numbers)
{
    $usedZones = array(
        0 => false,
        1 => false,
        2 => false,
        3 => false,
        4 => false,
    );

    foreach ($numbers as $number) {
        $number = (int) $number;

        if ($number <= 9) {
            $usedZones[0] = true;
        } elseif ($number <= 19) {
            $usedZones[1] = true;
        } elseif ($number <= 29) {
            $usedZones[2] = true;
        } elseif ($number <= 39) {
            $usedZones[3] = true;
        } else {
            $usedZones[4] = true;
        }
    }

    $emptyCount = 0;

    foreach ($usedZones as $used) {
        if (!$used) {
            $emptyCount++;
        }
    }

    return $emptyCount;
}

/**
 * 확정된 정적 필터 조건을 통과하는지 검사한다.
 *
 * DB 또는 직전 회차 자료가 필요 없는 필터만 검사한다.
 *
 * @param array $analysis
 * @return bool
 */
function lottoFilterPassStaticRules(
    array $analysis,
    $sumMin = 100,
    $sumMax = 190
) {
    $sumMin = (int) $sumMin;
    $sumMax = (int) $sumMax;
    if ($analysis['max_consecutive'] > 3) {
        return false;
    }

    if (
        $analysis['odd_count'] < 2
        || $analysis['odd_count'] > 5
    ) {
        return false;
    }

    if (
        $analysis['low_count'] < 2
        || $analysis['low_count'] > 4
    ) {
        return false;
    }

    if (
        $analysis['sum_value'] < $sumMin
        || $analysis['sum_value'] > $sumMax
    ) {
        return false;
    }

    if ($analysis['ac_value'] < 7) {
        return false;
    }

    if ($analysis['max_same_last_digit'] > 2) {
        return false;
    }

    if (
        $analysis['empty_zone_count'] < 1
        || $analysis['empty_zone_count'] > 2
    ) {
        return false;
    }

    if (
        $analysis['prime_count'] < 1
        || $analysis['prime_count'] > 3
    ) {
        return false;
    }

    if (
        $analysis['multiple3_count'] < 1
        || $analysis['multiple3_count'] > 3
    ) {
        return false;
    }

    return true;
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
    $lowCount = 0;
    $primeCount = 0;
    $multiple3Count = 0;

    foreach ($numbers as $number) {
        if ($number % 2 !== 0) {
            $oddCount++;
        }

        if ($number <= 22) {
            $lowCount++;
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
        'ac_value' => lottoFilterGetAcValue($numbers),
        'odd_count' => $oddCount,
        'low_count' => $lowCount,
        'prime_count' => $primeCount,
        'multiple3_count' => $multiple3Count,
        'max_consecutive' => lottoFilterGetMaxConsecutive($numbers),
        'max_same_last_digit' => lottoFilterGetMaxSameLastDigit($numbers),
        'empty_zone_count' => lottoFilterGetEmptyZoneCount($numbers),
    );
}

/**
 * 전체 로또 조합을 순회하면서 현재 필터/분석 파이프라인을 실행한다.
 *
 * 처리 순서:
 * 1. 전체 조합 생성
 * 2. 총합수 필터
 * 3. 통과 조합 기본 분석
 *
 * 조합 자체는 메모리에 누적하지 않는다.
 *
 * @param int $sumMin
 * @param int $sumMax
 * @return array
 */
/**
 * 확정된 정적 필터를 실제 명세 순서대로 실행한다.
 *
 * 현재 적용 단계:
 * 2. 연속 번호 제한
 * 4. 홀짝 / 저고 밸런스
 * 5. 총합 구간
 * 6. AC
 * 7. 동끝수
 * 8. 멸구간
 * 9. 소수 / 3의 배수
 *
 * 조합은 메모리에 저장하지 않고 개수만 집계한다.
 *
 * @param int $sumMin
 * @param int $sumMax
 * @return array
 */
/**
 * 조합 비교용 고유 키를 만든다.
 *
 * @param array $numbers
 * @return string
 */
function lottoFilterCombinationKey(array $numbers)
{
    if (!lottoFilterIsValidCombination($numbers)) {
        return '';
    }

    $numbers = array_map('intval', $numbers);
    sort($numbers, SORT_NUMERIC);

    return implode('-', $numbers);
}

/**
 * 특정 회차의 당첨번호 6개를 조회한다.
 *
 * @param int $drawNo
 * @return array
 */
function lottoFilterGetResultNumbers($drawNo)
{
    $drawNo = (int) $drawNo;

    if ($drawNo < 1) {
        return array();
    }

    $row = sql_fetch(
        "select
            num_1,
            num_2,
            num_3,
            num_4,
            num_5,
            num_6
         from g5_lotto_result
         where draw_no = '{$drawNo}'
         limit 1",
        false
    );

    if (!isset($row['num_1'])) {
        return array();
    }

    $numbers = array(
        (int) $row['num_1'],
        (int) $row['num_2'],
        (int) $row['num_3'],
        (int) $row['num_4'],
        (int) $row['num_5'],
        (int) $row['num_6'],
    );

    if (!lottoFilterIsValidCombination($numbers)) {
        return array();
    }

    sort($numbers, SORT_NUMERIC);

    return $numbers;
}

/**
 * 1회부터 지정 회차까지의 역대 1, 2, 3등 조합 키를 만든다.
 *
 * 한 회차 기준:
 * - 1등: 당첨번호 6개
 * - 2/3등: 당첨번호 5개 + 당첨번호가 아닌 번호 1개
 *
 * 즉 당첨번호 6개 중 5개 이상이 일치하는 모든 조합을 배제한다.
 *
 * @param int $maxDrawNo
 * @return array
 */
function lottoFilterGetHistoricalTop3Keys($maxDrawNo)
{
    $maxDrawNo = (int) $maxDrawNo;
    $keys = array();

    if ($maxDrawNo < 1) {
        return $keys;
    }

    $result = sql_query(
        "select
            draw_no,
            num_1,
            num_2,
            num_3,
            num_4,
            num_5,
            num_6
         from g5_lotto_result
         where draw_no <= '{$maxDrawNo}'
         order by draw_no asc",
        false
    );

    while ($result && ($row = sql_fetch_array($result))) {
        $winning = array(
            (int) $row['num_1'],
            (int) $row['num_2'],
            (int) $row['num_3'],
            (int) $row['num_4'],
            (int) $row['num_5'],
            (int) $row['num_6'],
        );

        if (!lottoFilterIsValidCombination($winning)) {
            continue;
        }

        sort($winning, SORT_NUMERIC);

        /*
         * 1등 조합
         */
        $firstKey = lottoFilterCombinationKey($winning);

        if ($firstKey !== '') {
            $keys[$firstKey] = true;
        }

        /*
         * 2등 + 3등 조합
         *
         * 당첨번호 6개 중 하나를 빼고,
         * 당첨번호가 아닌 1~45 번호 하나를 넣는다.
         */
        $winningLookup = array_fill_keys($winning, true);

        for ($removeIndex = 0; $removeIndex < 6; $removeIndex++) {
            $fiveNumbers = $winning;
            array_splice($fiveNumbers, $removeIndex, 1);

            for ($outside = 1; $outside <= 45; $outside++) {
                if (isset($winningLookup[$outside])) {
                    continue;
                }

                $combination = $fiveNumbers;
                $combination[] = $outside;

                $key = lottoFilterCombinationKey($combination);

                if ($key !== '') {
                    $keys[$key] = true;
                }
            }
        }
    }

    return $keys;
}

/**
 * 역대 1, 2, 3등 조합인지 검사한다.
 *
 * @param array $numbers
 * @param array $historicalKeys
 * @return bool
 */
function lottoFilterPassHistoricalTop3(
    array $numbers,
    array $historicalKeys
) {
    $key = lottoFilterCombinationKey($numbers);

    if ($key === '') {
        return false;
    }

    return !isset($historicalKeys[$key]);
}

/**
 * 직전 회차 기준 이월수와 이웃수를 계산한다.
 *
 * 이웃수는 직전 당첨번호 각각의 ±1 번호다.
 * 명세 그대로 이월수와 이웃수는 각각 독립 계산한다.
 *
 * @param array $numbers
 * @param array $previousNumbers
 * @return array
 */
function lottoFilterGetCarryNeighborCounts(
    array $numbers,
    array $previousNumbers
) {
    if (
        !lottoFilterIsValidCombination($numbers)
        || !lottoFilterIsValidCombination($previousNumbers)
    ) {
        return array(
            'carry_count' => 0,
            'neighbor_count' => 0,
        );
    }

    $previousLookup = array_fill_keys(
        array_map('intval', $previousNumbers),
        true
    );

    $neighborLookup = array();

    foreach ($previousNumbers as $previousNumber) {
        $previousNumber = (int) $previousNumber;

        $lower = $previousNumber - 1;
        $upper = $previousNumber + 1;

        if ($lower >= 1) {
            $neighborLookup[$lower] = true;
        }

        if ($upper <= 45) {
            $neighborLookup[$upper] = true;
        }
    }

    $carryCount = 0;
    $neighborCount = 0;

    foreach ($numbers as $number) {
        $number = (int) $number;

        if (isset($previousLookup[$number])) {
            $carryCount++;
        }

        if (isset($neighborLookup[$number])) {
            $neighborCount++;
        }
    }

    return array(
        'carry_count' => $carryCount,
        'neighbor_count' => $neighborCount,
    );
}

/**
 * 이월수 / 이웃수 필터를 검사한다.
 *
 * 각각 0~2개만 허용한다.
 *
 * @param array $numbers
 * @param array $previousNumbers
 * @return bool
 */
function lottoFilterPassCarryNeighbor(
    array $numbers,
    array $previousNumbers
) {
    $counts = lottoFilterGetCarryNeighborCounts(
        $numbers,
        $previousNumbers
    );

    return $counts['carry_count'] <= 2
        && $counts['neighbor_count'] <= 2;
}

/**
 * 필터 1~9 중 현재 확정된 필터를 명세 순서대로 실행한다.
 *
 * 적용:
 * 1. 역대 1~3등 조합 배제
 * 2. 연속번호
 * 3. 이월수 / 이웃수
 * 4. 홀짝 / 저고
 * 5. 총합
 * 6. AC
 * 7. 동끝수
 * 8. 멸구간
 * 9. 소수 / 3의 배수
 *
 * @param int $sumMin
 * @param int $sumMax
 * @param array $historicalKeys
 * @param array $previousNumbers
 * @return array
 */
/**
 * 1회부터 지정 회차까지 당첨번호 출현 이력을 조회한다.
 *
 * @param int $maxDrawNo
 * @return array
 */
function lottoFilterGetDrawNumberHistory($maxDrawNo)
{
    $maxDrawNo = (int) $maxDrawNo;

    if ($maxDrawNo < 1) {
        return array();
    }

    $history = array();

    $result = sql_query(
        "select
            draw_no,
            num_1,
            num_2,
            num_3,
            num_4,
            num_5,
            num_6
         from g5_lotto_result
         where draw_no <= '{$maxDrawNo}'
         order by draw_no asc",
        false
    );

    while ($result && ($row = sql_fetch_array($result))) {
        $drawNo = (int) $row['draw_no'];

        $numbers = array(
            (int) $row['num_1'],
            (int) $row['num_2'],
            (int) $row['num_3'],
            (int) $row['num_4'],
            (int) $row['num_5'],
            (int) $row['num_6'],
        );

        if (!lottoFilterIsValidCombination($numbers)) {
            continue;
        }

        $history[$drawNo] = $numbers;
    }

    return $history;
}

/**
 * 번호 하나의 전체/최근 출현 통계를 계산한다.
 *
 * gap은 실제 미출현 회차 수를 의미한다.
 *
 * @param int $number
 * @param array $history
 * @return array
 */
function lottoFilterAnalyzeNumberHistory(
    $number,
    array $history
) {
    $number = (int) $number;

    if ($number < 1 || $number > 45 || count($history) < 1) {
        return array();
    }

    $drawNos = array_keys($history);
    sort($drawNos, SORT_NUMERIC);

    $latestDrawNo = (int) end($drawNos);

    $appearanceDraws = array();
    $presenceByDraw = array();

    foreach ($drawNos as $drawNo) {
        $numbers = $history[$drawNo];

        $appeared = in_array(
            $number,
            $numbers,
            true
        );

        $presenceByDraw[$drawNo] = $appeared;

        if ($appeared) {
            $appearanceDraws[] = (int) $drawNo;
        }
    }

    $gaps = array();

    for ($i = 1; $i < count($appearanceDraws); $i++) {
        $gap = $appearanceDraws[$i]
            - $appearanceDraws[$i - 1]
            - 1;

        if ($gap < 0) {
            $gap = 0;
        }

        $gaps[] = $gap;
    }

    $averageGap = 0.0;
    $maxGap = 0;

    if (count($gaps) > 0) {
        $averageGap = array_sum($gaps) / count($gaps);
        $maxGap = max($gaps);
    }

    $lastAppearanceDraw = count($appearanceDraws) > 0
        ? (int) end($appearanceDraws)
        : 0;

    $currentGap = $lastAppearanceDraw > 0
        ? $latestDrawNo - $lastAppearanceDraw
        : $latestDrawNo;

    $currentStreak = 0;

    for ($i = count($drawNos) - 1; $i >= 0; $i--) {
        $drawNo = $drawNos[$i];

        if (empty($presenceByDraw[$drawNo])) {
            break;
        }

        $currentStreak++;
    }

    $continuation = array(
        'streak1_cases' => 0,
        'streak1_continued' => 0,
        'streak2_cases' => 0,
        'streak2_continued' => 0,
        'streak3_plus_cases' => 0,
        'streak3_plus_continued' => 0,
    );

    $streak = 0;
    $drawCount = count($drawNos);

    for ($i = 0; $i < $drawCount - 1; $i++) {
        $drawNo = $drawNos[$i];

        if (!empty($presenceByDraw[$drawNo])) {
            $streak++;
        } else {
            $streak = 0;
            continue;
        }

        $nextDrawNo = $drawNos[$i + 1];

        $continued = !empty(
            $presenceByDraw[$nextDrawNo]
        );

        if ($streak === 1) {
            $continuation['streak1_cases']++;

            if ($continued) {
                $continuation['streak1_continued']++;
            }
        } elseif ($streak === 2) {
            $continuation['streak2_cases']++;

            if ($continued) {
                $continuation['streak2_continued']++;
            }
        } else {
            $continuation['streak3_plus_cases']++;

            if ($continued) {
                $continuation['streak3_plus_continued']++;
            }
        }
    }

    $recent25Count = 0;
    $recent5Count = 0;
    $recent3Count = 0;

    $recent25Draws = array_slice($drawNos, -25);
    $recent5Draws = array_slice($drawNos, -5);
    $recent3Draws = array_slice($drawNos, -3);

    foreach ($recent25Draws as $drawNo) {
        if (!empty($presenceByDraw[$drawNo])) {
            $recent25Count++;
        }
    }

    foreach ($recent5Draws as $drawNo) {
        if (!empty($presenceByDraw[$drawNo])) {
            $recent5Count++;
        }
    }

    foreach ($recent3Draws as $drawNo) {
        if (!empty($presenceByDraw[$drawNo])) {
            $recent3Count++;
        }
    }

    $gapRatio = 0.0;

    if ($averageGap > 0) {
        $gapRatio = $currentGap / $averageGap;
    }

    $streak1Rate = $continuation['streak1_cases'] > 0
        ? (
            $continuation['streak1_continued']
            / $continuation['streak1_cases']
        )
        : 0.0;

    $streak2Rate = $continuation['streak2_cases'] > 0
        ? (
            $continuation['streak2_continued']
            / $continuation['streak2_cases']
        )
        : 0.0;

    $streak3PlusRate = $continuation[
        'streak3_plus_cases'
    ] > 0
        ? (
            $continuation['streak3_plus_continued']
            / $continuation['streak3_plus_cases']
        )
        : 0.0;

    return array(
        'number' => $number,
        'latest_draw_no' => $latestDrawNo,
        'total_appearances' => count($appearanceDraws),
        'average_gap' => $averageGap,
        'current_gap' => $currentGap,
        'max_gap' => $maxGap,
        'gap_ratio' => $gapRatio,
        'current_streak' => $currentStreak,
        'recent25_count' => $recent25Count,
        'recent5_count' => $recent5Count,
        'recent3_count' => $recent3Count,
        'streak1_cases' => $continuation['streak1_cases'],
        'streak1_continued' => $continuation['streak1_continued'],
        'streak1_rate' => $streak1Rate,
        'streak2_cases' => $continuation['streak2_cases'],
        'streak2_continued' => $continuation['streak2_continued'],
        'streak2_rate' => $streak2Rate,
        'streak3_plus_cases' => $continuation[
            'streak3_plus_cases'
        ],
        'streak3_plus_continued' => $continuation[
            'streak3_plus_continued'
        ],
        'streak3_plus_rate' => $streak3PlusRate,
    );
}

/**
 * 1~45번 전체 통계를 계산한다.
 *
 * @param int $maxDrawNo
 * @return array
 */
function lottoFilterGetAllNumberStatistics($maxDrawNo)
{
    $history = lottoFilterGetDrawNumberHistory(
        $maxDrawNo
    );

    if (count($history) < 1) {
        return array();
    }

    $statistics = array();

    for ($number = 1; $number <= 45; $number++) {
        $statistics[$number] =
            lottoFilterAnalyzeNumberHistory(
                $number,
                $history
            );
    }

    return $statistics;
}

/**
 * 숫자를 지정 범위로 제한한다.
 *
 * @param float $value
 * @param float $min
 * @param float $max
 * @return float
 */
function lottoFilterClamp($value, $min, $max)
{
    $value = (float) $value;
    $min = (float) $min;
    $max = (float) $max;

    if ($value < $min) {
        return $min;
    }

    if ($value > $max) {
        return $max;
    }

    return $value;
}

/**
 * 연속출현 지속률을 전체 로또 기본 출현률 방향으로 보정한다.
 *
 * 표본이 매우 적은 streak 2, streak 3+ 통계가
 * 과도하게 0% 또는 100%로 작동하지 않도록 한다.
 *
 * @param int $cases
 * @param int $continued
 * @return float
 */
function lottoFilterGetSmoothedContinuationRate(
    $cases,
    $continued
) {
    $cases = max(0, (int) $cases);
    $continued = max(0, (int) $continued);

    /*
     * 한 번호가 한 회차 6개 안에 포함될 기본 비율.
     */
    $baseline = 6 / 45;

    /*
     * 과거 20건의 가상 표본을 기본 비율에 둔다.
     * 실제 사례가 쌓일수록 실제 데이터의 영향이 커진다.
     */
    $priorCases = 20;
    $priorContinued = $priorCases * $baseline;

    return (
        $continued + $priorContinued
    ) / (
        $cases + $priorCases
    );
}

/**
 * 1~45 번호의 필터10 추천점수와
 * 필터11 위험점수를 계산한다.
 *
 * @param int $maxDrawNo
 * @return array
 */
function lottoFilterGetTrendNumberScores($maxDrawNo)
{
    $maxDrawNo = (int) $maxDrawNo;

    $history = lottoFilterGetDrawNumberHistory(
        $maxDrawNo
    );

    $statistics = lottoFilterGetAllNumberStatistics(
        $maxDrawNo
    );

    if (
        count($history) < 1
        || count($statistics) !== 45
    ) {
        return array();
    }

    $drawNos = array_keys($history);
    sort($drawNos, SORT_NUMERIC);

    $recent25Draws = array_slice($drawNos, -25);

    /*
     * 최근 25회 가중치:
     * 가장 오래된 회차 = 1
     * 최신 회차 = 25
     */
    $totalWeight = 0;

    for ($weight = 1; $weight <= 25; $weight++) {
        $totalWeight += $weight;
    }

    /*
     * 공정 추첨 기준 번호 하나의 회차당 기본 출현 비율.
     */
    $baselineRate = 6 / 45;

    $expectedRecent25 = 25 * $baselineRate;
    $expectedWeighted = $totalWeight * $baselineRate;

    $scores = array();

    for ($number = 1; $number <= 45; $number++) {
        $stat = $statistics[$number];

        $weighted25 = 0;
        $transitionCount = 0;
        $previousPresence = null;

        foreach ($recent25Draws as $index => $drawNo) {
            $present = in_array(
                $number,
                $history[$drawNo],
                true
            );

            if ($present) {
                $weighted25 += $index + 1;
            }

            if (
                $previousPresence !== null
                && $previousPresence !== $present
            ) {
                $transitionCount++;
            }

            $previousPresence = $present;
        }

        /*
         * 장기 미출현 점수.
         *
         * 평균 미출현 간격 도달 = 50점
         * 평균의 2배 이상 = 100점
         */
        $overdueScore = lottoFilterClamp(
            $stat['gap_ratio'] * 50,
            0,
            100
        );

        /*
         * 최근25회 출현 균형.
         *
         * 역사적 기본 기대치에 가까울수록 높다.
         * 적게 나온 경우도 완전히 나쁜 것으로 처리하지 않는다.
         */
        $recentDeviation = abs(
            $stat['recent25_count']
            - $expectedRecent25
        ) / $expectedRecent25;

        $recentBalanceScore = lottoFilterClamp(
            100 - ($recentDeviation * 50),
            0,
            100
        );

        /*
         * 최근 회차에 더 높은 가중치를 둔 출현 균형.
         */
        $weightedDeviation = abs(
            $weighted25 - $expectedWeighted
        ) / $expectedWeighted;

        $weightedBalanceScore = lottoFilterClamp(
            100 - ($weightedDeviation * 50),
            0,
            100
        );

        /*
         * 현재 연속출현 상태.
         *
         * 연속출현이 없으면 감점하지 않는다.
         * 연속출현 중이면 과거 동일 streak 이후
         * 실제 지속률을 사용한다.
         */
        $currentStreak = (int) $stat['current_streak'];
        $continuationRate = $baselineRate;

        if ($currentStreak === 1) {
            $continuationRate =
                lottoFilterGetSmoothedContinuationRate(
                    $stat['streak1_cases'],
                    $stat['streak1_continued']
                );
        } elseif ($currentStreak === 2) {
            $continuationRate =
                lottoFilterGetSmoothedContinuationRate(
                    $stat['streak2_cases'],
                    $stat['streak2_continued']
                );
        } elseif ($currentStreak >= 3) {
            $continuationRate =
                lottoFilterGetSmoothedContinuationRate(
                    $stat['streak3_plus_cases'],
                    $stat['streak3_plus_continued']
                );
        }

        $streakScore = 100.0;

        if ($currentStreak > 0) {
            $continuationRatio = $continuationRate
                / $baselineRate;

            $streakScore = lottoFilterClamp(
                $continuationRatio * 100,
                0,
                100
            );
        }

        /*
         * 최근25회 출현/미출현 변화 안정성.
         */
        $stabilityScore = lottoFilterClamp(
            100 - (
                ($transitionCount / 24) * 100
            ),
            0,
            100
        );

        /*
         * 필터10 번호 점수.
         */
        $trendScore =
            ($overdueScore * 0.35)
            + ($recentBalanceScore * 0.20)
            + ($weightedBalanceScore * 0.15)
            + ($streakScore * 0.20)
            + ($stabilityScore * 0.10);

        /*
         * 필터11 위험점수.
         *
         * 장기 미출현은 위험요소로 사용하지 않는다.
         */
        $saturation25 = 0.0;

        if (
            $stat['recent25_count']
            > $expectedRecent25
        ) {
            $saturation25 = lottoFilterClamp(
                (
                    (
                        $stat['recent25_count']
                        - $expectedRecent25
                    )
                    / $expectedRecent25
                ) * 100,
                0,
                100
            );
        }

        $recent5Risk = lottoFilterClamp(
            ($stat['recent5_count'] / 5) * 100,
            0,
            100
        );

        $recent3Risk = lottoFilterClamp(
            ($stat['recent3_count'] / 3) * 100,
            0,
            100
        );

        $repeatRisk = $currentStreak > 0
            ? 100 - $streakScore
            : 0.0;

        $riskScore =
            ($saturation25 * 0.40)
            + ($recent5Risk * 0.25)
            + ($recent3Risk * 0.20)
            + ($repeatRisk * 0.15);

        $scores[$number] = array_merge(
            $stat,
            array(
                'weighted25' => $weighted25,
                'transition_count' => $transitionCount,
                'overdue_score' => $overdueScore,
                'recent_balance_score' => $recentBalanceScore,
                'weighted_balance_score' => $weightedBalanceScore,
                'continuation_rate' => $continuationRate,
                'streak_score' => $streakScore,
                'stability_score' => $stabilityScore,
                'trend_score' => $trendScore,
                'risk_score' => $riskScore,
            )
        );
    }

    return $scores;
}

/**
 * 위험점수가 높은 추천 제외수 N개를 선정한다.
 *
 * @param array $scores
 * @param int $count
 * @return array
 */
function lottoFilterGetRecommendedExcludedNumbers(
    array $scores,
    $count = 6
) {
    $count = max(1, (int) $count);

    $rows = array_values($scores);

    usort(
        $rows,
        function ($a, $b) {
            if ($a['risk_score'] != $b['risk_score']) {
                return $a['risk_score'] < $b['risk_score']
                    ? 1
                    : -1;
            }

            if (
                $a['recent25_count']
                !== $b['recent25_count']
            ) {
                return $b['recent25_count']
                    <=> $a['recent25_count'];
            }

            if (
                $a['recent5_count']
                !== $b['recent5_count']
            ) {
                return $b['recent5_count']
                    <=> $a['recent5_count'];
            }

            if ($a['weighted25'] !== $b['weighted25']) {
                return $b['weighted25']
                    <=> $a['weighted25'];
            }

            return $a['number'] <=> $b['number'];
        }
    );

    $excluded = array();

    foreach (array_slice($rows, 0, $count) as $row) {
        $excluded[] = (int) $row['number'];
    }

    sort($excluded, SORT_NUMERIC);

    return $excluded;
}

/**
 * 조합 6개의 필터10 점수를 계산한다.
 *
 * 번호별 trend_score 평균값을 사용한다.
 *
 * @param array $numbers
 * @param array $numberScores
 * @return float
 */
function lottoFilterCalculateCombinationTrendScore(
    array $numbers,
    array $numberScores
) {
    if (!lottoFilterIsValidCombination($numbers)) {
        return 0.0;
    }

    $totalScore = 0.0;

    foreach ($numbers as $number) {
        $number = (int) $number;

        if (
            !isset($numberScores[$number])
            || !isset(
                $numberScores[$number]['trend_score']
            )
        ) {
            return 0.0;
        }

        $totalScore += (float) $numberScores[
            $number
        ]['trend_score'];
    }

    return $totalScore / 6;
}

/**
 * 추천 제외수가 포함되어 있는지 검사한다.
 *
 * 제외수 하나라도 포함되면 false.
 *
 * @param array $numbers
 * @param array $excludedNumbers
 * @return bool
 */
function lottoFilterPassRecommendedExclusions(
    array $numbers,
    array $excludedNumbers
) {
    if (!lottoFilterIsValidCombination($numbers)) {
        return false;
    }

    $excludedLookup = array();

    foreach ($excludedNumbers as $number) {
        $number = (int) $number;

        if ($number >= 1 && $number <= 45) {
            $excludedLookup[$number] = true;
        }
    }

    foreach ($numbers as $number) {
        if (isset($excludedLookup[(int) $number])) {
            return false;
        }
    }

    return true;
}

function lottoFilterRunCorePipeline(
    $sumMin,
    $sumMax,
    array $historicalKeys,
    array $previousNumbers,
    $onCoreCandidate = null
) {
    $sumMin = (int) $sumMin;
    $sumMax = (int) $sumMax;

    $counts = array(
        'total_count' => 0,
        'historical_pass_count' => 0,
        'consecutive_pass_count' => 0,
        'carry_neighbor_pass_count' => 0,
        'balance_pass_count' => 0,
        'sum_pass_count' => 0,
        'ac_pass_count' => 0,
        'same_last_digit_pass_count' => 0,
        'empty_zone_pass_count' => 0,
        'math_balance_pass_count' => 0,
        'final_core_pass_count' => 0,
    );

    for ($n1 = 1; $n1 <= 40; $n1++) {
        for ($n2 = $n1 + 1; $n2 <= 41; $n2++) {
            for ($n3 = $n2 + 1; $n3 <= 42; $n3++) {
                for ($n4 = $n3 + 1; $n4 <= 43; $n4++) {
                    for ($n5 = $n4 + 1; $n5 <= 44; $n5++) {
                        for ($n6 = $n5 + 1; $n6 <= 45; $n6++) {
                            $counts['total_count']++;

                            $numbers = array(
                                $n1,
                                $n2,
                                $n3,
                                $n4,
                                $n5,
                                $n6,
                            );

                            /*
                             * 필터 1
                             */
                            if (
                                !lottoFilterPassHistoricalTop3(
                                    $numbers,
                                    $historicalKeys
                                )
                            ) {
                                continue;
                            }

                            $counts['historical_pass_count']++;

                            /*
                             * 필터 2
                             */
                            if (
                                lottoFilterGetMaxConsecutive($numbers) > 3
                            ) {
                                continue;
                            }

                            $counts['consecutive_pass_count']++;

                            /*
                             * 필터 3
                             */
                            if (
                                !lottoFilterPassCarryNeighbor(
                                    $numbers,
                                    $previousNumbers
                                )
                            ) {
                                continue;
                            }

                            $counts['carry_neighbor_pass_count']++;

                            /*
                             * 기본 분석
                             */
                            $analysis = lottoFilterAnalyzeCombination(
                                $numbers
                            );

                            if ($analysis === false) {
                                continue;
                            }

                            /*
                             * 필터 4
                             */
                            if (
                                $analysis['odd_count'] < 2
                                || $analysis['odd_count'] > 5
                                || $analysis['low_count'] < 2
                                || $analysis['low_count'] > 4
                            ) {
                                continue;
                            }

                            $counts['balance_pass_count']++;

                            /*
                             * 필터 5
                             */
                            if (
                                !lottoFilterPassSum(
                                    $analysis['sum_value'],
                                    $sumMin,
                                    $sumMax
                                )
                            ) {
                                continue;
                            }

                            $counts['sum_pass_count']++;

                            /*
                             * 필터 6
                             */
                            if ($analysis['ac_value'] < 7) {
                                continue;
                            }

                            $counts['ac_pass_count']++;

                            /*
                             * 필터 7
                             */
                            if (
                                $analysis['max_same_last_digit'] > 2
                            ) {
                                continue;
                            }

                            $counts[
                                'same_last_digit_pass_count'
                            ]++;

                            /*
                             * 필터 8
                             */
                            if (
                                $analysis['empty_zone_count'] < 1
                                || $analysis['empty_zone_count'] > 2
                            ) {
                                continue;
                            }

                            $counts['empty_zone_pass_count']++;

                            /*
                             * 필터 9
                             */
                            if (
                                $analysis['prime_count'] < 1
                                || $analysis['prime_count'] > 3
                                || $analysis['multiple3_count'] < 1
                                || $analysis['multiple3_count'] > 3
                            ) {
                                continue;
                            }

                            $counts[
                                'math_balance_pass_count'
                            ]++;

                            $counts['final_core_pass_count']++;

                            if ($onCoreCandidate !== null) {
                                $onCoreCandidate(
                                    $numbers,
                                    $analysis
                                );
                            }
                        }
                    }
                }
            }
        }
    }

    return $counts;
}

/**
 * 필터 1~11 전체 파이프라인을 실행한다.
 *
 * 필터 10은 조합 Trend 점수를 계산한다.
 * 필터 11은 추천 제외수 포함 조합을 제거한다.
 *
 * 전체 조합을 메모리에 저장하지 않고 집계값만 반환한다.
 *
 * @param int $sumMin
 * @param int $sumMax
 * @param array $historicalKeys
 * @param array $previousNumbers
 * @param array $numberScores
 * @param array $excludedNumbers
 * @return array
 */
function lottoFilterRunFullPipeline(
    $sumMin,
    $sumMax,
    array $historicalKeys,
    array $previousNumbers,
    array $numberScores,
    array $excludedNumbers,
    $onFinalCandidate = null
) {
    $trendScoredCount = 0;
    $exclusionPassCount = 0;

    $trendScoreSum = 0.0;
    $trendScoreMin = null;
    $trendScoreMax = null;

    $highestScore = null;
    $highestCombination = array();

    $coreCounts = lottoFilterRunCorePipeline(
        $sumMin,
        $sumMax,
        $historicalKeys,
        $previousNumbers,
        function (
            array $numbers,
            array $analysis
        ) use (
            $numberScores,
            $excludedNumbers,
            $onFinalCandidate,
            &$trendScoredCount,
            &$exclusionPassCount,
            &$trendScoreSum,
            &$trendScoreMin,
            &$trendScoreMax,
            &$highestScore,
            &$highestCombination
        ) {
            /*
             * 필터 10
             */
            $trendScore =
                lottoFilterCalculateCombinationTrendScore(
                    $numbers,
                    $numberScores
                );

            $trendScoredCount++;
            $trendScoreSum += $trendScore;

            if (
                $trendScoreMin === null
                || $trendScore < $trendScoreMin
            ) {
                $trendScoreMin = $trendScore;
            }

            if (
                $trendScoreMax === null
                || $trendScore > $trendScoreMax
            ) {
                $trendScoreMax = $trendScore;
            }

            /*
             * 필터 11
             */
            if (
                !lottoFilterPassRecommendedExclusions(
                    $numbers,
                    $excludedNumbers
                )
            ) {
                return;
            }

            $exclusionPassCount++;

            if ($onFinalCandidate !== null) {
                $onFinalCandidate(
                    $numbers,
                    $analysis,
                    $trendScore
                );
            }

            if (
                $highestScore === null
                || $trendScore > $highestScore
            ) {
                $highestScore = $trendScore;
                $highestCombination = $numbers;
            }
        }
    );

    $averageTrendScore = 0.0;

    if ($trendScoredCount > 0) {
        $averageTrendScore =
            $trendScoreSum / $trendScoredCount;
    }

    return array_merge(
        $coreCounts,
        array(
            'trend_scored_count' =>
                $trendScoredCount,

            'exclusion_pass_count' =>
                $exclusionPassCount,

            'final_pass_count' =>
                $exclusionPassCount,

            'trend_score_min' =>
                $trendScoreMin,

            'trend_score_max' =>
                $trendScoreMax,

            'trend_score_average' =>
                $averageTrendScore,

            'highest_score' =>
                $highestScore,

            'highest_combination' =>
                $highestCombination,
        )
    );
}

function lottoFilterRunStaticPipeline($sumMin, $sumMax)
{
    $sumMin = (int) $sumMin;
    $sumMax = (int) $sumMax;

    $counts = array(
        'total_count' => 0,
        'consecutive_pass_count' => 0,
        'balance_pass_count' => 0,
        'sum_pass_count' => 0,
        'ac_pass_count' => 0,
        'same_last_digit_pass_count' => 0,
        'empty_zone_pass_count' => 0,
        'math_balance_pass_count' => 0,
        'final_static_pass_count' => 0,
    );

    for ($n1 = 1; $n1 <= 40; $n1++) {
        for ($n2 = $n1 + 1; $n2 <= 41; $n2++) {
            for ($n3 = $n2 + 1; $n3 <= 42; $n3++) {
                for ($n4 = $n3 + 1; $n4 <= 43; $n4++) {
                    for ($n5 = $n4 + 1; $n5 <= 44; $n5++) {
                        for ($n6 = $n5 + 1; $n6 <= 45; $n6++) {
                            $counts['total_count']++;

                            $numbers = array(
                                $n1,
                                $n2,
                                $n3,
                                $n4,
                                $n5,
                                $n6,
                            );

                            /*
                             * 필터 2
                             * 4연번 이상 배제
                             */
                            if (
                                lottoFilterGetMaxConsecutive($numbers) > 3
                            ) {
                                continue;
                            }

                            $counts['consecutive_pass_count']++;

                            /*
                             * 필터 4
                             * 홀짝:
                             * 홀수 2,3,4,5개 허용
                             *
                             * 저고:
                             * 1~22가 2,3,4개 허용
                             */
                            $oddCount = 0;
                            $lowCount = 0;

                            foreach ($numbers as $number) {
                                if ($number % 2 !== 0) {
                                    $oddCount++;
                                }

                                if ($number <= 22) {
                                    $lowCount++;
                                }
                            }

                            if (
                                $oddCount < 2
                                || $oddCount > 5
                                || $lowCount < 2
                                || $lowCount > 4
                            ) {
                                continue;
                            }

                            $counts['balance_pass_count']++;

                            /*
                             * 필터 5
                             * 현재 저장된 총합 설정값 사용
                             */
                            $sum = array_sum($numbers);

                            if (
                                !lottoFilterPassSum(
                                    $sum,
                                    $sumMin,
                                    $sumMax
                                )
                            ) {
                                continue;
                            }

                            $counts['sum_pass_count']++;

                            $analysis = lottoFilterAnalyzeCombination(
                                $numbers
                            );

                            if ($analysis === false) {
                                continue;
                            }

                            /*
                             * 필터 6
                             * AC >= 7
                             */
                            if ($analysis['ac_value'] < 7) {
                                continue;
                            }

                            $counts['ac_pass_count']++;

                            /*
                             * 필터 7
                             * 같은 끝자리 최대 2개
                             */
                            if (
                                $analysis['max_same_last_digit'] > 2
                            ) {
                                continue;
                            }

                            $counts[
                                'same_last_digit_pass_count'
                            ]++;

                            /*
                             * 필터 8
                             * 빈 번호대 1~2개
                             */
                            if (
                                $analysis['empty_zone_count'] < 1
                                || $analysis['empty_zone_count'] > 2
                            ) {
                                continue;
                            }

                            $counts['empty_zone_pass_count']++;

                            /*
                             * 필터 9
                             * 소수 1~3개
                             * 3의 배수 1~3개
                             */
                            if (
                                $analysis['prime_count'] < 1
                                || $analysis['prime_count'] > 3
                                || $analysis['multiple3_count'] < 1
                                || $analysis['multiple3_count'] > 3
                            ) {
                                continue;
                            }

                            $counts[
                                'math_balance_pass_count'
                            ]++;

                            $counts['final_static_pass_count']++;
                        }
                    }
                }
            }
        }
    }

    return $counts;
}

function lottoFilterRunAnalysisPipeline($sumMin, $sumMax)
{
    $sumMin = (int) $sumMin;
    $sumMax = (int) $sumMax;

    $totalCount = 0;
    $sumPassCount = 0;
    $analyzedCount = 0;

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

                            if (!lottoFilterPassSum($sum, $sumMin, $sumMax)) {
                                continue;
                            }

                            $sumPassCount++;

                            $analysis = lottoFilterAnalyzeCombination(array(
                                $n1,
                                $n2,
                                $n3,
                                $n4,
                                $n5,
                                $n6,
                            ));

                            if ($analysis === false) {
                                continue;
                            }

                            $analyzedCount++;
                        }
                    }
                }
            }
        }
    }

    return array(
        'total_count' => $totalCount,
        'sum_pass_count' => $sumPassCount,
        'analyzed_count' => $analyzedCount,
    );
}

/**
 * 필터 실행 이력을 시작 상태로 생성하거나 초기화한다.
 *
 * 같은 회차를 다시 실행하면 기존 l_filter_run 행을 재사용한다.
 *
 * @param int $drawNo
 * @param int $sourceDrawNo
 * @param string $createdBy
 * @return int
 */
/**
 * 최종 필터 후보를 배치 저장한다.
 *
 * @param int $runId
 * @param int $drawNo
 * @param array $rows
 * @return bool
 */
function lottoFilterInsertCandidateBatch(
    $runId,
    $drawNo,
    array $rows
) {
    $runId = (int) $runId;
    $drawNo = (int) $drawNo;

    if (
        $runId < 1
        || $drawNo < 1
        || count($rows) < 1
    ) {
        return false;
    }

    $values = array();

    foreach ($rows as $row) {
        $numbers = isset($row['numbers'])
            ? $row['numbers']
            : array();

        if (!lottoFilterIsValidCombination($numbers)) {
            return false;
        }

        $analysis = isset($row['analysis'])
            && is_array($row['analysis'])
            ? $row['analysis']
            : array();

        $rankNo = isset($row['rank_no'])
            ? (int) $row['rank_no']
            : 0;

        if ($rankNo < 1) {
            return false;
        }

        $score = isset($row['score'])
            ? (float) $row['score']
            : 0.0;

        $carryCount = isset($row['carry_count'])
            ? (int) $row['carry_count']
            : 0;

        $neighborCount = isset($row['neighbor_count'])
            ? (int) $row['neighbor_count']
            : 0;

        $analysisData = json_encode(
            array(
                'max_same_last_digit' =>
                    isset($analysis['max_same_last_digit'])
                        ? (int) $analysis['max_same_last_digit']
                        : 0,
            ),
            JSON_UNESCAPED_UNICODE
        );

        if ($analysisData === false) {
            return false;
        }

        $analysisData = sql_escape_string(
            $analysisData
        );

        $values[] = "("
            . $runId . ","
            . $drawNo . ","
            . (int) $numbers[0] . ","
            . (int) $numbers[1] . ","
            . (int) $numbers[2] . ","
            . (int) $numbers[3] . ","
            . (int) $numbers[4] . ","
            . (int) $numbers[5] . ","
            . number_format($score, 6, '.', '') . ","
            . $rankNo . ","
            . (int) $analysis['sum_value'] . ","
            . (int) $analysis['ac_value'] . ","
            . (int) $analysis['odd_count'] . ","
            . (int) $analysis['low_count'] . ","
            . $carryCount . ","
            . $neighborCount . ","
            . (int) $analysis['prime_count'] . ","
            . (int) $analysis['multiple3_count'] . ","
            . (int) $analysis['max_consecutive'] . ","
            . (int) $analysis['empty_zone_count'] . ","
            . "'" . $analysisData . "'"
            . ")";
    }

    $sql = "
        insert into l_filter_candidate (
            lfr_id,
            draw_no,
            num1,
            num2,
            num3,
            num4,
            num5,
            num6,
            score,
            rank_no,
            sum_value,
            ac_value,
            odd_count,
            low_count,
            carry_count,
            neighbor_count,
            prime_count,
            multiple3_count,
            max_consecutive,
            empty_zone_count,
            analysis_data
        ) values
        " . implode(",\n", $values);

    return sql_query($sql, false) !== false;
}

/**
 * 저장 후보를 점수순으로 재랭킹한다.
 *
 * @param int $drawNo
 * @return array
 */
function lottoFilterRankStoredCandidates($drawNo)
{
    $drawNo = (int) $drawNo;

    if ($drawNo < 1) {
        return array(
            'success' => false,
            'count' => 0,
        );
    }

    $row = sql_fetch(
        "select count(*) as cnt
           from l_filter_candidate
          where draw_no = '{$drawNo}'",
        false
    );

    $count = isset($row['cnt'])
        ? (int) $row['cnt']
        : 0;

    if ($count < 1) {
        return array(
            'success' => false,
            'count' => 0,
        );
    }

    /*
     * 전체 로또 조합은 8,145,060개이므로
     * 9,000,000 이상을 임시 rank 영역으로 사용한다.
     */
    $result = sql_query(
        "update l_filter_candidate
            set rank_no = rank_no + 9000000
          where draw_no = '{$drawNo}'",
        false
    );

    if ($result === false) {
        return array(
            'success' => false,
            'count' => $count,
        );
    }

    $result = sql_query(
        "update l_filter_candidate as c
         join (
             select
                 lfc_id,
                 row_number() over (
                     order by
                         score desc,
                         num1 asc,
                         num2 asc,
                         num3 asc,
                         num4 asc,
                         num5 asc,
                         num6 asc,
                         lfc_id asc
                 ) as new_rank
             from l_filter_candidate
             where draw_no = '{$drawNo}'
         ) as ranked
             on ranked.lfc_id = c.lfc_id
         set c.rank_no = ranked.new_rank
         where c.draw_no = '{$drawNo}'",
        false
    );

    if ($result === false) {
        return array(
            'success' => false,
            'count' => $count,
        );
    }

    $check = sql_fetch(
        "select
            count(*) as row_count,
            min(rank_no) as min_rank,
            max(rank_no) as max_rank,
            count(distinct rank_no) as distinct_rank_count
         from l_filter_candidate
         where draw_no = '{$drawNo}'",
        false
    );

    $rowCount = (int) $check['row_count'];
    $minRank = (int) $check['min_rank'];
    $maxRank = (int) $check['max_rank'];
    $distinctRankCount =
        (int) $check['distinct_rank_count'];

    return array(
        'success' =>
            $rowCount === $count
            && $minRank === 1
            && $maxRank === $count
            && $distinctRankCount === $count,

        'count' => $count,
        'min_rank' => $minRank,
        'max_rank' => $maxRank,
        'distinct_rank_count' =>
            $distinctRankCount,
    );
}


function lottoFilterStartRun($drawNo, $sourceDrawNo, $createdBy)
{
    $drawNo = (int) $drawNo;
    $sourceDrawNo = (int) $sourceDrawNo;
    $createdBy = sql_escape_string(trim((string) $createdBy));

    if ($drawNo < 1) {
        return 0;
    }

    if ($sourceDrawNo < 0) {
        $sourceDrawNo = 0;
    }

    $sql = "
        insert into l_filter_run
        set
            draw_no = '{$drawNo}',
            source_draw_no = '{$sourceDrawNo}',
            status = 'running',
            total_combinations = 8145060,
            candidate_count = 0,
            excluded_numbers = '',
            started_at = now(),
            filtered_at = null,
            ranked_at = null,
            distributed_at = null,
            completed_at = null,
            last_error = null,
            created_by = '{$createdBy}'
        on duplicate key update
            lfr_id = last_insert_id(lfr_id),
            source_draw_no = values(source_draw_no),
            status = 'running',
            total_combinations = 8145060,
            candidate_count = 0,
            excluded_numbers = '',
            started_at = now(),
            filtered_at = null,
            ranked_at = null,
            distributed_at = null,
            completed_at = null,
            last_error = null,
            created_by = values(created_by)
    ";

    sql_query($sql);

    return (int) sql_insert_id();
}

/**
 * 필터 실행 성공 결과를 기록한다.
 *
 * @param int $runId
 * @param int $totalCount
 * @param int $candidateCount
 * @return bool
 */
function lottoFilterCompleteFiltering(
    $runId,
    $totalCount,
    $candidateCount
) {
    $runId = (int) $runId;
    $totalCount = (int) $totalCount;
    $candidateCount = (int) $candidateCount;

    if ($runId < 1) {
        return false;
    }

    $sql = "
        update l_filter_run
        set
            status = 'filtered',
            total_combinations = '{$totalCount}',
            candidate_count = '{$candidateCount}',
            filtered_at = now(),
            last_error = null
        where lfr_id = '{$runId}'
    ";

    sql_query($sql);

    return true;
}

/**
 * 필터 실행 실패 상태를 기록한다.
 *
 * @param int $runId
 * @param string $errorMessage
 * @return bool
 */
function lottoFilterFailRun($runId, $errorMessage)
{
    $runId = (int) $runId;

    if ($runId < 1) {
        return false;
    }

    $errorMessage = sql_escape_string(
        mb_substr((string) $errorMessage, 0, 5000)
    );

    $sql = "
        update l_filter_run
        set
            status = 'failed',
            last_error = '{$errorMessage}'
        where lfr_id = '{$runId}'
    ";

    sql_query($sql);

    return true;
}

/**
 * 실제 필터 파이프라인을 실행하고 l_filter_run에 결과를 기록한다.
 *
 * 현재 단계에서는 후보 번호 자체는 저장하지 않는다.
 *
 * @param int $drawNo
 * @param int $sourceDrawNo
 * @param string $createdBy
 * @param int $sumMin
 * @param int $sumMax
 * @return array
 */
/**
 * 후보 저장과 랭킹이 완료된 필터 실행을 기록한다.
 *
 * @param int $runId
 * @param int $totalCount
 * @param int $candidateCount
 * @param array $excludedNumbers
 * @return bool
 */
function lottoFilterCompleteStoredRun(
    $runId,
    $totalCount,
    $candidateCount,
    array $excludedNumbers
) {
    $runId = (int) $runId;
    $totalCount = (int) $totalCount;
    $candidateCount = (int) $candidateCount;

    if ($runId < 1) {
        return false;
    }

    $excluded = array();

    foreach ($excludedNumbers as $number) {
        $number = (int) $number;

        if ($number >= 1 && $number <= 45) {
            $excluded[$number] = $number;
        }
    }

    sort($excluded, SORT_NUMERIC);

    $excludedText = sql_escape_string(
        implode(',', $excluded)
    );

    $result = sql_query(
        "update l_filter_run
         set
            status = 'filtered',
            total_combinations = '{$totalCount}',
            candidate_count = '{$candidateCount}',
            excluded_numbers = '{$excludedText}',
            filtered_at = now(),
            ranked_at = now(),
            last_error = null
         where lfr_id = '{$runId}'",
        false
    );

    return $result !== false;
}

/**
 * 필터 1~11 실행부터 후보 저장/랭킹까지 수행한다.
 *
 * 기존 후보가 있는 회차는 자동 삭제하지 않고 실행을 거부한다.
 *
 * @param int $drawNo
 * @param int $sourceDrawNo
 * @param string $createdBy
 * @param int $sumMin
 * @param int $sumMax
 * @param int $batchSize
 * @return array
 */
function lottoFilterExecuteStoredRun(
    $drawNo,
    $sourceDrawNo,
    $createdBy,
    $sumMin,
    $sumMax,
    $batchSize = 500
) {
    $drawNo = (int) $drawNo;
    $sourceDrawNo = (int) $sourceDrawNo;
    $sumMin = (int) $sumMin;
    $sumMax = (int) $sumMax;
    $batchSize = max(1, (int) $batchSize);

    if (
        $drawNo < 1
        || $sourceDrawNo < 1
        || $sumMin > $sumMax
    ) {
        return array(
            'success' => false,
            'run_id' => 0,
            'error' => '필터 실행 입력값이 올바르지 않습니다.',
        );
    }

    $existing = sql_fetch(
        "select count(*) as cnt
         from l_filter_candidate
         where draw_no = '{$drawNo}'",
        false
    );

    $existingCount = isset($existing['cnt'])
        ? (int) $existing['cnt']
        : 0;

    /*
     * 기존 결과를 임의로 삭제하지 않는다.
     */
    if ($existingCount > 0) {
        return array(
            'success' => false,
            'run_id' => 0,
            'error' => '이미 저장된 후보가 있는 회차입니다.',
        );
    }

    $runId = lottoFilterStartRun(
        $drawNo,
        $sourceDrawNo,
        $createdBy
    );

    if ($runId < 1) {
        return array(
            'success' => false,
            'run_id' => 0,
            'error' => '필터 실행 이력을 생성하지 못했습니다.',
        );
    }

    try {
        $previousNumbers =
            lottoFilterGetResultNumbers(
                $sourceDrawNo
            );

        if (
            !lottoFilterIsValidCombination(
                $previousNumbers
            )
        ) {
            throw new RuntimeException(
                '직전 회차 당첨번호를 확인하지 못했습니다.'
            );
        }

        $historicalKeys =
            lottoFilterGetHistoricalTop3Keys(
                $sourceDrawNo
            );

        if (count($historicalKeys) < 1) {
            throw new RuntimeException(
                '과거 당첨조합 데이터를 확인하지 못했습니다.'
            );
        }

        $numberScores =
            lottoFilterGetTrendNumberScores(
                $sourceDrawNo
            );

        if (count($numberScores) !== 45) {
            throw new RuntimeException(
                '번호별 Trend 통계를 생성하지 못했습니다.'
            );
        }

        $excludedNumbers =
            lottoFilterGetRecommendedExcludedNumbers(
                $numberScores,
                6
            );

        if (count($excludedNumbers) !== 6) {
            throw new RuntimeException(
                '추천 제외수 6개를 생성하지 못했습니다.'
            );
        }

        $buffer = array();
        $storedCount = 0;

        $result = lottoFilterRunFullPipeline(
            $sumMin,
            $sumMax,
            $historicalKeys,
            $previousNumbers,
            $numberScores,
            $excludedNumbers,
            function (
                array $numbers,
                array $analysis,
                $trendScore
            ) use (
                $runId,
                $drawNo,
                $previousNumbers,
                $batchSize,
                &$buffer,
                &$storedCount
            ) {
                $carryNeighbor =
                    lottoFilterGetCarryNeighborCounts(
                        $numbers,
                        $previousNumbers
                    );

                $storedCount++;

                $buffer[] = array(
                    'numbers' => $numbers,
                    'analysis' => $analysis,
                    'score' => $trendScore,
                    'rank_no' => $storedCount,
                    'carry_count' =>
                        $carryNeighbor['carry_count'],
                    'neighbor_count' =>
                        $carryNeighbor['neighbor_count'],
                );

                if (count($buffer) >= $batchSize) {
                    if (
                        !lottoFilterInsertCandidateBatch(
                            $runId,
                            $drawNo,
                            $buffer
                        )
                    ) {
                        throw new RuntimeException(
                            '후보 배치 저장에 실패했습니다.'
                        );
                    }

                    $buffer = array();
                }
            }
        );

        if (count($buffer) > 0) {
            if (
                !lottoFilterInsertCandidateBatch(
                    $runId,
                    $drawNo,
                    $buffer
                )
            ) {
                throw new RuntimeException(
                    '마지막 후보 배치 저장에 실패했습니다.'
                );
            }
        }

        if (
            $storedCount
            !== (int) $result['final_pass_count']
        ) {
            throw new RuntimeException(
                '필터 후보 수와 저장 후보 수가 다릅니다.'
            );
        }

        $rankResult =
            lottoFilterRankStoredCandidates(
                $drawNo
            );

        if (
            !isset($rankResult['success'])
            || !$rankResult['success']
        ) {
            throw new RuntimeException(
                '후보 랭킹에 실패했습니다.'
            );
        }

        if (
            !lottoFilterCompleteStoredRun(
                $runId,
                $result['total_count'],
                $storedCount,
                $excludedNumbers
            )
        ) {
            throw new RuntimeException(
                '필터 실행 결과 기록에 실패했습니다.'
            );
        }

        return array(
            'success' => true,
            'run_id' => $runId,
            'total_count' =>
                (int) $result['total_count'],
            'candidate_count' => $storedCount,
            'excluded_numbers' => $excludedNumbers,
            'rank_count' =>
                (int) $rankResult['count'],
        );
    } catch (Throwable $e) {
        /*
         * 실행 시작 전에 후보가 0건임을 확인했으므로
         * 이 run에서 생성된 부분 결과만 정리한다.
         */
        sql_query(
            "delete from l_filter_candidate
             where lfr_id = '{$runId}'
               and draw_no = '{$drawNo}'",
            false
        );

        lottoFilterFailRun(
            $runId,
            $e->getMessage()
        );

        return array(
            'success' => false,
            'run_id' => $runId,
            'error' => $e->getMessage(),
        );
    }
}


function lottoFilterExecuteRun(
    $drawNo,
    $sourceDrawNo,
    $createdBy,
    $sumMin,
    $sumMax
) {
    return lottoFilterExecuteStoredRun(
        $drawNo,
        $sourceDrawNo,
        $createdBy,
        $sumMin,
        $sumMax
    );
}
