<?php

include_once("_common.php");
include_once G5_PATH . "/include/lotto_sms.lib.php";
include_once G5_PATH . "/include/lotto_sms_split.lib.php";

header('Content-Type: application/json; charset=utf-8');

function lottoLinkSmsResponse($success, $message)
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
    lottoLinkSmsResponse(false, '접근 권한이 없습니다.');
}

if (
    !isset($_SERVER['REQUEST_METHOD'])
    || $_SERVER['REQUEST_METHOD'] !== 'POST'
) {
    lottoLinkSmsResponse(false, '잘못된 요청입니다.');
}

$targetMbId = isset($_POST['mb_id'])
    ? trim((string) $_POST['mb_id'])
    : '';

if ($targetMbId === '') {
    lottoLinkSmsResponse(false, '회원을 확인할 수 없습니다.');
}

if (
    !isset($_POST['token'])
    || !lottoMemberTokenCheck((string) $_POST['token'])
) {
    lottoLinkSmsResponse(false, '요청 토큰이 올바르지 않습니다.');
}

if (!lottoCanViewMember($loginMbId, $loginLevel, $targetMbId)) {
    lottoLinkSmsResponse(false, '조회 권한이 없습니다.');
}

$targetMbIdSql = sql_real_escape_string($targetMbId);

$target = sql_fetch(
    "select mb_id,
            mb_name,
            mb_hp,
            mb_type,
            mb_leave_date
       from g5_member
      where mb_id = '{$targetMbIdSql}'
      limit 1",
    false
);

if (empty($target['mb_id'])) {
    lottoLinkSmsResponse(false, '회원을 찾을 수 없습니다.');
}

if (trim((string) $target['mb_leave_date']) !== '') {
    lottoLinkSmsResponse(false, '탈퇴 회원에게는 링크 문자를 발송할 수 없습니다.');
}

$combinationRow = sql_fetch(
    "select lmc_id
       from l_member_combination
      where mb_id = '{$targetMbIdSql}'
      limit 1",
    false
);

if (empty($combinationRow['lmc_id'])) {
    lottoLinkSmsResponse(false, '회원에게 배분된 조합이 없습니다.');
}

$receiver = lottoSmsNormalizePhone($target['mb_hp']);

if (strlen($receiver) < 10 || strlen($receiver) > 15) {
    lottoLinkSmsResponse(false, '회원의 휴대폰번호를 확인해주세요.');
}

$linkRow = sql_fetch(
    "select lsl_id,
            token_value,
            token_hash
       from l_lotto_share_link
      where mb_id = '{$targetMbIdSql}'
      limit 1",
    false
);

$plainToken = '';

if (empty($linkRow['lsl_id'])) {
    try {
        $plainToken = bin2hex(random_bytes(16));
    } catch (Exception $e) {
        lottoLinkSmsResponse(false, '링크 토큰 생성에 실패했습니다.');
    }

    $tokenValueSql = sql_real_escape_string($plainToken);
    $tokenHash = hash('sha256', $plainToken);
    $tokenHashSql = sql_real_escape_string($tokenHash);
    $createdBySql = sql_real_escape_string($loginMbId);

    sql_query(
        "insert into l_lotto_share_link (
            mb_id,
            token_value,
            token_hash,
            created_by,
            created_at
        ) values (
            '{$targetMbIdSql}',
            '{$tokenValueSql}',
            '{$tokenHashSql}',
            '{$createdBySql}',
            now()
        )"
    );
} else {
    $plainToken = isset($linkRow['token_value'])
        ? trim((string) $linkRow['token_value'])
        : '';

    if (!preg_match('/^[a-f0-9]{32}$/', $plainToken)) {
        lottoLinkSmsResponse(
            false,
            '회원의 고정 링크 정보를 확인할 수 없습니다.'
        );
    }
}

$baseUrl = trim((string) G5_URL);

if (preg_match('#^https?://#i', $baseUrl)) {
    $baseUrl = preg_replace(
        '#^http://#i',
        'https://',
        $baseUrl
    );
} else {
    $host = isset($_SERVER['HTTP_HOST'])
        ? trim((string) $_SERVER['HTTP_HOST'])
        : '';

    if (
        $host === ''
        || !preg_match(
            '/^[a-z0-9.-]+(?::[0-9]+)?$/i',
            $host
        )
    ) {
        lottoLinkSmsResponse(
            false,
            '사이트 주소를 확인할 수 없습니다.'
        );
    }

    $baseUrl = 'https://'
        . $host
        . '/'
        . ltrim($baseUrl, '/');
}

$linkUrl = rtrim($baseUrl, '/')
    . '/l.php?k='
    . rawurlencode($plainToken);

$message = "아래 주소는 회원님만의 전용 주소입니다.\n"
    . "타인과 공유하시면 안됩니다.\n"
    . $linkUrl;

$smsConfig = lottoSmsGetConfig();

$sender = isset($smsConfig['sender_phone'])
    ? lottoSmsNormalizePhone($smsConfig['sender_phone'])
    : '';

if (strlen($sender) < 8 || strlen($sender) > 15) {
    lottoLinkSmsResponse(false, '설정관리의 문자 발신번호를 확인해주세요.');
}

$groupId = 'LL'
    . substr(
        sha1($targetMbId . microtime(true) . mt_rand()),
        0,
        16
    );

$queued = lottoSmsQueueOShotWithSplit(
    $groupId,
    $sender,
    $receiver,
    $message,
    '추천번호 링크',
    array(
        'mb_id' => $targetMbId,
        'sender_mb_id' => $loginMbId,
        'send_category' => 'link',
    )
);

if (empty($queued['success'])) {
    lottoLinkSmsResponse(
        false,
        isset($queued['error'])
            ? (string) $queued['error']
            : 'OShot 문자 큐 등록에 실패했습니다.'
    );
}

lottoLinkSmsResponse(true, '링크 문자를 OShot 문자 큐에 등록했습니다.');
