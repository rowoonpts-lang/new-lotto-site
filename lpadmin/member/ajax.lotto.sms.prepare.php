<?php

include_once("_common.php");

header('Content-Type: application/json; charset=utf-8');

$loginMbId = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$loginLevel = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if (!lottoIsStaffLevel($loginLevel)) {
    echo json_encode(array(
        'success' => false,
        'message' => '접근 권한이 없습니다.',
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if (
    !isset($_SERVER['REQUEST_METHOD'])
    || $_SERVER['REQUEST_METHOD'] !== 'POST'
) {
    echo json_encode(array(
        'success' => false,
        'message' => '잘못된 요청입니다.',
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

$batch = isset($_POST['batch'])
    ? trim((string) $_POST['batch'])
    : '';

$sendType = isset($_POST['send_type'])
    ? trim((string) $_POST['send_type'])
    : '';

$smsContent = isset($_POST['sms_content'])
    ? (string) $_POST['sms_content']
    : '';

if ($batch === '') {
    echo json_encode(array(
        'success' => false,
        'message' => '조합 정보가 없습니다.',
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if (!in_array($sendType, array('manual', 'resend'), true)) {
    echo json_encode(array(
        'success' => false,
        'message' => '문자 발송 유형이 올바르지 않습니다.',
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if (trim($smsContent) === '') {
    echo json_encode(array(
        'success' => false,
        'message' => '문자 내용을 입력해주세요.',
    ), JSON_UNESCAPED_UNICODE);
    exit;
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
     inner join g5_member b
        on b.mb_id = a.mb_id
     where a.distribution_batch = '{$batchSql}'
     order by a.distribution_seq asc, a.lmc_id asc
     limit 1",
    false
);

if (empty($target['mb_id'])) {
    echo json_encode(array(
        'success' => false,
        'message' => '발송할 조합을 찾을 수 없습니다.',
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

$targetMbId = trim((string) $target['mb_id']);

if (!lottoCanViewMember(
    $loginMbId,
    $loginLevel,
    $targetMbId
)) {
    echo json_encode(array(
        'success' => false,
        'message' => '조회 권한이 없습니다.',
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

$paidMemberTypes = fnGetTypePre();
$currentMemberType = trim((string) $target['mb_type']);

if (!in_array(
    $currentMemberType,
    $paidMemberTypes,
    true
)) {
    echo json_encode(array(
        'success' => false,
        'message' => '유료회원만 조합 문자를 발송할 수 있습니다.',
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if (trim((string) $target['mb_leave_date']) !== '') {
    echo json_encode(array(
        'success' => false,
        'message' => '탈퇴 회원에게는 조합 문자를 발송할 수 없습니다.',
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if (trim((string) $target['mb_hp']) === '') {
    echo json_encode(array(
        'success' => false,
        'message' => '회원의 휴대폰번호가 없습니다.',
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if (
    $sendType === 'manual'
    && (string) $target['distribution_type'] !== 'manual'
) {
    echo json_encode(array(
        'success' => false,
        'message' => '추가발송 조합이 아닙니다.',
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

/*
 * SMS 업체 API 연결 전 개발 단계.
 *
 * 여기서는 발송 직전 서버 검증까지만 수행한다.
 * fnSendOneshot() 호출 및 DB 변경은 하지 않는다.
 */
echo json_encode(array(
    'success' => true,
    'message' => '문자 발송 전 서버 검증이 완료되었습니다.',
), JSON_UNESCAPED_UNICODE);
exit;
