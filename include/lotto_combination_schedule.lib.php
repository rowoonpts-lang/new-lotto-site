<?php

if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_PATH . '/include/lotto_result.lib.php';
include_once G5_PATH . '/include/lotto_distribution.lib.php';
include_once G5_PATH . '/include/lotto_manual_distribution.lib.php';
include_once G5_PATH . '/include/lotto_combination_sms.lib.php';
include_once G5_PATH . '/include/lotto_sms.lib.php';
include_once G5_PATH . '/include/lotto_sms_split.lib.php';

function lottoCombinationScheduleDayMap()
{
    return array(
        'mon' => 1,
        'tue' => 2,
        'wed' => 3,
        'thur' => 4,
        'fri' => 5,
    );
}

function lottoCombinationScheduleResolveMemberDay(array $row)
{
    $columns = array(
        'mon' => 'num_mon',
        'tue' => 'num_tue',
        'wed' => 'num_wed',
        'thur' => 'num_thur',
        'fri' => 'num_fri',
    );

    $selectedDay = '';
    $selectedQty = 0;
    $configuredCount = 0;

    foreach ($columns as $day => $column) {
        $qty = isset($row[$column])
            ? max(0, (int) $row[$column])
            : 0;

        if ($qty > 0) {
            $configuredCount++;
            $selectedDay = $day;
            $selectedQty = $qty;
        }
    }

    if ($configuredCount !== 1) {
        return array(
            'success' => false,
            'error' => '회원의 로또 배분 요일은 하나만 설정되어야 합니다.',
        );
    }

    return array(
        'success' => true,
        'day' => $selectedDay,
        'qty' => $selectedQty,
    );
}

function lottoCombinationScheduleGetWeek($drawNo)
{
    $drawNo = (int) $drawNo;

    if ($drawNo < 2) {
        return array(
            'success' => false,
            'error' => '회차가 올바르지 않습니다.',
        );
    }

    $sourceDrawNo = $drawNo - 1;
    $resultTable = lotto_result_table_name();

    $row = sql_fetch(
        "select draw_no, draw_date
           from `{$resultTable}`
          where draw_no = '{$sourceDrawNo}'
          limit 1",
        false
    );

    $drawDate = isset($row['draw_date'])
        ? trim((string) $row['draw_date'])
        : '';

    $timezone = new DateTimeZone('Asia/Seoul');

    $drawDateObject = DateTimeImmutable::createFromFormat(
        '!Y-m-d',
        $drawDate,
        $timezone
    );

    if (
        !$drawDateObject
        || $drawDateObject->format('Y-m-d') !== $drawDate
    ) {
        return array(
            'success' => false,
            'error' => '직전 회차 추첨일을 확인하지 못했습니다.',
        );
    }

    $referenceSunday = $drawDateObject->modify('+1 day');

    $weekStart = $referenceSunday
        ->modify('next monday')
        ->setTime(0, 0, 0);

    return array(
        'success' => true,
        'source_draw_no' => $sourceDrawNo,
        'week_start' => $weekStart,
        'week_end' => $weekStart
            ->modify('+4 days')
            ->setTime(23, 59, 59),
    );
}

function lottoCombinationScheduleReservePart(
    $groupId,
    $sender,
    $receiver,
    $message,
    $subject,
    DateTimeImmutable $reserveAt = null,
    array $history = array()
) {
    $groupId = trim((string) $groupId);
    $sender = lottoSmsNormalizePhone($sender);
    $receiver = lottoSmsNormalizePhone($receiver);
    $message = trim((string) $message);
    $subject = trim((string) $subject);

    if ($groupId === '' || strlen($groupId) > 20) {
        return array(
            'success' => false,
            'status' => 'invalid_group_id',
            'error' => '문자 그룹 ID가 올바르지 않습니다.',
        );
    }

    if (strlen($sender) < 8 || strlen($sender) > 15) {
        return array(
            'success' => false,
            'status' => 'invalid_sender',
            'error' => '발신번호가 올바르지 않습니다.',
        );
    }

    if (strlen($receiver) < 10 || strlen($receiver) > 15) {
        return array(
            'success' => false,
            'status' => 'invalid_receiver',
            'error' => '수신번호가 올바르지 않습니다.',
        );
    }

    if ($message === '') {
        return array(
            'success' => false,
            'status' => 'empty_message',
            'error' => '문자 내용이 없습니다.',
        );
    }

    $tableRow = sql_fetch(
        "show tables like 'OShotMSG'",
        false
    );

    if (!$tableRow || count($tableRow) < 1) {
        return array(
            'success' => false,
            'status' => 'oshot_table_missing',
            'error' => 'OShotMSG 테이블이 없습니다.',
        );
    }

    $groupIdSql = sql_real_escape_string($groupId);
    $receiverSql = sql_real_escape_string($receiver);

    $existing = sql_fetch(
        "select MsgID
           from OShotMSG
          where MsgGroupID = '{$groupIdSql}'
            and Receiver = '{$receiverSql}'
          order by MsgID asc
          limit 1",
        false
    );

    $sendType = lottoSmsGetMessageBytes($message) > 90
        ? 'LMS'
        : 'SMS';

    if (
        isset($existing['MsgID'])
        && (int) $existing['MsgID'] > 0
    ) {
        $existingMsgId = (int) $existing['MsgID'];

        if (
            !empty($history)
            && !lottoSmsRecordHistory(
                $existingMsgId,
                $groupId,
                $sender,
                $receiver,
                $message,
                $subject,
                $sendType,
                $history
            )
        ) {
            return array(
                'success' => false,
                'status' => 'history_failed',
                'error' => '기존 문자 발송내역 저장에 실패했습니다.',
            );
        }

        return array(
            'success' => true,
            'status' => 'already_queued',
            'msg_id' => $existingMsgId,
        );
    }

    $senderSql = sql_real_escape_string($sender);
    $messageSql = sql_real_escape_string($message);
    $subjectSql = sql_real_escape_string($subject);
    $sendTypeSql = sql_real_escape_string($sendType);

    $reserveSql = 'null';

    if ($reserveAt instanceof DateTimeImmutable) {
        $reserveSql = "'"
            . sql_real_escape_string(
                $reserveAt->format('Y-m-d H:i:s')
            )
            . "'";
    }

    if (sql_query('start transaction', false) === false) {
        return array(
            'success' => false,
            'status' => 'transaction_failed',
            'error' => '문자 예약 처리를 시작하지 못했습니다.',
        );
    }

    $insert = sql_query(
        "insert into OShotMSG
         set
            MsgGroupID = '{$groupIdSql}',
            SendType = '{$sendTypeSql}',
            Sender = '{$senderSql}',
            Receiver = '{$receiverSql}',
            Subject = '{$subjectSql}',
            Msg = '{$messageSql}',
            ReserveDT = {$reserveSql},
            SendResult = 0",
        false
    );

    if ($insert === false) {
        sql_query('rollback', false);

        return array(
            'success' => false,
            'status' => 'queue_failed',
            'error' => 'OShot 문자 예약 등록에 실패했습니다.',
        );
    }

    $msgId = (int) sql_insert_id();

    if (
        !lottoSmsRecordHistory(
            $msgId,
            $groupId,
            $sender,
            $receiver,
            $message,
            $subject,
            $sendType,
            $history
        )
    ) {
        sql_query('rollback', false);

        return array(
            'success' => false,
            'status' => 'history_failed',
            'error' => '문자 발송이력 저장에 실패하여 예약을 취소했습니다.',
        );
    }

    if (sql_query('commit', false) === false) {
        sql_query('rollback', false);

        return array(
            'success' => false,
            'status' => 'commit_failed',
            'error' => '문자 예약 저장을 완료하지 못했습니다.',
        );
    }

    return array(
        'success' => true,
        'status' => $reserveAt instanceof DateTimeImmutable
            ? 'reserved'
            : 'queued',
        'msg_id' => $msgId,
        'send_type' => $sendType,
        'reserve_at' => $reserveAt instanceof DateTimeImmutable
            ? $reserveAt->format('Y-m-d H:i:s')
            : null,
    );
}

function lottoCombinationScheduleReserveWithSplit(
    $groupId,
    $sender,
    $receiver,
    $message,
    $subject,
    DateTimeImmutable $reserveAt = null,
    array $history = array()
) {
    $split = lottoSmsSplitLmsMessage(
        $message,
        $subject,
        2000
    );

    if (empty($split['success'])) {
        return $split;
    }

    $messages = isset($split['messages'])
        && is_array($split['messages'])
        ? $split['messages']
        : array();

    if (count($messages) < 1) {
        return array(
            'success' => false,
            'status' => 'empty_parts',
            'error' => '발송할 문자 내용이 없습니다.',
        );
    }

    $msgIds = array();
    $newCount = 0;
    $skipCount = 0;
    $total = count($messages);

    foreach ($messages as $index => $partMessage) {
        $partNo = $index + 1;

        $partGroupId = $total === 1
            ? $groupId
            : lottoSmsBuildSplitGroupId(
                $groupId,
                $partNo
            );

        $partSubject = $subject;

        if ($total > 1 && $partSubject !== '') {
            $partSubject .= ' '
                . $partNo
                . '/'
                . $total;
        }

        $queued = lottoCombinationScheduleReservePart(
            $partGroupId,
            $sender,
            $receiver,
            $partMessage,
            $partSubject,
            $reserveAt,
            $history
        );

        if (empty($queued['success'])) {
            return array(
                'success' => false,
                'status' => 'partial_failed',
                'error' => isset($queued['error'])
                    ? $queued['error']
                    : '문자 예약 중 오류가 발생했습니다.',
                'msg_ids' => $msgIds,
            );
        }

        if (isset($queued['msg_id'])) {
            $msgIds[] = (int) $queued['msg_id'];
        }

        if (
            isset($queued['status'])
            && $queued['status'] === 'already_queued'
        ) {
            $skipCount++;
        } else {
            $newCount++;
        }
    }

    return array(
        'success' => true,
        'status' => $newCount > 0
            ? 'reserved'
            : 'already_queued',
        'msg_ids' => $msgIds,
        'queued_part_count' => $newCount,
        'skipped_part_count' => $skipCount,
        'reserve_at' => $reserveAt instanceof DateTimeImmutable
            ? $reserveAt->format('Y-m-d H:i:s')
            : null,
    );
}

function lottoCombinationScheduleCancelPendingMember(
    $drawNo,
    $mbId,
    DateTimeImmutable $now
) {
    $drawNo = (int) $drawNo;
    $mbId = trim((string) $mbId);
    $mbIdSql = sql_real_escape_string($mbId);

    $result = sql_query(
        "select
            h.oshot_msg_id,
            o.SendResult,
            o.QStatus,
            o.ReserveDT
         from l_sms_history h
         inner join OShotMSG o
            on o.MsgID = h.oshot_msg_id
         where h.draw_no = '{$drawNo}'
           and h.mb_id = '{$mbIdSql}'
           and h.send_category = 'combination'
         order by h.oshot_msg_id asc",
        false
    );

    if ($result === false) {
        return array(
            'success' => false,
            'error' => '현재 회차 문자 예약 상태를 확인하지 못했습니다.',
        );
    }

    $cancelIds = array();

    while ($row = sql_fetch_array($result)) {
        $msgId = (int) $row['oshot_msg_id'];
        $sendResult = isset($row['SendResult'])
            ? (int) $row['SendResult']
            : -1;
        $qStatus = isset($row['QStatus'])
            ? trim((string) $row['QStatus'])
            : '';
        $reserveText = isset($row['ReserveDT'])
            ? trim((string) $row['ReserveDT'])
            : '';

        if ($sendResult !== 0 || $qStatus !== '') {
            return array(
                'success' => false,
                'status' => 'already_started',
                'error' => '현재 회차 문자가 이미 발송 처리에 들어가 바로적용할 수 없습니다.',
            );
        }

        if ($reserveText === '') {
            return array(
                'success' => false,
                'status' => 'already_due',
                'error' => '현재 회차 즉시발송 문자가 이미 큐에 있어 바로적용할 수 없습니다.',
            );
        }

        $reserveAt = new DateTimeImmutable(
            $reserveText,
            new DateTimeZone('Asia/Seoul')
        );

        if ($reserveAt <= $now) {
            return array(
                'success' => false,
                'status' => 'already_due',
                'error' => '현재 회차 문자 발송시간이 이미 지나 바로적용할 수 없습니다.',
            );
        }

        if ($msgId > 0) {
            $cancelIds[] = $msgId;
        }
    }

    if (empty($cancelIds)) {
        return array(
            'success' => true,
            'status' => 'nothing_to_cancel',
            'cancelled_count' => 0,
        );
    }

    $idList = implode(
        ',',
        array_map('intval', $cancelIds)
    );

    if (sql_query('start transaction', false) === false) {
        return array(
            'success' => false,
            'error' => '문자 예약 취소 처리를 시작하지 못했습니다.',
        );
    }

    if (
        sql_query(
            "delete from OShotMSG
              where MsgID in ({$idList})",
            false
        ) === false
    ) {
        sql_query('rollback', false);

        return array(
            'success' => false,
            'error' => 'OShot 예약문자를 취소하지 못했습니다.',
        );
    }

    if (
        sql_query(
            "delete from l_sms_history
              where oshot_msg_id in ({$idList})
                and draw_no = '{$drawNo}'
                and mb_id = '{$mbIdSql}'
                and send_category = 'combination'",
            false
        ) === false
    ) {
        sql_query('rollback', false);

        return array(
            'success' => false,
            'error' => '취소된 문자 발송이력을 정리하지 못했습니다.',
        );
    }

    if (sql_query('commit', false) === false) {
        sql_query('rollback', false);

        return array(
            'success' => false,
            'error' => '문자 예약 취소를 완료하지 못했습니다.',
        );
    }

    return array(
        'success' => true,
        'status' => 'cancelled',
        'cancelled_count' => count($cancelIds),
    );
}

function lottoCombinationScheduleResizeWeeklyMember(
    $drawNo,
    $mbId,
    $memberType,
    $targetCount,
    $createdBy
) {
    $drawNo = (int) $drawNo;
    $mbId = trim((string) $mbId);
    $memberType = trim((string) $memberType);
    $targetCount = max(1, (int) $targetCount);

    $mbIdSql = sql_real_escape_string($mbId);

    $countRow = sql_fetch(
        "select count(*) as cnt
           from l_member_combination
          where draw_no = '{$drawNo}'
            and mb_id = '{$mbIdSql}'
            and distribution_type = 'weekly'",
        false
    );

    $currentCount = isset($countRow['cnt'])
        ? (int) $countRow['cnt']
        : 0;

    if ($currentCount === $targetCount) {
        return array(
            'success' => true,
            'status' => 'unchanged',
            'count' => $currentCount,
        );
    }

    if ($currentCount > $targetCount) {
        $removeCount = $currentCount - $targetCount;

        $delete = sql_query(
            "delete from l_member_combination
              where lmc_id in (
                select lmc_id
                  from (
                    select lmc_id
                      from l_member_combination
                     where draw_no = '{$drawNo}'
                       and mb_id = '{$mbIdSql}'
                       and distribution_type = 'weekly'
                     order by distribution_seq desc, lmc_id desc
                     limit {$removeCount}
                  ) remove_rows
              )",
            false
        );

        if ($delete === false) {
            return array(
                'success' => false,
                'error' => '현재 회차 배분 수량을 줄이지 못했습니다.',
            );
        }

        return array(
            'success' => true,
            'status' => 'reduced',
            'count' => $targetCount,
        );
    }

    if ($currentCount === 0) {
        $distributed = lottoDistributionDistributeWeeklyMember(
            $drawNo,
            $mbId,
            $memberType,
            $targetCount,
            $createdBy
        );

        if (empty($distributed['success'])) {
            return array(
                'success' => false,
                'error' => isset($distributed['error'])
                    ? $distributed['error']
                    : '현재 회차 조합을 배분하지 못했습니다.',
            );
        }

        return array(
            'success' => true,
            'status' => 'distributed',
            'count' => $targetCount,
        );
    }

    $addCount = $targetCount - $currentCount;

    $added = lottoManualDistributionDistributeMember(
        $drawNo,
        $mbId,
        $memberType,
        $addCount,
        $createdBy
    );

    if (empty($added['success'])) {
        return array(
            'success' => false,
            'error' => isset($added['error'])
                ? $added['error']
                : '현재 회차 추가 조합을 배분하지 못했습니다.',
        );
    }

    $batch = isset($added['distribution_batch'])
        ? trim((string) $added['distribution_batch'])
        : '';

    if ($batch === '') {
        return array(
            'success' => false,
            'error' => '추가 배분 묶음을 확인하지 못했습니다.',
        );
    }

    $batchSql = sql_real_escape_string($batch);

    $update = sql_query(
        "update l_member_combination
            set
                distribution_seq = distribution_seq + {$currentCount},
                distribution_type = 'weekly',
                distribution_day = null
          where draw_no = '{$drawNo}'
            and mb_id = '{$mbIdSql}'
            and distribution_batch = '{$batchSql}'
            and distribution_type = 'manual'",
        false
    );

    if ($update === false) {
        return array(
            'success' => false,
            'error' => '추가 배분 조합을 현재 회차 주간배분으로 전환하지 못했습니다.',
        );
    }

    return array(
        'success' => true,
        'status' => 'increased',
        'count' => $targetCount,
    );
}

function lottoCombinationScheduleQueueMember(
    $drawNo,
    $mbId,
    DateTimeImmutable $now
) {
    $drawNo = (int) $drawNo;
    $mbId = trim((string) $mbId);
    $mbIdSql = sql_real_escape_string($mbId);

    $week = lottoCombinationScheduleGetWeek($drawNo);

    if (empty($week['success'])) {
        return $week;
    }

    $memberRow = sql_fetch(
        "select
            e.mb_id,
            e.start_date,
            e.end_date,
            e.num_mon,
            e.num_tue,
            e.num_wed,
            e.num_thur,
            e.num_fri,
            m.mb_type,
            m.mb_hp,
            m.mb_leave_date
         from g5_member_etc e
         inner join g5_member m
            on m.mb_id = e.mb_id
         where e.mb_id = '{$mbIdSql}'
         limit 1",
        false
    );

    if (!isset($memberRow['mb_id'])) {
        return array(
            'success' => false,
            'error' => '회원 배분 설정을 확인하지 못했습니다.',
        );
    }

    $paidTypes = fnGetTypePre();

    if (
        !in_array(
            trim((string) $memberRow['mb_type']),
            $paidTypes,
            true
        )
    ) {
        return array(
            'success' => true,
            'status' => 'skipped',
            'message' => '유료회원이 아닙니다.',
        );
    }

    if (
        trim((string) $memberRow['mb_leave_date']) !== ''
    ) {
        return array(
            'success' => true,
            'status' => 'skipped',
            'message' => '탈퇴회원입니다.',
        );
    }

    $day = lottoCombinationScheduleResolveMemberDay(
        $memberRow
    );

    if (empty($day['success'])) {
        return $day;
    }

    $dayMap = lottoCombinationScheduleDayMap();
    $weekDay = $dayMap[$day['day']];
    $count = (int) $day['qty'];

    $targetDate = $week['week_start']
        ->modify('+' . ($weekDay - 1) . ' days');

    $targetDateText = $targetDate->format('Y-m-d');

    if (
        trim((string) $memberRow['start_date']) > $targetDateText
        || trim((string) $memberRow['end_date']) < $targetDateText
    ) {
        return array(
            'success' => true,
            'status' => 'skipped',
            'message' => '해당 발송일이 회원 이용기간에 포함되지 않습니다.',
        );
    }

    $rows = array();

    $combinationResult = sql_query(
        "select
            lmc_id,
            num1, num2, num3, num4, num5, num6
         from l_member_combination
         where draw_no = '{$drawNo}'
           and mb_id = '{$mbIdSql}'
           and distribution_type = 'weekly'
         order by distribution_seq asc, lmc_id asc
         limit {$count}",
        false
    );

    if ($combinationResult === false) {
        return array(
            'success' => false,
            'error' => '현재 회차 배분번호를 조회하지 못했습니다.',
        );
    }

    while ($combination = sql_fetch_array(
        $combinationResult
    )) {
        $rows[] = $combination;
    }

    if (count($rows) !== $count) {
        return array(
            'success' => false,
            'error' => '설정 수량과 현재 회차 배분번호 수량이 일치하지 않습니다.',
        );
    }

    $receiver = lottoSmsNormalizePhone(
        $memberRow['mb_hp']
    );

    $smsConfig = lottoSmsGetConfig();
    $sender = isset($smsConfig['sender_phone'])
        ? lottoSmsNormalizePhone(
            $smsConfig['sender_phone']
        )
        : '';

    if (strlen($sender) < 8 || strlen($sender) > 15) {
        return array(
            'success' => false,
            'error' => '설정관리의 문자 발신번호를 확인해주세요.',
        );
    }

    if (
        strlen($receiver) < 10
        || strlen($receiver) > 15
    ) {
        return array(
            'success' => false,
            'error' => '회원 휴대폰번호가 올바르지 않습니다.',
        );
    }

    $message = lottoSmsBuildCombinationMessage(
        $drawNo,
        '추천번호',
        $rows
    );

    $usageGroupId = lottoCombinationSmsGroupId(
        $drawNo,
        $weekDay,
        $mbId
    );

    $scheduledAt = $targetDate->setTime(10, 0, 0);

    /*
     * 바로적용 또는 장애복구가 발송 예정시각보다 늦게
     * 실행된 경우에는 즉시 큐 등록한다.
     */
    $reserveAt = $scheduledAt > $now
        ? $scheduledAt
        : null;

    $queued = lottoCombinationScheduleReserveWithSplit(
        $usageGroupId,
        $sender,
        $receiver,
        $message,
        '추천번호',
        $reserveAt,
        array(
            'mb_id' => $mbId,
            'sender_mb_id' => '',
            'send_category' => 'combination',
            'usage_group_id' => $usageGroupId,
            'draw_no' => $drawNo,
            'combination_count' => $count,
        )
    );

    if (empty($queued['success'])) {
        return $queued;
    }

    $queued['draw_no'] = $drawNo;
    $queued['mb_id'] = $mbId;
    $queued['weekday'] = $weekDay;
    $queued['combination_count'] = $count;
    $queued['scheduled_at'] =
        $scheduledAt->format('Y-m-d H:i:s');

    return $queued;
}

function lottoCombinationScheduleQueueDraw(
    $drawNo,
    DateTimeImmutable $now
) {
    $drawNo = (int) $drawNo;

    $result = sql_query(
        "select distinct mb_id
           from l_member_combination
          where draw_no = '{$drawNo}'
            and distribution_type = 'weekly'
          order by mb_id asc",
        false
    );

    if ($result === false) {
        return array(
            'success' => false,
            'error' => '주간 문자 예약 회원을 조회하지 못했습니다.',
        );
    }

    $reservedCount = 0;
    $skippedCount = 0;
    $failedCount = 0;
    $errors = array();

    while ($row = sql_fetch_array($result)) {
        $mbId = trim((string) $row['mb_id']);

        $queued = lottoCombinationScheduleQueueMember(
            $drawNo,
            $mbId,
            $now
        );

        if (empty($queued['success'])) {
            $failedCount++;
            $errors[] = $mbId . ': ' . (
                isset($queued['error'])
                    ? $queued['error']
                    : '문자 예약 실패'
            );
            continue;
        }

        if (
            isset($queued['status'])
            && (
                $queued['status'] === 'skipped'
                || $queued['status'] === 'already_queued'
            )
        ) {
            $skippedCount++;
        } else {
            $reservedCount++;
        }
    }

    return array(
        'success' => $failedCount === 0,
        'status' => $failedCount === 0
            ? 'completed'
            : 'partial_failed',
        'draw_no' => $drawNo,
        'reserved_count' => $reservedCount,
        'skipped_count' => $skippedCount,
        'failed_count' => $failedCount,
        'errors' => $errors,
    );
}

function lottoCombinationScheduleInsertPartsInTransaction(
    $groupId,
    $sender,
    $receiver,
    $message,
    $subject,
    DateTimeImmutable $reserveAt = null,
    array $history = array()
) {
    $split = lottoSmsSplitLmsMessage(
        $message,
        $subject,
        2000
    );

    if (empty($split['success'])) {
        return $split;
    }

    $messages = isset($split['messages'])
        && is_array($split['messages'])
        ? $split['messages']
        : array();

    $partCount = count($messages);

    if ($partCount < 1) {
        return array(
            'success' => false,
            'error' => '발송할 문자 내용이 없습니다.',
        );
    }

    $sender = lottoSmsNormalizePhone($sender);
    $receiver = lottoSmsNormalizePhone($receiver);

    if (strlen($sender) < 8 || strlen($sender) > 15) {
        return array(
            'success' => false,
            'error' => '발신번호가 올바르지 않습니다.',
        );
    }

    if (strlen($receiver) < 10 || strlen($receiver) > 15) {
        return array(
            'success' => false,
            'error' => '수신번호가 올바르지 않습니다.',
        );
    }

    $senderSql = sql_real_escape_string($sender);
    $receiverSql = sql_real_escape_string($receiver);

    $reserveSql = 'null';

    if ($reserveAt instanceof DateTimeImmutable) {
        $reserveSql = "'"
            . sql_real_escape_string(
                $reserveAt->format('Y-m-d H:i:s')
            )
            . "'";
    }

    $msgIds = array();

    foreach ($messages as $index => $partMessage) {
        $partNo = $index + 1;

        $partGroupId = $partCount === 1
            ? $groupId
            : lottoSmsBuildSplitGroupId(
                $groupId,
                $partNo
            );

        $partSubject = trim((string) $subject);

        if ($partCount > 1 && $partSubject !== '') {
            $partSubject .= ' '
                . $partNo
                . '/'
                . $partCount;
        }

        $partGroupId = trim((string) $partGroupId);

        if (
            $partGroupId === ''
            || strlen($partGroupId) > 20
        ) {
            return array(
                'success' => false,
                'error' => '문자 그룹 ID가 올바르지 않습니다.',
            );
        }

        $partMessage = trim((string) $partMessage);

        if ($partMessage === '') {
            return array(
                'success' => false,
                'error' => '문자 내용이 없습니다.',
            );
        }

        $sendType = lottoSmsGetMessageBytes(
            $partMessage
        ) > 90
            ? 'LMS'
            : 'SMS';

        $groupSql = sql_real_escape_string(
            $partGroupId
        );

        $messageSql = sql_real_escape_string(
            $partMessage
        );

        $subjectSql = sql_real_escape_string(
            $partSubject
        );

        $sendTypeSql = sql_real_escape_string(
            $sendType
        );

        $insert = sql_query(
            "insert into OShotMSG
             set
                MsgGroupID = '{$groupSql}',
                SendType = '{$sendTypeSql}',
                Sender = '{$senderSql}',
                Receiver = '{$receiverSql}',
                Subject = '{$subjectSql}',
                Msg = '{$messageSql}',
                ReserveDT = {$reserveSql},
                SendResult = 0",
            false
        );

        if ($insert === false) {
            return array(
                'success' => false,
                'error' => '새 OShot 문자 예약을 저장하지 못했습니다.',
            );
        }

        $msgId = (int) sql_insert_id();

        if ($msgId < 1) {
            return array(
                'success' => false,
                'error' => '새 OShot 문자 번호를 확인하지 못했습니다.',
            );
        }

        if (
            !lottoSmsRecordHistory(
                $msgId,
                $partGroupId,
                $sender,
                $receiver,
                $partMessage,
                $partSubject,
                $sendType,
                $history
            )
        ) {
            return array(
                'success' => false,
                'error' => '새 문자 발송내역을 저장하지 못했습니다.',
            );
        }

        $msgIds[] = $msgId;
    }

    return array(
        'success' => true,
        'status' => $reserveAt instanceof DateTimeImmutable
            ? 'reserved'
            : 'queued',
        'msg_ids' => $msgIds,
        'part_count' => count($msgIds),
    );
}

function lottoCombinationScheduleApplyCurrent(
    $mbId,
    $distributionDay,
    $distributionQty,
    DateTimeImmutable $now,
    $createdBy
) {
    $mbId = trim((string) $mbId);
    $distributionDay = trim(
        (string) $distributionDay
    );
    $distributionQty = max(
        1,
        (int) $distributionQty
    );
    $createdBy = trim((string) $createdBy);

    $dayMap = lottoCombinationScheduleDayMap();

    if (!isset($dayMap[$distributionDay])) {
        return array(
            'success' => false,
            'error' => '바로적용할 배분 요일이 올바르지 않습니다.',
        );
    }

    $run = sql_fetch(
        "select
            lfr_id,
            draw_no,
            status,
            candidate_count
         from l_filter_run
         where status = 'filtered'
           and candidate_count > 0
         order by draw_no desc
         limit 1",
        false
    );

    $runId = isset($run['lfr_id'])
        ? (int) $run['lfr_id']
        : 0;

    $drawNo = isset($run['draw_no'])
        ? (int) $run['draw_no']
        : 0;

    if ($runId < 1 || $drawNo < 1) {
        return array(
            'success' => false,
            'error' => '바로적용할 현재 회차가 없습니다.',
        );
    }

    $week = lottoCombinationScheduleGetWeek(
        $drawNo
    );

    if (empty($week['success'])) {
        return $week;
    }

    if ($now > $week['week_end']) {
        return array(
            'success' => false,
            'error' => '현재 회차 발송 주간이 이미 종료되었습니다.',
        );
    }

    $mbIdSql = sql_real_escape_string($mbId);
    $createdBySql = sql_real_escape_string(
        $createdBy !== ''
            ? $createdBy
            : 'admin'
    );

    $member = sql_fetch(
        "select
            m.mb_id,
            m.mb_type,
            m.mb_hp,
            m.mb_leave_date,
            e.start_date,
            e.end_date
         from g5_member m
         inner join g5_member_etc e
            on e.mb_id = m.mb_id
         where m.mb_id = '{$mbIdSql}'
         limit 1",
        false
    );

    if (!isset($member['mb_id'])) {
        return array(
            'success' => false,
            'error' => '회원정보를 확인하지 못했습니다.',
        );
    }

    $memberType = trim(
        (string) $member['mb_type']
    );

    if (
        !in_array(
            $memberType,
            fnGetTypePre(),
            true
        )
    ) {
        return array(
            'success' => false,
            'error' => '유료회원만 현재 회차 바로적용이 가능합니다.',
        );
    }

    if (
        trim(
            (string) $member['mb_leave_date']
        ) !== ''
    ) {
        return array(
            'success' => false,
            'error' => '탈퇴회원은 바로적용할 수 없습니다.',
        );
    }

    $weekDay = (int) $dayMap[
        $distributionDay
    ];

    $targetDate = $week['week_start']
        ->modify(
            '+'
            . ($weekDay - 1)
            . ' days'
        );

    $targetDateText = $targetDate->format(
        'Y-m-d'
    );

    $startDate = trim(
        (string) $member['start_date']
    );

    $endDate = trim(
        (string) $member['end_date']
    );

    if (
        $startDate === ''
        || $endDate === ''
        || $startDate > $targetDateText
        || $endDate < $targetDateText
    ) {
        return array(
            'success' => false,
            'error' => '새 발송일이 회원 이용기간에 포함되지 않습니다.',
        );
    }

    $receiver = lottoSmsNormalizePhone(
        $member['mb_hp']
    );

    $smsConfig = lottoSmsGetConfig();

    $sender = isset($smsConfig['sender_phone'])
        ? lottoSmsNormalizePhone(
            $smsConfig['sender_phone']
        )
        : '';

    if (
        strlen($sender) < 8
        || strlen($sender) > 15
    ) {
        return array(
            'success' => false,
            'error' => '설정관리의 문자 발신번호를 확인해주세요.',
        );
    }

    if (
        strlen($receiver) < 10
        || strlen($receiver) > 15
    ) {
        return array(
            'success' => false,
            'error' => '회원 휴대폰번호가 올바르지 않습니다.',
        );
    }

    $scheduledAt = $targetDate
        ->setTime(10, 0, 0);

    $reserveAt = $scheduledAt > $now
        ? $scheduledAt
        : null;

    $transactionStarted = false;

    try {
        if (
            sql_query(
                'start transaction',
                false
            ) === false
        ) {
            throw new RuntimeException(
                '바로적용 트랜잭션을 시작하지 못했습니다.'
            );
        }

        $transactionStarted = true;

        /*
         * 현재 회차 기존 문자 상태 잠금.
         *
         * 이미 OShot 처리에 들어간 문자가 하나라도 있으면
         * 현재 회차 변경을 금지한다.
         */
        $historyResult = sql_query(
            "select
                h.lsh_id,
                h.oshot_msg_id,
                o.MsgID,
                o.SendResult,
                o.QStatus,
                o.ReserveDT
             from l_sms_history h
             left join OShotMSG o
                on o.MsgID = h.oshot_msg_id
             where h.draw_no = '{$drawNo}'
               and h.mb_id = '{$mbIdSql}'
               and h.send_category = 'combination'
             order by h.oshot_msg_id asc
             for update",
            false
        );

        if ($historyResult === false) {
            throw new RuntimeException(
                '현재 회차 문자 상태를 확인하지 못했습니다.'
            );
        }

        $oldMsgIds = array();

        while (
            $historyRow = sql_fetch_array(
                $historyResult
            )
        ) {
            $historyMsgId = isset(
                $historyRow['oshot_msg_id']
            )
                ? (int) $historyRow['oshot_msg_id']
                : 0;

            $oshotMsgId = isset(
                $historyRow['MsgID']
            )
                ? (int) $historyRow['MsgID']
                : 0;

            if (
                $historyMsgId < 1
                || $oshotMsgId < 1
            ) {
                throw new RuntimeException(
                    '현재 회차 문자 이력과 OShot 큐가 일치하지 않습니다.'
                );
            }

            $sendResult = isset(
                $historyRow['SendResult']
            )
                ? (int) $historyRow['SendResult']
                : -1;

            $qStatus = isset(
                $historyRow['QStatus']
            )
                ? trim(
                    (string) $historyRow['QStatus']
                )
                : '';

            $oldReserveText = isset(
                $historyRow['ReserveDT']
            )
                ? trim(
                    (string) $historyRow['ReserveDT']
                )
                : '';

            if (
                $sendResult !== 0
                || $qStatus !== ''
            ) {
                throw new RuntimeException(
                    '현재 회차 문자가 이미 발송 처리에 들어가 바로적용할 수 없습니다.'
                );
            }

            if ($oldReserveText === '') {
                throw new RuntimeException(
                    '현재 회차 즉시발송 문자가 이미 등록되어 바로적용할 수 없습니다.'
                );
            }

            $oldReserveAt = new DateTimeImmutable(
                $oldReserveText,
                new DateTimeZone(
                    'Asia/Seoul'
                )
            );

            if ($oldReserveAt <= $now) {
                throw new RuntimeException(
                    '현재 회차 기존 문자 발송시간이 이미 지나 바로적용할 수 없습니다.'
                );
            }

            $oldMsgIds[] = $historyMsgId;
        }

        /*
         * 현재 회차 weekly 조합 개수 확인.
         */
        $countRow = sql_fetch(
            "select count(*) as cnt
             from l_member_combination
             where draw_no = '{$drawNo}'
               and mb_id = '{$mbIdSql}'
               and distribution_type = 'weekly'
             for update",
            false
        );

        $currentCount = isset(
            $countRow['cnt']
        )
            ? (int) $countRow['cnt']
            : 0;

        /*
         * 수량 감소:
         * 아직 발송되지 않은 현재 회차이므로
         * 뒤쪽 조합부터 제거한다.
         */
        if ($currentCount > $distributionQty) {
            $removeCount =
                $currentCount
                - $distributionQty;

            $delete = sql_query(
                "delete from l_member_combination
                 where lmc_id in (
                    select lmc_id
                    from (
                        select lmc_id
                        from l_member_combination
                        where draw_no = '{$drawNo}'
                          and mb_id = '{$mbIdSql}'
                          and distribution_type = 'weekly'
                        order by
                            distribution_seq desc,
                            lmc_id desc
                        limit {$removeCount}
                    ) rows_to_remove
                 )",
                false
            );

            if ($delete === false) {
                throw new RuntimeException(
                    '현재 회차 배분 수량을 줄이지 못했습니다.'
                );
            }
        }

        /*
         * 수량 증가:
         * 공용 cursor를 잠그고 다음 후보를 이어서 배분한다.
         */
        if ($currentCount < $distributionQty) {
            $addCount =
                $distributionQty
                - $currentCount;

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
                    '현재 회차 배분 커서를 준비하지 못했습니다.'
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

            $cursorId = isset(
                $cursor['ldc_id']
            )
                ? (int) $cursor['ldc_id']
                : 0;

            $lastRankNo = isset(
                $cursor['last_rank_no']
            )
                ? (int) $cursor['last_rank_no']
                : 0;

            $cycleNo = isset(
                $cursor['cycle_no']
            )
                ? (int) $cursor['cycle_no']
                : 1;

            if (
                $cursorId < 1
                || $cycleNo < 1
            ) {
                throw new RuntimeException(
                    '현재 회차 배분 커서를 확인하지 못했습니다.'
                );
            }

            $selection =
                lottoDistributionSelectCandidates(
                    $drawNo,
                    $addCount,
                    $lastRankNo,
                    $cycleNo
                );

            if (
                !isset($selection['success'])
                || !$selection['success']
            ) {
                throw new RuntimeException(
                    isset($selection['error'])
                        ? $selection['error']
                        : '추가 배분 후보를 조회하지 못했습니다.'
                );
            }

            $candidates = isset(
                $selection['candidates']
            )
                ? $selection['candidates']
                : array();

            if (
                count($candidates)
                !== $addCount
            ) {
                throw new RuntimeException(
                    '추가 배분 후보 수량이 요청 수량과 일치하지 않습니다.'
                );
            }

            $newCycleNo = isset(
                $selection['cycle_no']
            )
                ? (int) $selection['cycle_no']
                : $cycleNo;

            $batch = sprintf(
                '%d-weekly-now-%s-%s',
                $drawNo,
                date('YmdHis'),
                $mbId
            );

            $batchSql =
                sql_real_escape_string(
                    $batch
                );

            $memberTypeSql =
                sql_real_escape_string(
                    $memberType
                );

            $newLastRankNo =
                $lastRankNo;

            foreach (
                $candidates
                as $index => $candidate
            ) {
                $lfcId = (int)
                    $candidate['lfc_id'];

                $rankNo = (int)
                    $candidate['rank_no'];

                $candidateCycle = isset(
                    $candidate['_candidate_cycle']
                )
                    ? (int)
                        $candidate['_candidate_cycle']
                    : $newCycleNo;

                $score = sql_real_escape_string(
                    (string)
                    $candidate['score']
                );

                $seq =
                    $currentCount
                    + $index
                    + 1;

                $insert = sql_query(
                    "insert into l_member_combination
                     set
                        draw_no = '{$drawNo}',
                        mb_id = '{$mbIdSql}',
                        member_type = '{$memberTypeSql}',
                        lfc_id = '{$lfcId}',
                        candidate_rank = '{$rankNo}',
                        candidate_cycle = '{$candidateCycle}',
                        num1 = '"
                        . (int) $candidate['num1']
                        . "',
                        num2 = '"
                        . (int) $candidate['num2']
                        . "',
                        num3 = '"
                        . (int) $candidate['num3']
                        . "',
                        num4 = '"
                        . (int) $candidate['num4']
                        . "',
                        num5 = '"
                        . (int) $candidate['num5']
                        . "',
                        num6 = '"
                        . (int) $candidate['num6']
                        . "',
                        distribution_type = 'weekly',
                        distribution_day = null,
                        distribution_batch = '{$batchSql}',
                        distribution_seq = '{$seq}',
                        score = '{$score}',
                        sms_required = 0,
                        sms_status = 'not_required',
                        created_by = '{$createdBySql}'",
                    false
                );

                if ($insert === false) {
                    throw new RuntimeException(
                        '현재 회차 추가 조합 저장에 실패했습니다.'
                    );
                }

                $newLastRankNo =
                    $rankNo;
            }

            if (
                sql_query(
                    "update l_distribution_cursor
                     set
                        last_rank_no = '{$newLastRankNo}',
                        cycle_no = '{$newCycleNo}'
                     where ldc_id = '{$cursorId}'",
                    false
                ) === false
            ) {
                throw new RuntimeException(
                    '현재 회차 배분 커서를 저장하지 못했습니다.'
                );
            }
        }

        /*
         * 최종 수량을 다시 확인한다.
         */
        $finalCountRow = sql_fetch(
            "select count(*) as cnt
             from l_member_combination
             where draw_no = '{$drawNo}'
               and mb_id = '{$mbIdSql}'
               and distribution_type = 'weekly'",
            false
        );

        $finalCount = isset(
            $finalCountRow['cnt']
        )
            ? (int) $finalCountRow['cnt']
            : 0;

        if ($finalCount !== $distributionQty) {
            throw new RuntimeException(
                '현재 회차 최종 배분 수량이 설정 수량과 일치하지 않습니다.'
            );
        }

        /*
         * 새 문자에 사용할 조합을 확정한다.
         */
        $combinationRows = array();

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
               and mb_id = '{$mbIdSql}'
               and distribution_type = 'weekly'
             order by
                distribution_seq asc,
                lmc_id asc",
            false
        );

        if ($combinationResult === false) {
            throw new RuntimeException(
                '새 문자에 사용할 배분번호를 확인하지 못했습니다.'
            );
        }

        while (
            $combinationRow =
                sql_fetch_array(
                    $combinationResult
                )
        ) {
            $combinationRows[] =
                $combinationRow;
        }

        if (
            count($combinationRows)
            !== $distributionQty
        ) {
            throw new RuntimeException(
                '새 문자 조합 수량이 설정 수량과 일치하지 않습니다.'
            );
        }

        $message =
            lottoSmsBuildCombinationMessage(
                $drawNo,
                '추천번호',
                $combinationRows
            );

        /*
         * 여기까지 성공한 뒤에만
         * 기존 미래 예약을 제거한다.
         */
        if (!empty($oldMsgIds)) {
            $idList = implode(
                ',',
                array_map(
                    'intval',
                    $oldMsgIds
                )
            );

            if (
                sql_query(
                    "delete from OShotMSG
                     where MsgID in ({$idList})",
                    false
                ) === false
            ) {
                throw new RuntimeException(
                    '기존 OShot 예약을 취소하지 못했습니다.'
                );
            }

            if (
                sql_query(
                    "delete from l_sms_history
                     where oshot_msg_id in ({$idList})
                       and draw_no = '{$drawNo}'
                       and mb_id = '{$mbIdSql}'
                       and send_category = 'combination'",
                    false
                ) === false
            ) {
                throw new RuntimeException(
                    '기존 문자 예약 이력을 정리하지 못했습니다.'
                );
            }
        }

        $usageGroupId =
            lottoCombinationSmsGroupId(
                $drawNo,
                $weekDay,
                $mbId
            );

        $insertSms =
            lottoCombinationScheduleInsertPartsInTransaction(
                $usageGroupId,
                $sender,
                $receiver,
                $message,
                '추천번호',
                $reserveAt,
                array(
                    'mb_id' => $mbId,
                    'sender_mb_id' => '',
                    'send_category' => 'combination',
                    'usage_group_id' => $usageGroupId,
                    'draw_no' => $drawNo,
                    'combination_count' =>
                        $distributionQty,
                )
            );

        if (empty($insertSms['success'])) {
            throw new RuntimeException(
                isset($insertSms['error'])
                    ? $insertSms['error']
                    : '새 문자 예약을 등록하지 못했습니다.'
            );
        }

        if (
            sql_query(
                'commit',
                false
            ) === false
        ) {
            throw new RuntimeException(
                '바로적용 트랜잭션을 완료하지 못했습니다.'
            );
        }

        $transactionStarted = false;

        return array(
            'success' => true,
            'status' => 'applied',
            'draw_no' => $drawNo,
            'distribution_day' =>
                $distributionDay,
            'distribution_qty' =>
                $distributionQty,
            'previous_count' =>
                $currentCount,
            'final_count' =>
                $finalCount,
            'cancelled_count' =>
                count($oldMsgIds),
            'scheduled_at' =>
                $scheduledAt->format(
                    'Y-m-d H:i:s'
                ),
            'send_mode' =>
                $reserveAt
                    instanceof DateTimeImmutable
                    ? 'reserved'
                    : 'immediate',
        );
    } catch (Throwable $e) {
        if ($transactionStarted) {
            sql_query(
                'rollback',
                false
            );
        }

        return array(
            'success' => false,
            'status' => 'failed',
            'error' => $e->getMessage(),
        );
    }
}

