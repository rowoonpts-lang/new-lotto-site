<?php

if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * 저장된 필터 후보를 무료회원 한 명에게 순서대로 배분한다.
 *
 * - l_filter_candidate.rank_no 순서를 사용한다.
 * - l_distribution_cursor를 행 잠금하여 중복 배분을 막는다.
 * - distribution_type은 free로 저장한다.
 * - SMS는 이 단계에서 처리하지 않는다.
 * - recent_free_date/recent_free_datetime/recent_turn 갱신까지 같은 트랜잭션으로 처리한다.
 *
 * @param int $drawNo
 * @param string $mbId
 * @param int $count
 * @param int $distributionDay
 * @param string $createdBy
 * @return array
 */
function lottoFreeDistributionDistributeMember(
    $drawNo,
    $mbId,
    $count,
    $distributionDay,
    $createdBy
) {
    $drawNo = (int) $drawNo;
    $mbId = trim((string) $mbId);
    $count = (int) $count;
    $distributionDay = (int) $distributionDay;
    $createdBy = trim((string) $createdBy);

    if (
        $drawNo < 1
        || $mbId === ''
        || $count < 1
        || $distributionDay < 1
        || $distributionDay > 6
    ) {
        return array(
            'success' => false,
            'error' => '무료회원 배분 입력값이 올바르지 않습니다.',
        );
    }

    $mbIdSql = sql_real_escape_string($mbId);
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

    $runId = isset($run['lfr_id'])
        ? (int) $run['lfr_id']
        : 0;

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
            'error' => '배분 가능한 필터 결과가 없습니다.',
        );
    }

    $existing = sql_fetch(
        "select count(*) as cnt
         from l_member_combination
         where draw_no = '{$drawNo}'
           and mb_id = '{$mbIdSql}'
           and distribution_type = 'free'",
        false
    );

    if (
        isset($existing['cnt'])
        && (int) $existing['cnt'] > 0
    ) {
        return array(
            'success' => false,
            'error' => '해당 무료회원의 배분 내역이 이미 있습니다.',
        );
    }

    $transactionStarted = false;

    try {
        if (sql_query('start transaction', false) === false) {
            throw new RuntimeException(
                '무료회원 배분 트랜잭션을 시작하지 못했습니다.'
            );
        }

        $transactionStarted = true;

        $memberRow = sql_fetch(
            "select
                a.mb_type,
                a.mb_leave_date,
                b.recent_free_date,
                b.free_num_qty,
                b.free_num_date
             from g5_member a
             inner join g5_member_etc b
                on b.mb_id = a.mb_id
             where a.mb_id = '{$mbIdSql}'
             limit 1
             for update",
            false
        );

        if (
            !isset($memberRow['mb_type'])
            || trim((string) $memberRow['mb_type']) !== '무료회원'
        ) {
            throw new RuntimeException(
                '무료회원 정보를 확인하지 못했습니다.'
            );
        }

        if (trim((string) $memberRow['mb_leave_date']) !== '') {
            throw new RuntimeException(
                '탈퇴 회원은 배분할 수 없습니다.'
            );
        }

        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $recentFreeDate = trim(
            (string) $memberRow['recent_free_date']
        );

        if (
            $recentFreeDate === $today
            || $recentFreeDate === $yesterday
        ) {
            throw new RuntimeException(
                '오늘 또는 어제 이미 무료번호를 받은 회원입니다.'
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
                '배분 커서를 준비하지 못했습니다.'
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
                '배분 커서를 확인하지 못했습니다.'
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
                '무료회원 배분 후보를 조회하지 못했습니다.'
            );
        }

        $candidates = array();

        while ($row = sql_fetch_array($candidateResult)) {
            $candidates[] = $row;
        }

        if (count($candidates) !== $count) {
            throw new RuntimeException(
                '남은 필터 후보가 요청한 무료 배분 수량보다 적습니다.'
            );
        }

        $distributionBatch = sprintf(
            '%d-free-%d-%s-%s',
            $drawNo,
            $distributionDay,
            date('Ymd'),
            $mbId
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
                    member_type = '무료회원',
                    lfc_id = '{$lfcId}',
                    candidate_rank = '{$rankNo}',
                    candidate_cycle = '{$cycleNo}',
                    num1 = '{$numbers[0]}',
                    num2 = '{$numbers[1]}',
                    num3 = '{$numbers[2]}',
                    num4 = '{$numbers[3]}',
                    num5 = '{$numbers[4]}',
                    num6 = '{$numbers[5]}',
                    distribution_type = 'free',
                    distribution_day = '{$distributionDay}',
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
                    '무료회원 조합 저장에 실패했습니다.'
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
                '배분 커서 저장에 실패했습니다.'
            );
        }

        $usageUpdate = sql_query(
            "update g5_member_etc
             set
                recent_free_date = '{$today}',
                recent_free_datetime = now(),
                recent_turn = '{$drawNo}'
             where mb_id = '{$mbIdSql}'",
            false
        );

        if ($usageUpdate === false) {
            throw new RuntimeException(
                '무료회원 배분 이력 저장에 실패했습니다.'
            );
        }

        if (sql_query('commit', false) === false) {
            throw new RuntimeException(
                '무료회원 배분 트랜잭션을 완료하지 못했습니다.'
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
