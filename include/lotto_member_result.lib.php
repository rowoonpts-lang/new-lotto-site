<?php

if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * 회원 조합 한 건의 당첨 결과를 계산한다.
 *
 * @param array $numbers 회원 번호 6개
 * @param array $winningNumbers 당첨 번호 6개
 * @param int $bonusNumber 보너스 번호
 * @return array
 */
function lottoMemberResultClassify(
    $numbers,
    $winningNumbers,
    $bonusNumber
) {
    $numbers = array_values(array_map('intval', (array) $numbers));
    $winningNumbers = array_values(
        array_map('intval', (array) $winningNumbers)
    );
    $bonusNumber = (int) $bonusNumber;

    if (
        count($numbers) !== 6
        || count($winningNumbers) !== 6
        || count(array_unique($numbers)) !== 6
        || count(array_unique($winningNumbers)) !== 6
    ) {
        throw new InvalidArgumentException(
            '로또 번호는 중복 없는 6개 번호여야 합니다.'
        );
    }

    foreach (array_merge($numbers, $winningNumbers) as $number) {
        if ($number < 1 || $number > 45) {
            throw new InvalidArgumentException(
                '로또 번호는 1부터 45 사이여야 합니다.'
            );
        }
    }

    if (
        $bonusNumber < 1
        || $bonusNumber > 45
        || in_array($bonusNumber, $winningNumbers, true)
    ) {
        throw new InvalidArgumentException(
            '보너스 번호가 올바르지 않습니다.'
        );
    }

    $matchCount = count(
        array_intersect($numbers, $winningNumbers)
    );

    $bonusMatch = in_array(
        $bonusNumber,
        $numbers,
        true
    ) ? 1 : 0;

    $rank = null;

    if ($matchCount === 6) {
        $rank = 1;
    } elseif ($matchCount === 5 && $bonusMatch === 1) {
        $rank = 2;
    } elseif ($matchCount === 5) {
        $rank = 3;
    } elseif ($matchCount === 4) {
        $rank = 4;
    } elseif ($matchCount === 3) {
        $rank = 5;
    }

    return array(
        'match_count' => $matchCount,
        'bonus_match' => $bonusMatch,
        'result_rank' => $rank,
    );
}

/**
 * 저장된 공식 당첨번호를 기준으로 한 회차의 회원 결과를 계산한다.
 *
 * SMS 처리는 이 함수에서 하지 않는다.
 *
 * @param int $drawNo
 * @return array
 */
function lottoMemberResultProcessDraw($drawNo)
{
    $drawNo = (int) $drawNo;

    if ($drawNo < 1) {
        return array(
            'success' => false,
            'error' => '회차가 올바르지 않습니다.',
        );
    }

    $resultRow = sql_fetch(
        "select
            draw_no,
            num_1,
            num_2,
            num_3,
            num_4,
            num_5,
            num_6,
            bonus_num,
            fetched_at
         from g5_lotto_result
         where draw_no = '{$drawNo}'
         limit 1",
        false
    );

    if (
        !isset($resultRow['draw_no'])
        || (int) $resultRow['draw_no'] !== $drawNo
    ) {
        return array(
            'success' => false,
            'status' => 'result_missing',
            'error' => $drawNo . '회 공식 당첨결과가 없습니다.',
        );
    }

    $combinationCountRow = sql_fetch(
        "select count(*) as cnt
         from l_member_combination
         where draw_no = '{$drawNo}'",
        false
    );

    $combinationCount = isset($combinationCountRow['cnt'])
        ? (int) $combinationCountRow['cnt']
        : 0;

    if ($combinationCount < 1) {
        return array(
            'success' => false,
            'status' => 'combination_missing',
            'error' => $drawNo . '회 회원 배분 조합이 없습니다.',
        );
    }

    $winningNumbers = array(
        (int) $resultRow['num_1'],
        (int) $resultRow['num_2'],
        (int) $resultRow['num_3'],
        (int) $resultRow['num_4'],
        (int) $resultRow['num_5'],
        (int) $resultRow['num_6'],
    );
    $bonusNumber = (int) $resultRow['bonus_num'];

    try {
        lottoMemberResultClassify(
            $winningNumbers,
            $winningNumbers,
            $bonusNumber
        );
    } catch (Throwable $e) {
        return array(
            'success' => false,
            'status' => 'invalid_result',
            'error' => '저장된 공식 당첨번호가 올바르지 않습니다: '
                . $e->getMessage(),
        );
    }

    $transactionStarted = false;

    try {
        if (sql_query('start transaction', false) === false) {
            throw new RuntimeException(
                '결과 계산 트랜잭션을 시작하지 못했습니다.'
            );
        }

        $transactionStarted = true;

        $savedAt = trim((string) $resultRow['fetched_at']);
        $savedAtSql = $savedAt !== ''
            ? "'" . sql_real_escape_string($savedAt) . "'"
            : 'now()';

        $jobUpsert = sql_query(
            "insert into l_result_job
             set
                draw_no = '{$drawNo}',
                status = 'processing',
                result_saved_at = {$savedAtSql},
                last_error = null
             on duplicate key update
                status = 'processing',
                result_saved_at = values(result_saved_at),
                result_checked_at = null,
                last_error = null",
            false
        );

        if ($jobUpsert === false) {
            throw new RuntimeException(
                '결과 작업 상태를 준비하지 못했습니다.'
            );
        }

        $combinationResult = sql_query(
            "select
                lmc_id,
                num1,
                num2,
                num3,
                num4,
                num5,
                num6
             from l_member_combination
             where draw_no = '{$drawNo}'
             order by lmc_id asc",
            false
        );

        if ($combinationResult === false) {
            throw new RuntimeException(
                '회원 배분 조합을 조회하지 못했습니다.'
            );
        }

        $checkedCount = 0;
        $winningCount = 0;
        $rankCounts = array(
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0,
        );

        while ($row = sql_fetch_array($combinationResult)) {
            $classified = lottoMemberResultClassify(
                array(
                    (int) $row['num1'],
                    (int) $row['num2'],
                    (int) $row['num3'],
                    (int) $row['num4'],
                    (int) $row['num5'],
                    (int) $row['num6'],
                ),
                $winningNumbers,
                $bonusNumber
            );

            $lmcId = (int) $row['lmc_id'];
            $matchCount = (int) $classified['match_count'];
            $bonusMatch = (int) $classified['bonus_match'];
            $rankSql = $classified['result_rank'] === null
                ? 'null'
                : "'" . (int) $classified['result_rank'] . "'";

            $updateResult = sql_query(
                "update l_member_combination
                 set
                    match_count = '{$matchCount}',
                    bonus_match = '{$bonusMatch}',
                    result_rank = {$rankSql},
                    result_checked_at = now()
                 where lmc_id = '{$lmcId}'",
                false
            );

            if ($updateResult === false) {
                throw new RuntimeException(
                    '회원 조합 결과 저장에 실패했습니다.'
                );
            }

            $checkedCount++;

            if ($classified['result_rank'] !== null) {
                $rank = (int) $classified['result_rank'];
                $winningCount++;
                $rankCounts[$rank]++;
            }
        }

        $memberSummaryResult = sql_query(
            "select
                mb_id,
                member_type,
                count(*) as combination_count,
                sum(case when result_rank = 1 then 1 else 0 end) as rank1_count,
                sum(case when result_rank = 2 then 1 else 0 end) as rank2_count,
                sum(case when result_rank = 3 then 1 else 0 end) as rank3_count,
                sum(case when result_rank = 4 then 1 else 0 end) as rank4_count,
                sum(case when result_rank = 5 then 1 else 0 end) as rank5_count,
                min(case
                    when result_rank between 1 and 5 then result_rank
                    else null
                end) as best_rank
             from l_member_combination
             where draw_no = '{$drawNo}'
             group by mb_id, member_type",
            false
        );

        if ($memberSummaryResult === false) {
            throw new RuntimeException(
                '회원별 결과 집계를 조회하지 못했습니다.'
            );
        }

        $memberCount = 0;

        while ($summary = sql_fetch_array($memberSummaryResult)) {
            $mbIdSql = sql_real_escape_string(
                (string) $summary['mb_id']
            );
            $memberTypeSql = sql_real_escape_string(
                (string) $summary['member_type']
            );
            $bestRankSql = $summary['best_rank'] === null
                || $summary['best_rank'] === ''
                    ? 'null'
                    : "'" . (int) $summary['best_rank'] . "'";

            $summaryUpsert = sql_query(
                "insert into l_member_draw
                 set
                    draw_no = '{$drawNo}',
                    mb_id = '{$mbIdSql}',
                    member_type = '{$memberTypeSql}',
                    combination_count = '" . (int) $summary['combination_count'] . "',
                    rank1_count = '" . (int) $summary['rank1_count'] . "',
                    rank2_count = '" . (int) $summary['rank2_count'] . "',
                    rank3_count = '" . (int) $summary['rank3_count'] . "',
                    rank4_count = '" . (int) $summary['rank4_count'] . "',
                    rank5_count = '" . (int) $summary['rank5_count'] . "',
                    best_rank = {$bestRankSql},
                    result_checked_at = now(),
                    winner_sms_required = 0,
                    winner_sms_status = 'not_required'
                 on duplicate key update
                    member_type = values(member_type),
                    combination_count = values(combination_count),
                    rank1_count = values(rank1_count),
                    rank2_count = values(rank2_count),
                    rank3_count = values(rank3_count),
                    rank4_count = values(rank4_count),
                    rank5_count = values(rank5_count),
                    best_rank = values(best_rank),
                    result_checked_at = values(result_checked_at),
                    winner_sms_required = 0,
                    winner_sms_status = 'not_required'",
                false
            );

            if ($summaryUpsert === false) {
                throw new RuntimeException(
                    '회원별 결과 집계 저장에 실패했습니다.'
                );
            }

            $memberCount++;
        }

        $jobComplete = sql_query(
            "update l_result_job
             set
                status = 'completed',
                result_checked_at = now(),
                winner_sms_completed_at = null,
                last_error = null
             where draw_no = '{$drawNo}'",
            false
        );

        if ($jobComplete === false) {
            throw new RuntimeException(
                '결과 작업 완료 상태를 저장하지 못했습니다.'
            );
        }

        $filterRunComplete = sql_query(
            "update l_filter_run
             set completed_at = now()
             where draw_no = '{$drawNo}'",
            false
        );

        if ($filterRunComplete === false) {
            throw new RuntimeException(
                '필터 실행 완료 시간을 저장하지 못했습니다.'
            );
        }

        if (sql_query('commit', false) === false) {
            throw new RuntimeException(
                '결과 계산 트랜잭션을 완료하지 못했습니다.'
            );
        }

        $transactionStarted = false;

        return array(
            'success' => true,
            'status' => 'completed',
            'draw_no' => $drawNo,
            'combination_count' => $combinationCount,
            'checked_count' => $checkedCount,
            'winning_count' => $winningCount,
            'member_count' => $memberCount,
            'rank_counts' => $rankCounts,
        );
    } catch (Throwable $e) {
        if ($transactionStarted) {
            sql_query('rollback', false);
        }

        sql_query(
            "insert into l_result_job
             set
                draw_no = '{$drawNo}',
                status = 'failed',
                last_error = '" . sql_real_escape_string($e->getMessage()) . "'
             on duplicate key update
                status = 'failed',
                last_error = values(last_error)",
            false
        );

        return array(
            'success' => false,
            'status' => 'failed',
            'error' => $e->getMessage(),
        );
    }
}
