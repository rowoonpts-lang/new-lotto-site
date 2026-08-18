<?php

if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * 관리자가 회원에게 추가 조합을 배분한다.
 *
 * - 필터 후보와 공용 배분 cursor를 사용한다.
 * - 주간 설정 수량 제한을 적용하지 않는다.
 * - 같은 날/요일 중복 배분을 허용한다.
 * - 추가 배분은 정규 주간 배분량(use_num)을 소비하지 않는다.
 * - SMS는 이 함수에서 처리하지 않는다.
 */
function lottoManualDistributionDistributeMember(
    $drawNo,
    $mbId,
    $memberType,
    $count,
    $createdBy
) {
    $drawNo = (int) $drawNo;
    $mbId = trim((string) $mbId);
    $memberType = trim((string) $memberType);
    $count = (int) $count;
    $createdBy = trim((string) $createdBy);

    if (
        $drawNo < 1
        || $mbId === ''
        || $memberType === ''
        || $count < 1
    ) {
        return array(
            'success' => false,
            'error' => '추가발송 입력값이 올바르지 않습니다.',
        );
    }

    $mbIdSql = sql_real_escape_string($mbId);
    $memberTypeSql = sql_real_escape_string($memberType);
    $createdBySql = sql_real_escape_string($createdBy);

    $run = sql_fetch(
        "select
            lfr_id,
            status,
            candidate_count
         from l_filter_run
         where draw_no = '{$drawNo}'
         limit 1",
        false
    );

    $runId = isset($run['lfr_id']) ? (int) $run['lfr_id'] : 0;
    $candidateCount = isset($run['candidate_count'])
        ? (int) $run['candidate_count']
        : 0;

    if (
        $runId < 1
        || !isset($run['status'])
        || $run['status'] !== 'filtered'
        || $candidateCount < 1
    ) {
        return array(
            'success' => false,
            'error' => '추가 배분 가능한 필터 결과가 없습니다.',
        );
    }

    $transactionStarted = false;

    try {
        if (sql_query('start transaction', false) === false) {
            throw new RuntimeException(
                '추가배분 트랜잭션을 시작하지 못했습니다.'
            );
        }

        $transactionStarted = true;

        $memberEtcRow = sql_fetch(
            "select mb_id
             from g5_member_etc
             where mb_id = '{$mbIdSql}'
             limit 1
             for update",
            false
        );

        if (
            !isset($memberEtcRow['mb_id'])
            || $memberEtcRow['mb_id'] === ''
        ) {
            throw new RuntimeException(
                '회원 배분 설정을 확인하지 못했습니다.'
            );
        }

        $cursorInsert = sql_query(
            "insert into l_distribution_cursor
             set
                lfr_id = '{$runId}',
                draw_no = '{$drawNo}',
                last_rank_no = 0,
                cycle_no = 1
             on duplicate key update
                ldc_id = last_insert_id(ldc_id),
                lfr_id = values(lfr_id)",
            false
        );

        if ($cursorInsert === false) {
            throw new RuntimeException(
                '추가배분 커서를 준비하지 못했습니다.'
            );
        }

        $cursor = sql_fetch(
            "select
                ldc_id,
                last_rank_no,
                cycle_no
             from l_distribution_cursor
             where draw_no = '{$drawNo}'
             limit 1
             for update",
            false
        );

        $cursorId = isset($cursor['ldc_id'])
            ? (int) $cursor['ldc_id']
            : 0;
        $lastRankNo = isset($cursor['last_rank_no'])
            ? (int) $cursor['last_rank_no']
            : 0;
        $cycleNo = isset($cursor['cycle_no'])
            ? (int) $cursor['cycle_no']
            : 1;

        if ($cursorId < 1 || $cycleNo < 1) {
            throw new RuntimeException(
                '추가배분 커서를 확인하지 못했습니다.'
            );
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
               and rank_no > '{$lastRankNo}'
             order by rank_no asc
             limit {$count}",
            false
        );

        if ($candidateResult === false) {
            throw new RuntimeException(
                '추가배분 후보를 조회하지 못했습니다.'
            );
        }

        $candidates = array();

        while ($candidate = sql_fetch_array($candidateResult)) {
            $candidates[] = $candidate;
        }

        if (count($candidates) !== $count) {
            throw new RuntimeException(
                '남은 필터 후보가 추가발송 수량보다 적습니다.'
            );
        }

        $distributionBatch = sprintf(
            '%d-manual-%s-%s-%s',
            $drawNo,
            date('YmdHis'),
            $mbId,
            substr(uniqid('', true), -8)
        );

        $distributionBatchSql =
            sql_real_escape_string($distributionBatch);

        $distributed = array();
        $newLastRankNo = $lastRankNo;

        foreach ($candidates as $index => $candidate) {
            $lfcId = (int) $candidate['lfc_id'];
            $rankNo = (int) $candidate['rank_no'];
            $distributionSeq = $index + 1;
            $score = sql_real_escape_string(
                (string) $candidate['score']
            );

            $numbers = array(
                (int) $candidate['num1'],
                (int) $candidate['num2'],
                (int) $candidate['num3'],
                (int) $candidate['num4'],
                (int) $candidate['num5'],
                (int) $candidate['num6'],
            );

            $insertResult = sql_query(
                "insert into l_member_combination
                 set
                    draw_no = '{$drawNo}',
                    mb_id = '{$mbIdSql}',
                    member_type = '{$memberTypeSql}',
                    lfc_id = '{$lfcId}',
                    candidate_rank = '{$rankNo}',
                    candidate_cycle = '{$cycleNo}',
                    num1 = '{$numbers[0]}',
                    num2 = '{$numbers[1]}',
                    num3 = '{$numbers[2]}',
                    num4 = '{$numbers[3]}',
                    num5 = '{$numbers[4]}',
                    num6 = '{$numbers[5]}',
                    distribution_type = 'manual',
                    distribution_day = NULL,
                    distribution_batch = '{$distributionBatchSql}',
                    distribution_seq = '{$distributionSeq}',
                    score = '{$score}',
                    sms_required = 0,
                    sms_status = 'not_required',
                    created_by = '{$createdBySql}'",
                false
            );

            if ($insertResult === false) {
                throw new RuntimeException(
                    '추가 조합 저장에 실패했습니다.'
                );
            }

            $distributed[] = array(
                'lfc_id' => $lfcId,
                'rank_no' => $rankNo,
                'numbers' => $numbers,
            );

            $newLastRankNo = $rankNo;
        }

        $cursorUpdate = sql_query(
            "update l_distribution_cursor
             set
                last_rank_no = '{$newLastRankNo}',
                cycle_no = '{$cycleNo}'
             where ldc_id = '{$cursorId}'",
            false
        );

        if ($cursorUpdate === false) {
            throw new RuntimeException(
                '추가배분 커서 저장에 실패했습니다.'
            );
        }

        if (sql_query('commit', false) === false) {
            throw new RuntimeException(
                '추가배분 트랜잭션을 완료하지 못했습니다.'
            );
        }

        $transactionStarted = false;

        return array(
            'success' => true,
            'draw_no' => $drawNo,
            'mb_id' => $mbId,
            'count' => count($distributed),
            'start_rank_no' => $distributed[0]['rank_no'],
            'end_rank_no' => $newLastRankNo,
            'cycle_no' => $cycleNo,
            'distribution_batch' => $distributionBatch,
            'distributed' => $distributed,
        );
    } catch (Throwable $e) {
        if ($transactionStarted) {
            sql_query('rollback', false);
        }

        return array(
            'success' => false,
            'error' => $e->getMessage(),
        );
    }
}
