<?php

function lottoSmsGetConfig()
{
    $row = sql_fetch(
        "select
            sender_phone,
            combination_header,
            combination_footer,
            winner_header,
            winner_footer
         from l_sms_config
         where lsc_id = 1
         limit 1",
        false
    );

    if (!isset($row['sender_phone'])) {
        return array(
            'sender_phone' => '',
            'combination_header' => '',
            'combination_footer' => '',
            'winner_header' => '',
            'winner_footer' => '',
        );
    }

    return array(
        'sender_phone' => lottoSmsNormalizePhone(
            isset($row['sender_phone'])
                ? $row['sender_phone']
                : ''
        ),
        'combination_header' => isset($row['combination_header'])
            ? trim((string) $row['combination_header'])
            : '',
        'combination_footer' => isset($row['combination_footer'])
            ? trim((string) $row['combination_footer'])
            : '',
        'winner_header' => isset($row['winner_header'])
            ? trim((string) $row['winner_header'])
            : '',
        'winner_footer' => isset($row['winner_footer'])
            ? trim((string) $row['winner_footer'])
            : '',
    );
}

function lottoSmsJoinMessageParts($parts)
{
    $result = array();

    foreach ($parts as $part) {
        $part = trim((string) $part);

        if ($part !== '') {
            $result[] = $part;
        }
    }

    return implode("\n\n", $result);
}

function lottoSmsBuildCombinationMessage($drawNo, $title, $rows)
{
    $body = (int) $drawNo . '회 ' . trim((string) $title) . "\n";
    $total = count($rows);

    foreach ($rows as $index => $row) {
        $body .= ($index + 1) . '. ';
        $body .= (int) $row['num1'] . ',';
        $body .= (int) $row['num2'] . ',';
        $body .= (int) $row['num3'] . ',';
        $body .= (int) $row['num4'] . ',';
        $body .= (int) $row['num5'] . ',';
        $body .= (int) $row['num6'];

        if ($index < $total - 1) {
            $body .= "\n";
        }
    }

    $config = lottoSmsGetConfig();

    return lottoSmsJoinMessageParts(array(
        $config['combination_header'],
        $body,
        $config['combination_footer'],
    ));
}

function lottoSmsNormalizePhone($phone)
{
    return preg_replace('/[^0-9]/', '', (string) $phone);
}

function lottoSmsGetMessageBytes($message)
{
    $message = (string) $message;
    $chars = preg_split('//u', $message, -1, PREG_SPLIT_NO_EMPTY);

    if ($chars === false) {
        return strlen($message);
    }

    $bytes = 0;

    foreach ($chars as $char) {
        $bytes += strlen($char) === 1 ? 1 : 2;
    }

    return $bytes;
}

function lottoSmsBuildWinnerMessage($drawNo, $rankCounts)
{
    $drawNo = (int) $drawNo;
    $body = $drawNo . '회 결과';

    for ($rank = 1; $rank <= 5; $rank++) {
        $key = 'rank' . $rank . '_count';
        $count = isset($rankCounts[$key])
            ? max(0, (int) $rankCounts[$key])
            : 0;

        if ($count > 0) {
            $body .= "\n" . $rank . '등 ' . $count . '개';
        }
    }

    $config = lottoSmsGetConfig();

    return lottoSmsJoinMessageParts(array(
        $config['winner_header'],
        $body,
        $config['winner_footer'],
    ));
}

function lottoSmsBuildWinnerGroupId($drawNo, $mbId)
{
    return 'LW'
        . (int) $drawNo
        . substr(sha1((string) $mbId), 0, 12);
}

function lottoSmsQueueOShot(
    $groupId,
    $sender,
    $receiver,
    $message,
    $subject = ''
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

    if (isset($existing['MsgID']) && (int) $existing['MsgID'] > 0) {
        return array(
            'success' => true,
            'status' => 'already_queued',
            'msg_id' => (int) $existing['MsgID'],
        );
    }

    $sendType = lottoSmsGetMessageBytes($message) > 90
        ? 'LMS'
        : 'SMS';

    $senderSql = sql_real_escape_string($sender);
    $messageSql = sql_real_escape_string($message);
    $subjectSql = sql_real_escape_string($subject);
    $sendTypeSql = sql_real_escape_string($sendType);

    $insert = sql_query(
        "insert into OShotMSG
         set
            MsgGroupID = '{$groupIdSql}',
            SendType = '{$sendTypeSql}',
            Sender = '{$senderSql}',
            Receiver = '{$receiverSql}',
            Subject = '{$subjectSql}',
            Msg = '{$messageSql}',
            ReserveDT = null,
            SendResult = 0",
        false
    );

    if ($insert === false) {
        return array(
            'success' => false,
            'status' => 'queue_failed',
            'error' => 'OShot 문자 큐 등록에 실패했습니다.',
        );
    }

    return array(
        'success' => true,
        'status' => 'queued',
        'msg_id' => (int) sql_insert_id(),
        'send_type' => $sendType,
    );
}

function lottoSmsQueuePendingWinners($drawNo, $sender)
{
    $drawNo = (int) $drawNo;
    $sender = lottoSmsNormalizePhone($sender);

    if ($drawNo < 1) {
        return array(
            'success' => false,
            'status' => 'invalid_draw',
            'error' => '회차가 올바르지 않습니다.',
        );
    }

    $lockName = 'lotto_winner_sms_' . $drawNo;

    $lockRow = sql_fetch(
        "select get_lock('"
        . sql_real_escape_string($lockName)
        . "', 0) as acquired",
        false
    );

    if (
        !isset($lockRow['acquired'])
        || (int) $lockRow['acquired'] !== 1
    ) {
        return array(
            'success' => false,
            'status' => 'already_running',
            'error' => '같은 회차의 결과 문자 작업이 실행 중입니다.',
        );
    }

    $queuedCount = 0;
    $skippedCount = 0;
    $failedCount = 0;

    try {
        $result = sql_query(
            "select
                d.mb_id,
                d.rank1_count,
                d.rank2_count,
                d.rank3_count,
                d.rank4_count,
                d.rank5_count,
                m.mb_hp
             from l_member_draw d
             inner join g5_member m
                on m.mb_id = d.mb_id
             where d.draw_no = '{$drawNo}'
               and d.winner_sms_required = 1
               and d.winner_sms_status = 'pending'
             order by d.lmd_id asc",
            false
        );

        if ($result === false) {
            throw new RuntimeException(
                '결과 문자 대상 조회에 실패했습니다.'
            );
        }

        while ($row = sql_fetch_array($result)) {
            $mbId = (string) $row['mb_id'];
            $receiver = lottoSmsNormalizePhone($row['mb_hp']);

            if (strlen($receiver) < 10 || strlen($receiver) > 15) {
                sql_query(
                    "update l_member_draw
                     set
                        winner_sms_status = 'failed',
                        winner_sms_error = '유효한 휴대폰 번호가 없습니다.'
                     where draw_no = '{$drawNo}'
                       and mb_id = '"
                    . sql_real_escape_string($mbId)
                    . "'",
                    false
                );

                $failedCount++;
                continue;
            }

            $groupId = lottoSmsBuildWinnerGroupId(
                $drawNo,
                $mbId
            );

            $message = lottoSmsBuildWinnerMessage(
                $drawNo,
                $row
            );

            $queued = lottoSmsQueueOShot(
                $groupId,
                $sender,
                $receiver,
                $message,
                '결과'
            );

            if (!$queued['success']) {
                sql_query(
                    "update l_member_draw
                     set
                        winner_sms_status = 'failed',
                        winner_sms_error = '"
                    . sql_real_escape_string(
                        isset($queued['error'])
                            ? $queued['error']
                            : '문자 큐 등록 실패'
                    )
                    . "'
                     where draw_no = '{$drawNo}'
                       and mb_id = '"
                    . sql_real_escape_string($mbId)
                    . "'",
                    false
                );

                $failedCount++;
                continue;
            }

            $msgId = isset($queued['msg_id'])
                ? (int) $queued['msg_id']
                : 0;

            $updated = sql_query(
                "update l_member_draw
                 set
                    winner_sms_status = 'queued',
                    winner_sms_result_code = '"
                . sql_real_escape_string('queue:' . $msgId)
                . "',
                    winner_sms_error = null
                 where draw_no = '{$drawNo}'
                   and mb_id = '"
                . sql_real_escape_string($mbId)
                . "'
                   and winner_sms_status = 'pending'",
                false
            );

            if ($updated === false) {
                throw new RuntimeException(
                    '결과 문자 큐 상태 저장에 실패했습니다.'
                );
            }

            if ($queued['status'] === 'already_queued') {
                $skippedCount++;
            } else {
                $queuedCount++;
            }
        }

        return array(
            'success' => true,
            'status' => 'completed',
            'draw_no' => $drawNo,
            'queued_count' => $queuedCount,
            'skipped_count' => $skippedCount,
            'failed_count' => $failedCount,
        );
    } finally {
        sql_fetch(
            "select release_lock('"
            . sql_real_escape_string($lockName)
            . "') as released",
            false
        );
    }
}

function lottoSmsSyncWinnerResults($drawNo)
{
    $drawNo = (int) $drawNo;

    if ($drawNo < 1) {
        return array(
            'success' => false,
            'status' => 'invalid_draw',
            'error' => '회차가 올바르지 않습니다.',
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

    $result = sql_query(
        "select
            lmd_id,
            mb_id,
            winner_sms_result_code
         from l_member_draw
         where draw_no = '{$drawNo}'
           and winner_sms_required = 1
           and winner_sms_status = 'queued'
         order by lmd_id asc",
        false
    );

    if ($result === false) {
        return array(
            'success' => false,
            'status' => 'member_query_failed',
            'error' => '문자 결과 확인 대상을 조회하지 못했습니다.',
        );
    }

    $waitingCount = 0;
    $sentCount = 0;
    $failedCount = 0;
    $missingCount = 0;

    while ($row = sql_fetch_array($result)) {
        $resultCode = isset($row['winner_sms_result_code'])
            ? trim((string) $row['winner_sms_result_code'])
            : '';

        if (strpos($resultCode, 'queue:') !== 0) {
            $missingCount++;
            continue;
        }

        $msgId = (int) substr($resultCode, 6);

        if ($msgId < 1) {
            $missingCount++;
            continue;
        }

        $oshot = sql_fetch(
            "select
                MsgID,
                SendResult,
                SendDT,
                ResultMsg
             from OShotMSG
             where MsgID = '{$msgId}'
             limit 1",
            false
        );

        if (
            !isset($oshot['MsgID'])
            || (int) $oshot['MsgID'] !== $msgId
        ) {
            $missingCount++;
            continue;
        }

        $sendResult = isset($oshot['SendResult'])
            ? (int) $oshot['SendResult']
            : 0;

        /*
         * OShot 매뉴얼:
         * 0     : 초기 입력 상태
         * 1     : 전송요청 완료 / 결과수신대기
         * 95~99 : 일시적인 통신 오류이며 최종결과가 아님
         */
        if (
            $sendResult === 0
            || $sendResult === 1
            || ($sendResult >= 95 && $sendResult <= 99)
        ) {
            $waitingCount++;
            continue;
        }

        $lmdId = (int) $row['lmd_id'];

        if ($sendResult === 6) {
            $sendDt = isset($oshot['SendDT'])
                ? trim((string) $oshot['SendDT'])
                : '';

            $sendDtSql = $sendDt !== ''
                ? "'" . sql_real_escape_string($sendDt) . "'"
                : 'null';

            $updated = sql_query(
                "update l_member_draw
                 set
                    winner_sms_status = 'sent',
                    winner_sms_result_code = 'oshot:6',
                    winner_sms_sent_at = {$sendDtSql},
                    winner_sms_error = null
                 where lmd_id = '{$lmdId}'
                   and winner_sms_status = 'queued'",
                false
            );

            if ($updated === false) {
                return array(
                    'success' => false,
                    'status' => 'sent_update_failed',
                    'error' => '문자 성공 상태 저장에 실패했습니다.',
                );
            }

            $sentCount++;
            continue;
        }

        $resultMessage = isset($oshot['ResultMsg'])
            ? trim((string) $oshot['ResultMsg'])
            : '';

        if ($resultMessage === '') {
            $resultMessage = 'OShot SendResult ' . $sendResult;
        }

        $updated = sql_query(
            "update l_member_draw
             set
                winner_sms_status = 'failed',
                winner_sms_result_code = '"
            . sql_real_escape_string('oshot:' . $sendResult)
            . "',
                winner_sms_error = '"
            . sql_real_escape_string($resultMessage)
            . "'
             where lmd_id = '{$lmdId}'
               and winner_sms_status = 'queued'",
            false
        );

        if ($updated === false) {
            return array(
                'success' => false,
                'status' => 'failed_update_failed',
                'error' => '문자 실패 상태 저장에 실패했습니다.',
            );
        }

        $failedCount++;
    }

    $remainingRow = sql_fetch(
        "select count(*) as cnt
         from l_member_draw
         where draw_no = '{$drawNo}'
           and winner_sms_required = 1
           and winner_sms_status in ('pending', 'queued')",
        false
    );

    $remainingCount = isset($remainingRow['cnt'])
        ? (int) $remainingRow['cnt']
        : 0;

    if ($remainingCount === 0) {
        sql_query(
            "update l_result_job
             set winner_sms_completed_at =
                coalesce(winner_sms_completed_at, now())
             where draw_no = '{$drawNo}'",
            false
        );
    }

    return array(
        'success' => true,
        'status' => 'completed',
        'draw_no' => $drawNo,
        'waiting_count' => $waitingCount,
        'sent_count' => $sentCount,
        'failed_count' => $failedCount,
        'missing_count' => $missingCount,
        'remaining_count' => $remainingCount,
    );
}
