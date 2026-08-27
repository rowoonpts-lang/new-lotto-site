<?php

include_once("_common.php");
include_once G5_PATH . "/include/lotto_sms.lib.php";
include_once G5_PATH . "/include/lotto_sms_split.lib.php";

if (
    !isset($_SERVER['REQUEST_METHOD'])
    || strtoupper((string) $_SERVER['REQUEST_METHOD']) !== 'POST'
) {
    alert('잘못된 요청입니다.');
    exit;
}

$loginMbId = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$loginLevel = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if (
    $loginMbId === ''
    || $loginLevel < LOTTO_ROLE_STAFF1
) {
    alert('문자 발송 권한이 없습니다.');
    exit;
}

if (!lottoMemberTokenCheck()) {
    alert('요청 확인에 실패했습니다. 다시 시도해주세요.');
    exit;
}

$encodedMbId = isset($_POST['mb_id'])
    ? trim((string) $_POST['mb_id'])
    : '';

$targetMbId = base64_decode($encodedMbId, true);

if ($targetMbId === false || trim((string) $targetMbId) === '') {
    alert('회원 정보가 올바르지 않습니다.');
    exit;
}

$targetMbId = trim((string) $targetMbId);

if (!lottoCanViewMember($loginMbId, $loginLevel, $targetMbId)) {
    alert('접근 권한이 없는 회원입니다.');
    exit;
}

$safeMbId = sql_real_escape_string($targetMbId);

$target = sql_fetch(
    "select mb_id, mb_hp, mb_leave_date
       from g5_member
      where mb_id = '{$safeMbId}'
      limit 1",
    false
);

if (empty($target['mb_id'])) {
    alert('회원을 찾을 수 없습니다.');
    exit;
}

if (trim((string) $target['mb_leave_date']) !== '') {
    alert('탈퇴 회원에게는 문자를 발송할 수 없습니다.');
    exit;
}

$message = isset($_POST['sms_content'])
    ? trim((string) $_POST['sms_content'])
    : '';

if ($message === '') {
    alert('보내실 상담내용을 입력하세요.');
    exit;
}

$receiver = lottoSmsNormalizePhone(
    isset($target['mb_hp'])
        ? $target['mb_hp']
        : ''
);

$smsConfig = lottoSmsGetConfig();

$sender = isset($smsConfig['sender_phone'])
    ? lottoSmsNormalizePhone($smsConfig['sender_phone'])
    : '';

if (
    $sender === ''
    && isset($config['cf_oneshot_tel'])
) {
    $sender = lottoSmsNormalizePhone($config['cf_oneshot_tel']);
}

/*
 * 기존 080 광고문구 설정은 현재 비어 있다.
 * 향후 값이 설정된 경우에는 기존 fnSendOneshot의 정확한
 * 광고문구 조합 형식을 확인한 뒤 별도로 연결한다.
 */
$use080 = isset($_POST['chk'])
    && (string) $_POST['chk'] === '1';

$cf080 = isset($config['cf_oneshot_080'])
    ? trim((string) $config['cf_oneshot_080'])
    : '';

if ($use080 && $cf080 !== '') {
    alert('080 광고문구 형식 확인이 필요하여 현재 발송할 수 없습니다.');
    exit;
}

$groupId = 'MC' . substr(
    sha1(
        $targetMbId
        . '|'
        . microtime(true)
        . '|'
        . mt_rand()
    ),
    0,
    18
);

$queued = lottoSmsQueueOShotWithSplit(
    $groupId,
    $sender,
    $receiver,
    $message,
    '상담문자',
    array(
        'mb_id' => $targetMbId,
        'sender_mb_id' => $loginMbId,
        'send_category' => 'consultation',
    )
);

if (empty($queued['success'])) {
    alert(
        isset($queued['error'])
            ? (string) $queued['error']
            : 'OShot 문자 큐 등록에 실패했습니다.'
    );
    exit;
}

$partCount = isset($queued['part_count'])
    ? max(1, (int) $queued['part_count'])
    : 1;

if ($partCount > 1) {
    alert(
        'LMS 최대 길이를 초과하여 '
        . $partCount
        . '통으로 나누어 OShot 문자 큐에 등록했습니다.'
    );
    exit;
}

alert('OShot 문자 큐에 등록했습니다.');
?>
