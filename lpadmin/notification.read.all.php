<?php
include_once("_common.php");

$login_mb_id = isset($member['mb_id']) ? trim((string) $member['mb_id']) : '';
$login_level = isset($member['mb_level']) ? (int) $member['mb_level'] : 0;

if ($login_mb_id === '' || $login_level >= LOTTO_ROLE_ADMIN) {
    alert('알림을 처리할 권한이 없습니다.', G5_LADMIN_URL);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    alert('올바른 요청이 아닙니다.', G5_LADMIN_URL);
    exit;
}

$login_mb_id_sql = sql_real_escape_string($login_mb_id);

if (!sql_query(
    "update l_notification
        set is_read = 1,
            read_at = now()
      where recipient_mb_id = '{$login_mb_id_sql}'
        and is_read = 0",
    false
)) {
    alert('알림 읽음 처리 중 오류가 발생했습니다.', G5_LADMIN_URL);
    exit;
}

$return_url = isset($_SERVER['HTTP_REFERER']) ? trim((string) $_SERVER['HTTP_REFERER']) : '';
if ($return_url === '' || strpos($return_url, G5_LADMIN_URL) !== 0) {
    $return_url = G5_LADMIN_URL;
}

goto_url($return_url);
