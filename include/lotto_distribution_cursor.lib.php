<?php

if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * 후보 한 건의 로또 번호를 정수 배열로 만든다.
 *
 * @param array $candidate
 * @return array
 */
function lottoDistributionCandidateNumbers(array $candidate)
{
    return array(
        (int) $candidate['num1'],
        (int) $candidate['num2'],
        (int) $candidate['num3'],
        (int) $candidate['num4'],
        (int) $candidate['num5'],
        (int) $candidate['num6'],
    );
}

/**
 * 이미 선택된 후보들과 번호가 너무 많이 겹치는지 검사한다.
 *
 * 기본값 3은 같은 묶음 안에서 최대 3개 번호까지 겹치는 것을 허용하고,
 * 4개 이상 겹치는 조합은 제외한다.
 *
 * @param array $candidate
 * @param array $selectedCandidates
 * @param int $maxOverlap
 * @return bool
 */
function lottoDistributionHasExcessiveOverlap(
    array $candidate,
    array $selectedCandidates,
    $maxOverlap = 3
) {
    $maxOverlap = max(0, min(5, (int) $maxOverlap));
    $candidateNumbers = lottoDistributionCandidateNumbers($candidate);

    foreach ($selectedCandidates as $selectedCandidate) {
        $selectedNumbers = lottoDistributionCandidateNumbers(
            $selectedCandidate
        );

        if (
            count(array_intersect(
                $candidateNumbers,
                $selectedNumbers
            )) > $maxOverlap
        ) {
            return true;
        }
    }

    return false;
}

/**
 * 현재 배분 커서부터 필요한 수량만큼 필터 후보를 가져온다.
 *
 * - rank_no 순서를 유지한다.
 * - 같은 배분 묶음 안에서 서로 4개 이상 번호가 겹치는 후보는 건너뛴다.
 * - 후보 끝에 도달하면 현재 커서 이전 구간을 한 번만 이어서 확인한다.
 * - 같은 조합을 한 묶음 안에서 다시 사용하는 순환은 허용하지 않는다.
 * - 반환되는 각 후보에는 _candidate_cycle 값이 추가된다.
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
    $lastRankNo = max(0, (int) $lastRankNo);
    $cycleNo = max(1, (int) $cycleNo);

    if ($drawNo < 1 || $count < 1) {
        return array(
            'success' => false,
            'error' => '배분 후보 조회 입력값이 올바르지 않습니다.',
        );
    }

    $candidates = array();
    $originalLastRankNo = $lastRankNo;
    $currentLastRankNo = $lastRankNo;
    $currentCycleNo = $cycleNo;
    $hasWrapped = false;

    while (count($candidates) < $count) {
        $remaining = $count - count($candidates);
        $fetchLimit = max(100, $remaining * 20);
        $fetchLimit = min(1000, $fetchLimit);

        $upperRankCondition = '';

        if ($hasWrapped && $originalLastRankNo > 0) {
            $upperRankCondition =
                " and rank_no <= '{$originalLastRankNo}'";
        }

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
               {$upperRankCondition}
             order by rank_no asc
             limit {$fetchLimit}",
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
            $fetchedCount++;
            $currentLastRankNo = (int) $row['rank_no'];

            if (
                lottoDistributionHasExcessiveOverlap(
                    $row,
                    $candidates,
                    3
                )
            ) {
                continue;
            }

            $row['_candidate_cycle'] = $currentCycleNo;
            $candidates[] = $row;

            if (count($candidates) >= $count) {
                break;
            }
        }

        if (count($candidates) >= $count) {
            break;
        }

        /*
         * 아직 조회할 rank가 남아 있으면 다음 묶음을 이어서 조회한다.
         */
        if ($fetchedCount >= $fetchLimit) {
            continue;
        }

        /*
         * 현재 커서가 0이었다면 전체 후보를 이미 한 번 확인했다.
         * 또는 한 번 순환한 뒤 원래 커서까지 확인했다면
         * 같은 후보를 다시 사용할 수 없으므로 여기서 실패한다.
         */
        if ($originalLastRankNo === 0 || $hasWrapped) {
            return array(
                'success' => false,
                'error' => '서로 충분히 다른 배분 후보가 부족합니다.',
            );
        }

        /*
         * 현재 커서 이후 후보를 모두 확인했다.
         * rank 처음부터 원래 커서까지 한 번만 이어서 확인한다.
         */
        $currentLastRankNo = 0;
        $currentCycleNo++;
        $hasWrapped = true;
    }

    return array(
        'success' => true,
        'candidates' => $candidates,
        'last_rank_no' => $currentLastRankNo,
        'cycle_no' => $currentCycleNo,
    );
}
