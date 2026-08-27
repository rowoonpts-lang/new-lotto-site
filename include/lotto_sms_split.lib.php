<?php

if (!defined('_GNUBOARD_')) {
    exit;
}

function lottoSmsSplitLmsMessage($message, $subject = '', $maxBytes = 2000)
{
    $message = trim((string) $message);
    $subject = trim((string) $subject);
    $maxBytes = (int) $maxBytes;

    if ($message === '') {
        return array(
            'success' => false,
            'status' => 'empty_message',
            'error' => '문자 내용이 없습니다.',
        );
    }

    if ($maxBytes < 100) {
        return array(
            'success' => false,
            'status' => 'invalid_max_bytes',
            'error' => 'LMS 최대 길이 설정이 올바르지 않습니다.',
        );
    }

    if (lottoSmsGetMessageBytes($message) <= $maxBytes) {
        return array(
            'success' => true,
            'status' => 'single',
            'messages' => array($message),
        );
    }

    /*
     * 분할 번호 문구가 추가될 공간을 확보한다.
     * 실제 분할 여부는 전체 메시지가 LMS 최대 길이를 초과할 때만 결정한다.
     */
    $contentLimit = $maxBytes - 40;
    $lines = preg_split('/\r\n|\r|\n/', $message);

    if ($lines === false) {
        return array(
            'success' => false,
            'status' => 'split_failed',
            'error' => '문자 내용을 분할하지 못했습니다.',
        );
    }

    $chunks = array();
    $current = '';

    foreach ($lines as $line) {
        $line = (string) $line;
        $candidate = $current === ''
            ? $line
            : $current . "\n" . $line;

        if (lottoSmsGetMessageBytes($candidate) <= $contentLimit) {
            $current = $candidate;
            continue;
        }

        if ($current !== '') {
            $chunks[] = $current;
            $current = '';
        }

        if (lottoSmsGetMessageBytes($line) > $contentLimit) {
            return array(
                'success' => false,
                'status' => 'line_too_long',
                'error' => '한 줄의 문자 내용이 LMS 최대 길이를 초과합니다.',
            );
        }

        $current = $line;
    }

    if ($current !== '') {
        $chunks[] = $current;
    }

    $total = count($chunks);

    if ($total < 2) {
        return array(
            'success' => false,
            'status' => 'split_failed',
            'error' => 'LMS 분할 결과를 만들지 못했습니다.',
        );
    }

    if ($total > 99) {
        return array(
            'success' => false,
            'status' => 'too_many_parts',
            'error' => '문자 분할 건수가 너무 많습니다.',
        );
    }

    $label = $subject !== '' ? $subject : '문자';
    $messages = array();

    foreach ($chunks as $index => $chunk) {
        $partNo = $index + 1;
        $prefix = '[' . $label . ' ' . $partNo . '/' . $total . ']';
        $partMessage = $prefix . "\n" . $chunk;

        if (lottoSmsGetMessageBytes($partMessage) > $maxBytes) {
            return array(
                'success' => false,
                'status' => 'part_too_long',
                'error' => '분할된 LMS 내용이 최대 길이를 초과합니다.',
            );
        }

        $messages[] = $partMessage;
    }

    return array(
        'success' => true,
        'status' => 'split',
        'messages' => $messages,
    );
}

function lottoSmsBuildSplitGroupId($groupId, $partNo)
{
    $groupId = trim((string) $groupId);
    $partNo = (int) $partNo;

    return substr($groupId, 0, 10)
        . substr(sha1($groupId), 0, 5)
        . 'P'
        . str_pad((string) $partNo, 2, '0', STR_PAD_LEFT);
}

function lottoSmsQueueOShotWithSplit(
    $groupId,
    $sender,
    $receiver,
    $message,
    $subject = '',
    array $history = array()
) {
    $split = lottoSmsSplitLmsMessage($message, $subject, 2000);

    if (empty($split['success'])) {
        return $split;
    }

    $messages = isset($split['messages']) && is_array($split['messages'])
        ? $split['messages']
        : array();
    $partCount = count($messages);

    if ($partCount < 1) {
        return array(
            'success' => false,
            'status' => 'empty_parts',
            'error' => '발송할 문자 내용이 없습니다.',
        );
    }

    if ($partCount === 1) {
        $queued = lottoSmsQueueOShot(
            $groupId,
            $sender,
            $receiver,
            $messages[0],
            $subject,
            $history
        );
        $queued['part_count'] = 1;
        $queued['msg_ids'] = isset($queued['msg_id'])
            ? array((int) $queued['msg_id'])
            : array();

        return $queued;
    }

    $msgIds = array();
    $queuedPartCount = 0;
    $skippedPartCount = 0;

    foreach ($messages as $index => $partMessage) {
        $partNo = $index + 1;
        $partGroupId = lottoSmsBuildSplitGroupId($groupId, $partNo);
        $partSubject = trim((string) $subject);

        if ($partSubject !== '') {
            $partSubject .= ' ' . $partNo . '/' . $partCount;
        }

        $queued = lottoSmsQueueOShot(
            $partGroupId,
            $sender,
            $receiver,
            $partMessage,
            $partSubject,
            $history
        );

        if (empty($queued['success'])) {
            return array(
                'success' => false,
                'status' => 'partial_failed',
                'error' => isset($queued['error'])
                    ? (string) $queued['error']
                    : '분할 문자 큐 등록에 실패했습니다.',
                'part_count' => $partCount,
                'queued_part_count' => $queuedPartCount,
                'skipped_part_count' => $skippedPartCount,
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
            $skippedPartCount++;
        } else {
            $queuedPartCount++;
        }
    }

    return array(
        'success' => true,
        'status' => $queuedPartCount === 0
            ? 'already_queued'
            : 'queued',
        'msg_id' => !empty($msgIds) ? (int) $msgIds[0] : 0,
        'msg_ids' => $msgIds,
        'part_count' => $partCount,
        'queued_part_count' => $queuedPartCount,
        'skipped_part_count' => $skippedPartCount,
        'send_type' => 'LMS',
    );
}
