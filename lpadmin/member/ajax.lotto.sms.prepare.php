<?php

include_once("_common.php");
include_once G5_PATH . "/include/lotto_sms.lib.php";

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

$receiver = lottoSmsNormalizePhone($target['mb_hp']);
if (strlen($receiver) < 10 || strlen($receiver) > 15) {
    lottoSmsPrepareResponse(false, '회원의 휴대폰번호를 확인해주세요.');
}

$smsConfig = lottoSmsGetConfig();
$sender = isset($smsConfig['sender_phone'])
    ? lottoSmsNormalizePhone($smsConfig['sender_phone'])
    : '';

if (strlen($sender) < 8 || strlen($sender) > 15) {
    lottoSmsPrepareResponse(false, '설정관리의 문자 발신번호를 확인해주세요.');
}

$queued = lottoSmsQueueOShot(
    $groupId,
    $sender,
    $receiver,
    $smsContent,
    '추천번호'
);

if (empty($queued['success'])) {
    lottoSmsPrepareResponse(
        false,
        isset($queued['error'])
            ? (string) $queued['error']
            : 'OShot 문자 큐 등록에 실패했습니다.'
    );
}

$status = isset($queued['status']) ? (string) $queued['status'] : '';

if ($status === 'already_queued') {
    lottoSmsPrepareResponse(true, '이미 OShot 문자 큐에 등록되어 있습니다.');
}

lottoSmsPrepareResponse(true, 'OShot 문자 큐에 등록했습니다.');
