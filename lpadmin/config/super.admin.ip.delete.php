<?php

include_once("_common.php");

$loginMbId = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$loginLevel = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if (
    $loginMbId === ''
    || $loginLevel < LOTTO_ROLE_SUPER_ADMIN
) {
    alert(
        '최고관리자만 처리할 수 있습니다.',
        G5_LADMIN_URL
    );
    exit;
}

if (
    !isset($_SERVER['REQUEST_METHOD'])
    || $_SERVER['REQUEST_METHOD'] !== 'POST'
) {
    alert(
        '올바른 방법으로 이용해 주십시오.',
        G5_LADMIN_URL.'/config/super.admin.ip.php'
    );
    exit;
}

lottoConfigTokenCheck();

$logId = isset($_POST['lsail_id'])
    ? (int) $_POST['lsail_id']
    : 0;

if ($logId < 1) {
    alert(
        '삭제할 접속 IP 기록을 선택해 주세요.',
        G5_LADMIN_URL.'/config/super.admin.ip.php'
    );
    exit;
}

$logRow = sql_fetch(
    "select
        lsail_id,
        mb_id,
        ip_address
     from l_super_admin_ip_log
     where lsail_id = '{$logId}'
     limit 1",
    false
);

if (empty($logRow['lsail_id'])) {
    alert(
        '접속 IP 기록을 찾을 수 없습니다.',
        G5_LADMIN_URL.'/config/super.admin.ip.php'
    );
    exit;
}

$deleted = sql_query(
    "delete
       from l_super_admin_ip_log
      where lsail_id = '{$logId}'
      limit 1",
    false
);

if (!$deleted) {
    alert(
        '접속 IP 기록 삭제에 실패했습니다.',
        G5_LADMIN_URL.'/config/super.admin.ip.php'
    );
    exit;
}

if (function_exists('fnSetLog')) {
    fnSetLog(
        $loginMbId,
        '최고관리자 접속 IP 기록을 삭제하였습니다.'
    );
}

alert(
    '접속 IP 기록을 삭제했습니다.',
    G5_LADMIN_URL.'/config/super.admin.ip.php'
);
?>
