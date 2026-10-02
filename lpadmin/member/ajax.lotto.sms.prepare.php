<?php

include_once("_common.php");
include_once G5_PATH . "/include/lotto_sms.lib.php";
include_once G5_PATH . "/include/lotto_sms_split.lib.php";
include_once G5_PATH . "/include/lotto_push.lib.php";

header('Content-Type: application/json; charset=utf-8');

function lottoSmsPrepareResponse($success, $message)
{
    echo json_encode(
        array(
            'success' => (bool) $success,
            'message' => (string) $message,
        ),
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

$loginMbId = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$loginLevel = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if (!lottoIsStaffLevel($loginLevel)) {
    lottoSmsPrepareResponse(false, '접근 권한이 없습니다.');
}

if (
    !isset($_SERVER['REQUEST_METHOD'])
    || $_SERVER['REQUEST_METHOD'] !== 'POST'
) {
    lottoSmsPrepareResponse(false, '잘못된 요청입니다.');
}

$sendType = isset($_POST['send_type'])
    ? trim((string) $_POST['send_type'])
    : '';

$smsContent = isset($_POST['sms_content'])
    ? trim((string) $_POST['sms_content'])
    : '';

if (!in_array($sendType, array('manual', 'resend'), true)) {
    lottoSmsPrepareResponse(false, '문자 발송 유형이 올바르지 않습니다.');
}

if ($smsContent === '') {
    lottoSmsPrepareResponse(false, '문자 내용을 입력해주세요.');
}

$target = array();
$targetMbId = '';
$groupId = '';

if ($sendType === 'manual') {
    $batch = isset($_POST['batch'])
        ? trim((string) $_POST['batch'])
        : '';

    if ($batch === '') {
        lottoSmsPrepareResponse(false, '추가발송 조합 정보가 없습니다.');
    }

    $batchSql = sql_real_escape_string($batch);

    $target = sql_fetch(
        "select
            a.mb_id,
            a.draw_no,
            a.distribution_type,
            b.mb_name,
            b.mb_hp,
            b.mb_type,
            b.mb_leave_date
         from l_member_combination a
         inner join g5_member b on b.mb_id = a.mb_id
         where a.distribution_batch = '{$batchSql}'
         order by a.distribution_seq asc, a.lmc_id asc
         limit 1",
        false
    );

    if (empty($target['mb_id'])) {
        lottoSmsPrepareResponse(false, '발송할 추가조합을 찾을 수 없습니다.');
    }

    if ((string) $target['distribution_type'] !== 'manual') {
        lottoSmsPrepareResponse(false, '추가발송 조합이 아닙니다.');
    }

    $targetMbId = trim((string) $target['mb_id']);
    $drawNo = isset($target['draw_no'])
        ? (int) $target['draw_no']
        : 0;

    $manualCountRow = sql_fetch(
        "select count(*) as cnt
           from l_member_combination
          where distribution_batch = '{$batchSql}'
            and distribution_type = 'manual'",
        false
    );

    $manualCombinationCount = isset($manualCountRow['cnt'])
        ? max(0, (int) $manualCountRow['cnt'])
        : 0;

    if ($drawNo < 1 || $manualCombinationCount < 1) {
        lottoSmsPrepareResponse(
            false,
            '추가발송 조합 회차 또는 수량을 확인할 수 없습니다.'
        );
    }

    $groupId = 'LM' . substr(sha1($batch), 0, 16);
} else {
    $targetMbId = isset($_POST['mb_id'])
        ? trim((string) $_POST['mb_id'])
        : '';

    $drawNo = isset($_POST['draw_no'])
        ? (int) $_POST['draw_no']
        : 0;

    if ($targetMbId === '' || $drawNo < 1) {
        lottoSmsPrepareResponse(false, '재발송할 회원과 회차를 확인해주세요.');
    }

    $targetMbIdSql = sql_real_escape_string($targetMbId);

    $target = sql_fetch(
        "select
            a.mb_id,
            b.mb_name,
            b.mb_hp,
            b.mb_type,
            b.mb_leave_date
         from l_member_combination a
         inner join g5_member b on b.mb_id = a.mb_id
         where a.mb_id = '{$targetMbIdSql}'
           and a.draw_no = '{$drawNo}'
         order by a.lmc_id asc
         limit 1",
        false
    );

    if (empty($target['mb_id'])) {
        lottoSmsPrepareResponse(false, '재발송할 조합을 찾을 수 없습니다.');
    }

    /*
     * 재발송은 사용자가 명시적으로 다시 보내는 기능이므로
     * 매 요청마다 새로운 OShot 그룹 ID를 만든다.
     */
    $groupId = 'LR'
        . (int) $drawNo
        . substr(
            sha1($targetMbId . microtime(true) . mt_rand()),
            0,
            10
        );
}

if (!lottoCanViewMember($loginMbId, $loginLevel, $targetMbId)) {
    lottoSmsPrepareResponse(false, '조회 권한이 없습니다.');
}

$paidMemberTypes = fnGetTypePre();
$currentMemberType = trim((string) $target['mb_type']);

if (!in_array($currentMemberType, $paidMemberTypes, true)) {
    lottoSmsPrepareResponse(false, '유료회원만 조합 문자를 발송할 수 있습니다.');
}

if (trim((string) $target['mb_leave_date']) !== '') {
    lottoSmsPrepareResponse(false, '탈퇴 회원에게는 조합 문자를 발송할 수 없습니다.');
}

/*
 * 문자와 앱 Push는 서로 독립적으로 처리한다.
 *
 * 문자번호/OShot 문제가 있더라도 앱 알림이 가능한 회원에게는
 * Push를 먼저 별도로 시도한다.
 */
$pushCategory = $sendType === 'manual'
    ? 'combination_manual'
    : 'combination_resend';

if ($sendType === 'manual') {
    $pushBody =
        $drawNo
        . '회 추가 추천번호 '
        . $manualCombinationCount
        . '조합이 도착했습니다. 눌러서 확인해주세요.';
} else {
    $pushBody =
        $drawNo
        . '회 추천번호를 다시 보냈습니다. 눌러서 확인해주세요.';
}

$pushResult = lottoPushSendToMember(
    $targetMbId,
    'LottoGPT ' . $drawNo . '회 추천번호',
    $pushBody,
    '/app/?turn=' . $drawNo,
    $pushCategory,
    $drawNo,
    $groupId
);

$pushStatus = isset($pushResult['status'])
    ? trim((string) $pushResult['status'])
    : '';

$pushSentCount = isset($pushResult['sent_count'])
    ? max(0, (int) $pushResult['sent_count'])
    : 0;

$pushFailedCount = isset($pushResult['failed_count'])
    ? max(0, (int) $pushResult['failed_count'])
    : 0;

if (!empty($pushResult['success'])) {
    $pushNote = ' 앱 알림도 발송했습니다.';
} elseif (
    $pushSentCount > 0
    && $pushFailedCount > 0
) {
    $pushNote = ' 앱 알림은 일부 기기에만 발송되었습니다.';
} elseif ($pushStatus === 'no_subscription') {
    $pushNote = ' 앱 알림이 설정된 기기가 없어 Push는 건너뛰었습니다.';
} else {
    $pushNote = ' 앱 알림 발송에는 실패했습니다.';
}

$receiver = lottoSmsNormalizePhone($target['mb_hp']);

if (strlen($receiver) < 10 || strlen($receiver) > 15) {
    lottoSmsPrepareResponse(
        false,
        '회원의 휴대폰번호를 확인해주세요.' . $pushNote
    );
}

$smsConfig = lottoSmsGetConfig();
$sender = isset($smsConfig['sender_phone'])
    ? lottoSmsNormalizePhone($smsConfig['sender_phone'])
    : '';

if (strlen($sender) < 8 || strlen($sender) > 15) {
    lottoSmsPrepareResponse(
        false,
        '설정관리의 문자 발신번호를 확인해주세요.' . $pushNote
    );
}

$queued = lottoSmsQueueOShotWithSplit(
    $groupId,
    $sender,
    $receiver,
    $smsContent,
    '추천번호',
    array(
        'mb_id' => $targetMbId,
        'sender_mb_id' => $loginMbId,
        'send_category' => 'combination',
        'usage_group_id' => $sendType === 'manual'
            ? $groupId
            : '',
        'draw_no' => $sendType === 'manual'
            ? $drawNo
            : 0,
        'combination_count' => $sendType === 'manual'
            ? $manualCombinationCount
            : 0,
    )
);

if (empty($queued['success'])) {
    $smsError = isset($queued['error'])
        ? (string) $queued['error']
        : 'OShot 문자 큐 등록에 실패했습니다.';

    lottoSmsPrepareResponse(
        false,
        $smsError . $pushNote
    );
}

$status = isset($queued['status']) ? (string) $queued['status'] : '';
$partCount = isset($queued['part_count'])
    ? max(1, (int) $queued['part_count'])
    : 1;

if ($status === 'already_queued') {
    lottoSmsPrepareResponse(
        true,
        '이미 OShot 문자 큐에 등록되어 있습니다.' . $pushNote
    );
}

if ($partCount > 1) {
    lottoSmsPrepareResponse(
        true,
        'LMS 최대 길이를 초과하여 '
        . $partCount
        . '통으로 나누어 OShot 문자 큐에 등록했습니다.'
        . $pushNote
    );
}

lottoSmsPrepareResponse(
    true,
    'OShot 문자 큐에 등록했습니다.' . $pushNote
);
