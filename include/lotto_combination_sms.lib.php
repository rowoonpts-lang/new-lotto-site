<?php

if (!defined('_GNUBOARD_')) {
    exit;
}

include_once G5_PATH . '/include/lotto_sms.lib.php';

function lottoCombinationSmsGroupId($drawNo, $weekDay, $mbId)
{
    return 'LC'
        . (int) $drawNo
        . (int) $weekDay
        . substr(sha1((string) $mbId), 0, 10);
}

function lottoCombinationSmsGetDaySlice(array $memberEtc, $weekDay)
{
    $weekDay = (int) $weekDay;
    $columns = array(
        1 => 'num_mon',
        2 => 'num_tue',
        3 => 'num_wed',
        4 => 'num_thur',
        5 => 'num_fri',
    );

    if (!isset($columns[$weekDay])) {
        return array('offset' => 0, 'count' => 0);
    }

    $offset = 0;
    for ($day = 1; $day < $weekDay; $day++) {
        $column = $columns[$day];
        $offset += isset($memberEtc[$column])
            ? max(0, (int) $memberEtc[$column])
            : 0;
    }

    $todayColumn = $columns[$weekDay];
    $count = isset($memberEtc[$todayColumn])
        ? max(0, (int) $memberEtc[$todayColumn])
        : 0;

    return array(
        'offset' => $offset,
        'count' => $count,
    );
}

function lottoCombinationSmsQueueWeekday(DateTimeImmutable $now)
{
    $weekDay = (int) $now->format('w');

    if ($weekDay < 1 || $weekDay > 5) {
        return array(
            'success' => true,
            'status' => 'skipped',
            'message' => '월요일부터 금요일까지만 조합 문자를 발송합니다.',
            'queued_count' => 0,
            'skipped_count' => 0,
            'failed_count' => 0,
        );
    }

    $run = sql_fetch(
        "select draw_no
           from l_filter_run
          where status = 'filtered'
            and candidate_count > 0
          order by draw_no desc
          limit 1",
        false
    );

    $drawNo = isset($run['draw_no']) ? (int) $run['draw_no'] : 0;
    if ($drawNo < 1) {
        return array(
            'success' => false,
            'status' => 'draw_missing',
            'error' => '발송할 필터 회차를 찾을 수 없습니다.',
        );
    }

    $smsConfig = lottoSmsGetConfig();
    $sender = isset($smsConfig['sender_phone'])
        ? lottoSmsNormalizePhone($smsConfig['sender_phone'])
        : '';

    if (strlen($sender) < 8 || strlen($sender) > 15) {
        return array(
            'success' => false,
            'status' => 'invalid_sender',
            'error' => '설정관리의 문자 발신번호를 확인해주세요.',
        );
    }

    $dayColumns = array(
        1 => 'num_mon',
        2 => 'num_tue',
        3 => 'num_wed',
        4 => 'num_thur',
        5 => 'num_fri',
    );
    $todayColumn = $dayColumns[$weekDay];
    $today = $now->format('Y-m-d');
    $todaySql = sql_real_escape_string($today);

    $result = sql_query(
        "select
            e.mb_id,
            e.num_mon,
            e.num_tue,
            e.num_wed,
            e.num_thur,
            e.num_fri,
            m.mb_type,
            m.mb_hp,
            m.mb_leave_date
         from g5_member_etc e
         inner join g5_member m on m.mb_id = e.mb_id
         where e.start_date <= '{$todaySql}'
           and e.end_date >= '{$todaySql}'
           and e.{$todayColumn} > 0
         order by e.mb_id asc",
        false
    );

    if ($result === false) {
        return array(
            'success' => false,
            'status' => 'member_query_failed',
            'error' => '조합 문자 대상 회원을 조회하지 못했습니다.',
        );
    }

    $paidTypes = fnGetTypePre();
    $queuedCount = 0;
    $skippedCount = 0;
    $failedCount = 0;
    $errors = array();

    while ($memberRow = sql_fetch_array($result)) {
        $mbId = trim((string) $memberRow['mb_id']);
        $mbType = trim((string) $memberRow['mb_type']);
        $receiver = lottoSmsNormalizePhone($memberRow['mb_hp']);

        if (!in_array($mbType, $paidTypes, true)) {
            $skippedCount++;
            continue;
        }

        if (trim((string) $memberRow['mb_leave_date']) !== '') {
            $skippedCount++;
            continue;
        }

        if (strlen($receiver) < 10 || strlen($receiver) > 15) {
            $failedCount++;
            $errors[] = $mbId . ': 휴대폰번호 오류';
            continue;
        }

        $slice = lottoCombinationSmsGetDaySlice($memberRow, $weekDay);
        $offset = (int) $slice['offset'];
        $count = (int) $slice['count'];

        if ($count < 1) {
            $skippedCount++;
            continue;
        }

        $mbIdSql = sql_real_escape_string($mbId);
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
             limit {$offset}, {$count}",
            false
        );

        if ($combinationResult === false) {
            $failedCount++;
            $errors[] = $mbId . ': 배분번호 조회 실패';
            continue;
        }

        while ($combination = sql_fetch_array($combinationResult)) {
            $rows[] = $combination;
        }

        if (count($rows) !== $count) {
            $failedCount++;
            $errors[] = $mbId . ': 오늘 발송할 배분번호 수량 불일치';
            continue;
        }

        $message = lottoSmsBuildCombinationMessage(
            $drawNo,
            '로또조합',
            $rows
        );

        $queued = lottoSmsQueueOShot(
            lottoCombinationSmsGroupId($drawNo, $weekDay, $mbId),
            $sender,
            $receiver,
            $message
        );

        if (empty($queued['success'])) {
            $failedCount++;
            $errors[] = $mbId . ': ' . (
                isset($queued['error'])
                    ? (string) $queued['error']
                    : 'OShot 큐 등록 실패'
            );
            continue;
        }

        if (isset($queued['status']) && $queued['status'] === 'already_queued') {
            $skippedCount++;
        } else {
            $queuedCount++;
        }
    }

    return array(
        'success' => $failedCount === 0,
        'status' => $failedCount === 0 ? 'completed' : 'partial_failed',
        'draw_no' => $drawNo,
        'weekday' => $weekDay,
        'queued_count' => $queuedCount,
        'skipped_count' => $skippedCount,
        'failed_count' => $failedCount,
        'errors' => $errors,
    );
}
