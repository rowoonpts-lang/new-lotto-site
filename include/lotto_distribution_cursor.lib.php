<?php

if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * 현재 배분 커서부터 필요한 수량만큼 필터 후보를 가져온다.
 *
 * 후보 끝에 도달하면 rank 처음부터 다시 시작하고
 * cycle_no를 1 증가시킨다.
 *
 * 반환되는 각 후보에는 _candidate_cycle 값이 추가된다.
 *
 * @param int $drawNo
 * @param int $count
 * @param int $lastRankNo
 * @param int $cycleNo
 * @return array
 */
function lottoDistributionSelectCandidates(
    $drawNo,
    $count,
    $lastRankNo,
    $cycleNo
) {
    $drawNo = (int) $drawNo;
    $count = (int) $count;
    $lastRankNo = (int) $lastRankNo;
    $cycleNo = max(1, (int) $cycleNo);

    if ($drawNo < 1 || $count < 1) {
        return array(
            'success' => false,
            'error' => '배분 후보 조회 입력값이 올바르지 않습니다.',
        );
    }

    $candidates = array();
    $currentLastRankNo = $lastRankNo;
    $currentCycleNo = $cycleNo;

    while (count($candidates) < $count) {
        $remaining = $count - count($candidates);

        $candidateResult = sql_query(
            "select
                lfc_id,
                rank_no,
                num1,
                num2,
                num3,
                num4,
                num5,
                num6,
                score
             from l_filter_candidate
             where draw_no = '{$drawNo}'
               and rank_no > '{$currentLastRankNo}'
             order by rank_no asc
             limit {$remaining}",
            false
        );

        if ($candidateResult === false) {
            return array(
                'success' => false,
                'error' => '배분 후보를 조회하지 못했습니다.',
            );
        }

        $fetchedCount = 0;

        while ($row = sql_fetch_array($candidateResult)) {
            $row['_candidate_cycle'] = $currentCycleNo;

            $candidates[] = $row;
            $currentLastRankNo = (int) $row['rank_no'];
            $fetchedCount++;
        }

        if (count($candidates) >= $count) {
            break;
        }

        /*
         * rank 0부터 조회했는데도 한 건도 없다면
         * 해당 회차에 실제 필터 후보가 없는 상태다.
         */
        if ($currentLastRankNo === 0 && $fetchedCount === 0) {
            return array(
                'success' => false,
                'error' => '배분 가능한 필터 후보가 없습니다.',
            );
        }

        /*
         * 현재 cycle의 마지막 후보까지 사용했다.
         * 다음 후보부터 rank 처음으로 돌아간다.
         */
        $currentLastRankNo = 0;
        $currentCycleNo++;
    }

    return array(
        'success' => true,
        'candidates' => $candidates,
        'last_rank_no' => $currentLastRankNo,
        'cycle_no' => $currentCycleNo,
    );
}
