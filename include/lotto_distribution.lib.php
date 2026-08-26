<?php

if (!defined('_GNUBOARD_')) {
    exit;
}

include_once __DIR__ . '/lotto_distribution_cursor.lib.php';

/**
 * 저장된 필터 후보를 회원 한 명에게 순서대로 배분한다.
 *
 * - l_filter_candidate.rank_no 순서를 사용한다.
 * - l_distribution_cursor를 행 잠금하여 중복 배분을 막는다.
 * - SMS는 이 단계에서 처리하지 않는다.
 * - 동일 회차/회원/요일의 regular 배분이 이미 있으면 다시 배분하지 않는다.
 * - 회원 use_num/recent_auto_date/recent_turn 갱신까지 같은 트랜잭션으로 처리한다.
 *
 * @param int $drawNo
 * @param string $mbId
 * @param string $memberType
 * @param int $count
 * @param int $distributionDay
 * @param string $createdBy
 * @return array
 */
function lottoDistributionDistributeMember(
    $drawNo,
    $mbId,
    $memberType,
    $count,
    $distributionDay,
    $createdBy
) {
    $drawNo = (int) $drawNo;
    $mbId = trim((string) $mbId);
    $memberType = trim((string) $memberType);
    $count = (int) $count;
    $distributionDay = (int) $distributionDay;
    $createdBy = trim((string) $createdBy);

    if (
        $drawNo < 1
        || $mbId === ''
        || $memberType === ''
        || $count < 1
        || $distributionDay < 1
        || $distributionDay > 6
    ) {
        return array(
            'success' => false,
            'error' => '배분 입력값이 올바르지 않습니다.',
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
           and distribution_type = 'regular'
           and distribution_day = '{$distributionDay}'",
        false
    );

    if (
        isset($existing['cnt'])
        && (int) $existing['cnt'] > 0
    ) {
        return array(
            'success' => false,
            'error' => '해당 회원의 같은 요일 배분 내역이 이미 있습니다.',
        );
    }

    $transactionStarted = false;

    try {
        if (sql_query('start transaction', false) === false) {
            throw new RuntimeException(
                '배분 트랜잭션을 시작하지 못했습니다.'
            );
        }

        $transactionStarted = true;

        $usageRow = sql_fetch(
            "select
                use_num,
                num_mon,
                num_tue,
                num_wed,
                num_thur,
                num_fri,
                num_sat,
                recent_turn
             from g5_member_etc
             where mb_id = '{$mbIdSql}'
             limit 1
             for update",
            false
        );

        if (!isset($usageRow['use_num'])) {
            throw new RuntimeException(
                '회원 배분 설정을 확인하지 못했습니다.'
            );
        }

        $weekTotal =
            (int) $usageRow['num_mon']
            + (int) $usageRow['num_tue']
            + (int) $usageRow['num_wed']
            + (int) $usageRow['num_thur']
            + (int) $usageRow['num_fri']
            + (int) $usageRow['num_sat'];

        $recentTurn = isset($usageRow['recent_turn'])
            ? (int) $usageRow['recent_turn']
            : 0;

        $useNum = $recentTurn === $drawNo
            ? (int) $usageRow['use_num']
            : 0;

        $leftNum = max(0, $weekTotal - $useNum);

        if ($count > $leftNum) {
            throw new RuntimeException(
                '남은 주간 조합 수보다 배분 요청 수량이 많습니다.'
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

        $candidateSelection = lottoDistributionSelectCandidates(
            $drawNo,
            $count,
            $lastRankNo,
            $cycleNo
        );

        if (
            !isset($candidateSelection['success'])
            || !$candidateSelection['success']
        ) {
            throw new RuntimeException(
                isset($candidateSelection['error'])
                    ? (string) $candidateSelection['error']
                    : '배분 후보를 조회하지 못했습니다.'
            );
        }

        $candidates = $candidateSelection['candidates'];
        $cycleNo = (int) $candidateSelection['cycle_no'];

        $distributionBatch = sprintf(
            '%d-%d-%s-%s',
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
            $candidateCycle = isset($candidate['_candidate_cycle'])
                ? (int) $candidate['_candidate_cycle']
                : $cycleNo;
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
                    candidate_cycle = '{$candidateCycle}',
                    num1 = '{$numbers[0]}',
                    num2 = '{$numbers[1]}',
                    num3 = '{$numbers[2]}',
                    num4 = '{$numbers[3]}',
                    num5 = '{$numbers[4]}',
                    num6 = '{$numbers[5]}',
                    distribution_type = 'regular',
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
                    '회원 조합 저장에 실패했습니다.'
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

        $today = date('Y-m-d');
        $usageUpdate = sql_query(
            "update g5_member_etc
             set
                use_num = case
                    when recent_turn = '{$drawNo}'
                        then use_num + '{$count}'
                    else '{$count}'
                end,
                recent_auto_date = '{$today}',
                recent_auto_datetime = now(),
                recent_turn = '{$drawNo}'
             where mb_id = '{$mbIdSql}'",
            false
        );

        if ($usageUpdate === false) {
            throw new RuntimeException(
                '회원 배분 사용량 저장에 실패했습니다.'
            );
        }

        if (sql_query('commit', false) === false) {
            throw new RuntimeException(
                '배분 트랜잭션을 완료하지 못했습니다.'
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

/**
 * 저장된 필터 후보를 유료회원 한 명에게 주간 단위로 일괄 배분한다.
 *
 * - 일요일에 다음 회차의 월~금 수량을 한 번에 배분한다.
 * - num_sat는 배분 수량에서 제외한다.
 * - distribution_type은 weekly로 저장한다.
 * - distribution_day는 NULL로 저장한다.
 * - 같은 회차/회원의 weekly 배분이 이미 있으면 중복 배분하지 않는다.
 * - l_distribution_cursor를 행 잠금하여 회원 간 후보 중복 배분을 막는다.
 * - 회원 use_num/recent_auto_date/recent_turn 갱신까지 같은 트랜잭션으로 처리한다.
 * - SMS는 이 단계에서 처리하지 않는다.
 *
 * @param int $drawNo
 * @param string $mbId
 * @param string $memberType
 * @param int $count
 * @param string $createdBy
 * @return array
 */
function lottoDistributionDistributeWeeklyMember(
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
        || $memberType === '무료회원'
        || $count < 1
    ) {
        return array(
            'success' => false,
            'error' => '주간 배분 입력값이 올바르지 않습니다.',
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
           and distribution_type = 'weekly'",
        false
    );

    if (
        isset($existing['cnt'])
        && (int) $existing['cnt'] > 0
    ) {
        return array(
            'success' => false,
            'error' => '해당 회원의 주간 배분 내역이 이미 있습니다.',
        );
    }

    $transactionStarted = false;

    try {
        if (sql_query('start transaction', false) === false) {
            throw new RuntimeException(
                '주간 배분 트랜잭션을 시작하지 못했습니다.'
            );
        }

        $transactionStarted = true;

        $usageRow = sql_fetch(
            "select
                use_num,
                num_mon,
                num_tue,
                num_wed,
                num_thur,
                num_fri,
                num_sat,
                recent_turn
             from g5_member_etc
             where mb_id = '{$mbIdSql}'
             limit 1
             for update",
            false
        );

        if (!isset($usageRow['use_num'])) {
            throw new RuntimeException(
                '회원 배분 설정을 확인하지 못했습니다.'
            );
        }

        $weekdayTotal =
            (int) $usageRow['num_mon']
            + (int) $usageRow['num_tue']
            + (int) $usageRow['num_wed']
            + (int) $usageRow['num_thur']
            + (int) $usageRow['num_fri'];

        if ($weekdayTotal < 1) {
            throw new RuntimeException(
                '월요일부터 금요일까지의 배분 수량이 없습니다.'
            );
        }

        if ($count > $weekdayTotal) {
            throw new RuntimeException(
                '요청 수량이 월~금 주간 배분 수량을 초과합니다.'
            );
        }

        $recentTurn = isset($usageRow['recent_turn'])
            ? (int) $usageRow['recent_turn']
            : 0;

        $useNum = $recentTurn === $drawNo
            ? (int) $usageRow['use_num']
            : 0;

        $leftNum = max(
            0,
            $weekdayTotal - $useNum
        );

        if ($count > $leftNum) {
            throw new RuntimeException(
                '남은 월~금 주간 조합 수보다 배분 요청 수량이 많습니다.'
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

        $candidateSelection = lottoDistributionSelectCandidates(
            $drawNo,
            $count,
            $lastRankNo,
            $cycleNo
        );

        if (
            !isset($candidateSelection['success'])
            || !$candidateSelection['success']
        ) {
            throw new RuntimeException(
                isset($candidateSelection['error'])
                    ? (string) $candidateSelection['error']
                    : '배분 후보를 조회하지 못했습니다.'
            );
        }

        $candidates = $candidateSelection['candidates'];
        $cycleNo = (int) $candidateSelection['cycle_no'];

        $distributionBatch = sprintf(
            '%d-weekly-%s-%s',
            $drawNo,
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
            $candidateCycle = isset($candidate['_candidate_cycle'])
                ? (int) $candidate['_candidate_cycle']
                : $cycleNo;
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
                    candidate_cycle = '{$candidateCycle}',
                    num1 = '{$numbers[0]}',
                    num2 = '{$numbers[1]}',
                    num3 = '{$numbers[2]}',
                    num4 = '{$numbers[3]}',
                    num5 = '{$numbers[4]}',
                    num6 = '{$numbers[5]}',
                    distribution_type = 'weekly',
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
                    '회원 주간 조합 저장에 실패했습니다.'
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

        $today = date('Y-m-d');

        $usageUpdate = sql_query(
            "update g5_member_etc
             set
                use_num = case
                    when recent_turn = '{$drawNo}'
                        then use_num + '{$count}'
                    else '{$count}'
                end,
                recent_auto_date = '{$today}',
                recent_auto_datetime = now(),
                recent_turn = '{$drawNo}'
             where mb_id = '{$mbIdSql}'",
            false
        );

        if ($usageUpdate === false) {
            throw new RuntimeException(
                '회원 주간 배분 사용량 저장에 실패했습니다.'
            );
        }

        if (sql_query('commit', false) === false) {
            throw new RuntimeException(
                '주간 배분 트랜잭션을 완료하지 못했습니다.'
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

/**
 * 다음 주 월~금 기준으로 유료회원 전체를 주간 일괄 배분한다.
 *
 * - 무료회원 제외
 * - 토요일 제외
 * - 회원 서비스 기간 안에 포함되는 요일 수량만 계산
 * - 같은 회차 weekly 배분은 다시 배분하지 않음
 *
 * @param int $drawNo
 * @param DateTimeImmutable $now
 * @param bool $dryRun
 * @param string $createdBy
 * @return array
 */
function lottoDistributionRunWeeklyPaid(
    $drawNo,
    DateTimeImmutable $now,
    $dryRun = false,
    $createdBy = 'cron'
) {
    $drawNo = (int) $drawNo;
    $dryRun = (bool) $dryRun;
    $createdBy = trim((string) $createdBy);

    if ($drawNo < 1) {
        return array(
            'success' => false,
            'error' => '주간 배분 회차가 올바르지 않습니다.',
        );
    }

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

    if (
        !isset($run['lfr_id'])
        || (int) $run['lfr_id'] < 1
        || !isset($run['status'])
        || $run['status'] !== 'filtered'
        || !isset($run['candidate_count'])
        || (int) $run['candidate_count'] < 1
    ) {
        return array(
            'success' => false,
            'error' => '주간 배분 가능한 필터 결과가 없습니다.',
        );
    }

    $weekStart = $now
        ->modify('next monday')
        ->setTime(0, 0, 0);

    $weekDates = array(
        'num_mon' => $weekStart,
        'num_tue' => $weekStart->modify('+1 day'),
        'num_wed' => $weekStart->modify('+2 days'),
        'num_thur' => $weekStart->modify('+3 days'),
        'num_fri' => $weekStart->modify('+4 days'),
    );

    $lockName = 'lotto_weekly_distribution_' . $drawNo;

    $lockRow = sql_fetch(
        "select get_lock(
            '" . sql_real_escape_string($lockName) . "',
            0
        ) as acquired",
        false
    );

    $lockAcquired = isset($lockRow['acquired'])
        && (int) $lockRow['acquired'] === 1;

    if (!$lockAcquired) {
        return array(
            'success' => false,
            'error' => '같은 회차의 주간 배분 작업이 이미 실행 중입니다.',
        );
    }

    try {
        $memberResult = sql_query(
            "select
                a.mb_id,
                a.mb_name,
                a.mb_type,
                a.mb_datetime,
                b.start_date,
                b.end_date,
                b.num_mon,
                b.num_tue,
                b.num_wed,
                b.num_thur,
                b.num_fri,
                b.num_sat
             from g5_member a
             inner join g5_member_etc b
                on b.mb_id = a.mb_id
             where a.mb_leave_date = ''
               and a.mb_type <> ''
               and a.mb_type <> '무료회원'
               and (
                    coalesce(b.num_mon, 0)
                    + coalesce(b.num_tue, 0)
                    + coalesce(b.num_wed, 0)
                    + coalesce(b.num_thur, 0)
                    + coalesce(b.num_fri, 0)
               ) > 0
             order by a.mb_datetime asc, a.mb_id asc",
            false
        );

        if ($memberResult === false) {
            throw new RuntimeException(
                '유료회원 주간 배분 대상 조회에 실패했습니다.'
            );
        }

        $eligibleMembers = 0;
        $processedMembers = 0;
        $skippedMembers = 0;
        $combinationCount = 0;
        $members = array();

        while ($member = sql_fetch_array($memberResult)) {
            $startDate = trim((string) $member['start_date']);
            $endDate = trim((string) $member['end_date']);

            if ($startDate === '' || $endDate === '') {
                continue;
            }

            $requestCount = 0;

            foreach ($weekDates as $column => $date) {
                $targetDate = $date->format('Y-m-d');

                if (
                    $startDate <= $targetDate
                    && $endDate >= $targetDate
                ) {
                    $requestCount += isset($member[$column])
                        ? (int) $member[$column]
                        : 0;
                }
            }

            if ($requestCount < 1) {
                continue;
            }

            $eligibleMembers++;

            $mbIdSql = sql_real_escape_string(
                (string) $member['mb_id']
            );

            $existing = sql_fetch(
                "select count(*) as cnt
                 from l_member_combination
                 where draw_no = '{$drawNo}'
                   and mb_id = '{$mbIdSql}'
                   and distribution_type = 'weekly'",
                false
            );

            if (
                isset($existing['cnt'])
                && (int) $existing['cnt'] > 0
            ) {
                $skippedMembers++;

                $members[] = array(
                    'mb_id' => $member['mb_id'],
                    'status' => 'skipped',
                    'count' => 0,
                );

                continue;
            }

            if ($dryRun) {
                $processedMembers++;
                $combinationCount += $requestCount;

                $members[] = array(
                    'mb_id' => $member['mb_id'],
                    'status' => 'dry_run',
                    'count' => $requestCount,
                );

                continue;
            }

            $result = lottoDistributionDistributeWeeklyMember(
                $drawNo,
                $member['mb_id'],
                $member['mb_type'],
                $requestCount,
                $createdBy
            );

            if (
                !isset($result['success'])
                || !$result['success']
            ) {
                throw new RuntimeException(
                    '유료회원 '
                    . $member['mb_id']
                    . ' 주간 배분 실패: '
                    . (
                        isset($result['error'])
                            ? $result['error']
                            : '알 수 없는 오류'
                    )
                );
            }

            $processedMembers++;
            $combinationCount += (int) $result['count'];

            $members[] = array(
                'mb_id' => $member['mb_id'],
                'status' => 'distributed',
                'count' => (int) $result['count'],
                'start_rank_no' => (int) $result['start_rank_no'],
                'end_rank_no' => (int) $result['end_rank_no'],
            );
        }

        return array(
            'success' => true,
            'draw_no' => $drawNo,
            'week_start' => $weekStart->format('Y-m-d'),
            'week_end' => $weekStart
                ->modify('+4 days')
                ->format('Y-m-d'),
            'eligible_members' => $eligibleMembers,
            'processed_members' => $processedMembers,
            'skipped_members' => $skippedMembers,
            'combination_count' => $combinationCount,
            'dry_run' => $dryRun,
            'members' => $members,
        );
    } catch (Throwable $e) {
        return array(
            'success' => false,
            'error' => $e->getMessage(),
        );
    } finally {
        if ($lockAcquired) {
            sql_fetch(
                "select release_lock(
                    '" . sql_real_escape_string($lockName) . "'
                ) as released",
                false
            );
        }
    }
}
