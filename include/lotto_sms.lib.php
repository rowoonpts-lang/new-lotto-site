<?php

function lottoSmsBuildCombinationMessage($drawNo, $title, $rows)
{
    $message = (int) $drawNo . '회 ' . trim((string) $title) . "\n";
    $total = count($rows);

    foreach ($rows as $index => $row) {
        $message .= ($index + 1) . '. ';
        $message .= (int) $row['num1'] . ',';
        $message .= (int) $row['num2'] . ',';
        $message .= (int) $row['num3'] . ',';
        $message .= (int) $row['num4'] . ',';
        $message .= (int) $row['num5'] . ',';
        $message .= (int) $row['num6'];

        if ($index < $total - 1) {
            $message .= "\n";
        }
    }

    return $message;
}
function lottoSmsNormalizePhone($phone)
{
    return preg_replace('/[^0-9]/', '', (string) $phone);
}

function lottoSmsBuildWinnerMessage($drawNo, $bestRank)
{
    $drawNo = (int) $drawNo;
    $bestRank = (int) $bestRank;

    return '[로또] '
        . $drawNo
        . '회 당첨 결과가 있습니다. '
        . '최고 '
        . $bestRank
        . '등. 사이트에서 확인해주세요.';
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

    $senderSql = sql_real_escape_string($sender);
    $messageSql = sql_real_escape_string($message);
    $subjectSql = sql_real_escape_string($subject);

    $insert = sql_query(
        "insert into OShotMSG
         set
            MsgGroupID = '{$groupIdSql}',
            SendType = 'SMS',
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
            'error' => '같은 회차의 당첨 문자 작업이 실행 중입니다.',
        );
    }

    $queuedCount = 0;
    $skippedCount = 0;
    $failedCount = 0;

    try {
        $result = sql_query(
            "select
                d.mb_id,
                d.best_rank,
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
                '당첨 문자 대상 조회에 실패했습니다.'
            );
        }

        while ($row = sql_fetch_array($result)) {
            $mbId = (string) $row['mb_id'];
            $bestRank = (int) $row['best_rank'];
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
                $bestRank
            );

            $queued = lottoSmsQueueOShot(
                $groupId,
                $sender,
                $receiver,
                $message
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
                    '당첨 문자 큐 상태 저장에 실패했습니다.'
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
