<?php

$_SERVER['SERVER_PORT'] = '80';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

include_once("_common.php");
include_once(
    G5_PATH . "/include/lotto_filter.lib.php"
);

$testDrawNo = 4000000001;
$sourceDrawNo = 1236;
$expectedCount = 836859;

sql_query(
    "delete from l_filter_candidate
      where draw_no = '{$testDrawNo}'"
);

sql_query(
    "delete from l_filter_run
      where draw_no = '{$testDrawNo}'"
);

$runId = lottoFilterStartRun(
    $testDrawNo,
    $sourceDrawNo,
    'storage-test'
);

if ($runId < 1) {
    echo "FAIL: test run could not be created.\n";
    exit(1);
}

try {
    $previousNumbers =
        lottoFilterGetResultNumbers(
            $sourceDrawNo
        );

    $historicalKeys =
        lottoFilterGetHistoricalTop3Keys(
            $sourceDrawNo
        );

    $numberScores =
        lottoFilterGetTrendNumberScores(
            $sourceDrawNo
        );

    $excludedNumbers =
        lottoFilterGetRecommendedExcludedNumbers(
            $numberScores,
            6
        );

    if (
        !lottoFilterIsValidCombination(
            $previousNumbers
        )
        || count($historicalKeys) < 1
        || count($numberScores) !== 45
        || count($excludedNumbers) !== 6
    ) {
        throw new RuntimeException(
            '필터 입력 데이터가 올바르지 않습니다.'
        );
    }

    $buffer = array();
    $storedCount = 0;
    $batchSize = 500;

    $startedAt = microtime(true);

    $result = lottoFilterRunFullPipeline(
        100,
        190,
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
            $testDrawNo,
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
                        $testDrawNo,
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
                $testDrawNo,
                $buffer
            )
        ) {
            throw new RuntimeException(
                '마지막 후보 배치 저장에 실패했습니다.'
            );
        }

        $buffer = array();
    }

    $filterElapsed =
        microtime(true) - $startedAt;

    if ($storedCount !== $expectedCount) {
        throw new RuntimeException(
            '저장 후보 수가 예상값과 다릅니다.'
        );
    }

    if (
        (int) $result['final_pass_count']
        !== $expectedCount
    ) {
        throw new RuntimeException(
            '전체 필터 후보 수가 예상값과 다릅니다.'
        );
    }

    $dbCountRow = sql_fetch(
        "select count(*) as cnt
           from l_filter_candidate
          where draw_no = '{$testDrawNo}'",
        false
    );

    $dbCount = isset($dbCountRow['cnt'])
        ? (int) $dbCountRow['cnt']
        : 0;

    if ($dbCount !== $expectedCount) {
        throw new RuntimeException(
            'DB 저장 행 수가 예상값과 다릅니다.'
        );
    }

    $rankStartedAt = microtime(true);

    $rankResult =
        lottoFilterRankStoredCandidates(
            $testDrawNo
        );

    $rankElapsed =
        microtime(true) - $rankStartedAt;

    if (
        !isset($rankResult['success'])
        || !$rankResult['success']
    ) {
        throw new RuntimeException(
            '후보 순위 부여 검증에 실패했습니다.'
        );
    }

    $rankOne = sql_fetch(
        "select
            rank_no,
            score,
            num1,
            num2,
            num3,
            num4,
            num5,
            num6
         from l_filter_candidate
         where draw_no = '{$testDrawNo}'
           and rank_no = 1
         limit 1",
        false
    );

    $rankOneNumbers = array(
        (int) $rankOne['num1'],
        (int) $rankOne['num2'],
        (int) $rankOne['num3'],
        (int) $rankOne['num4'],
        (int) $rankOne['num5'],
        (int) $rankOne['num6'],
    );

    $duplicateNumbers = sql_fetch(
        "select count(*) as duplicate_groups
         from (
             select
                 num1,
                 num2,
                 num3,
                 num4,
                 num5,
                 num6,
                 count(*) as cnt
             from l_filter_candidate
             where draw_no = '{$testDrawNo}'
             group by
                 num1,
                 num2,
                 num3,
                 num4,
                 num5,
                 num6
             having count(*) > 1
         ) as duplicated",
        false
    );

    $duplicateGroupCount =
        isset(
            $duplicateNumbers[
                'duplicate_groups'
            ]
        )
            ? (int) $duplicateNumbers[
                'duplicate_groups'
            ]
            : 0;

    if ($duplicateGroupCount !== 0) {
        throw new RuntimeException(
            '중복 후보 조합이 존재합니다.'
        );
    }

    echo "===== Candidate Storage Test =====\n";
    echo "Source draw          : {$sourceDrawNo}\n";
    echo "Test draw            : {$testDrawNo}\n";
    echo "Run ID               : {$runId}\n";
    echo "Excluded numbers     : "
        . implode(',', $excludedNumbers)
        . "\n";
    echo "Expected candidates  : "
        . number_format($expectedCount)
        . "\n";
    echo "Stored candidates    : "
        . number_format($storedCount)
        . "\n";
    echo "DB candidates        : "
        . number_format($dbCount)
        . "\n";
    echo "Min rank             : "
        . number_format(
            $rankResult['min_rank']
        )
        . "\n";
    echo "Max rank             : "
        . number_format(
            $rankResult['max_rank']
        )
        . "\n";
    echo "Distinct ranks       : "
        . number_format(
            $rankResult[
                'distinct_rank_count'
            ]
        )
        . "\n";
    echo "Rank 1 score         : "
        . $rankOne['score']
        . "\n";
    echo "Rank 1 combination   : "
        . implode(',', $rankOneNumbers)
        . "\n";
    echo "Duplicate groups     : "
        . $duplicateGroupCount
        . "\n";
    echo "Filter/store seconds : "
        . number_format(
            $filterElapsed,
            4
        )
        . "\n";
    echo "Ranking seconds      : "
        . number_format(
            $rankElapsed,
            4
        )
        . "\n";

    if (
        $rankOneNumbers
        !== array(10,17,27,32,33,45)
    ) {
        throw new RuntimeException(
            '1위 조합이 기존 전체 필터 결과와 다릅니다.'
        );
    }

    echo "PASS: all final candidates stored.\n";
    echo "PASS: candidate count is correct.\n";
    echo "PASS: ranks are continuous and unique.\n";
    echo "PASS: rank 1 combination is correct.\n";
    echo "PASS: candidate numbers are unique.\n";
} catch (Throwable $e) {
    echo "FAIL: "
        . $e->getMessage()
        . "\n";

    sql_query(
        "delete from l_filter_candidate
          where draw_no = '{$testDrawNo}'",
        false
    );

    sql_query(
        "delete from l_filter_run
          where draw_no = '{$testDrawNo}'",
        false
    );

    exit(1);
}

sql_query(
    "delete from l_filter_candidate
      where draw_no = '{$testDrawNo}'"
);

sql_query(
    "delete from l_filter_run
      where draw_no = '{$testDrawNo}'"
);

$remaining = sql_fetch(
    "select count(*) as cnt
       from l_filter_candidate
      where draw_no = '{$testDrawNo}'",
    false
);

$remainingCount = isset($remaining['cnt'])
    ? (int) $remaining['cnt']
    : 0;

echo "Remaining test rows : {$remainingCount}\n";

if ($remainingCount !== 0) {
    echo "FAIL: test rows remain.\n";
    exit(1);
}

echo "PASS: test data cleaned up.\n";
