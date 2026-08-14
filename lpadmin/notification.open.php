<?php
include_once("_common.php");

$login_mb_id = isset($member['mb_id']) ? trim((string) $member['mb_id']) : '';
$ln_id = isset($_GET['ln_id']) ? (int) $_GET['ln_id'] : 0;

if ($login_mb_id === '' || $ln_id < 1) {
    alert('알림정보가 올바르지 않습니다.', G5_LADMIN_URL);
    exit;
}

$login_mb_id_sql = sql_real_escape_string($login_mb_id);
$notification = sql_fetch(
    "select ln_id, recipient_mb_id, mb_id, notification_type, reference_type, reference_id, is_read
       from l_notification
      where ln_id = {$ln_id}
        and recipient_mb_id = '{$login_mb_id_sql}'
      limit 1",
    false
);

if (empty($notification['ln_id'])) {
    alert('확인할 수 없는 알림입니다.', G5_LADMIN_URL);
    exit;
}

$notification_type = isset($notification['notification_type']) ? (string) $notification['notification_type'] : '';
$target_url = G5_LADMIN_URL;

if ($notification_type === 'payment_approved') {
    $target_mb_id = isset($notification['mb_id']) ? trim((string) $notification['mb_id']) : '';
    $query = array('sch_staff' => $login_mb_id);
    if ($target_mb_id !== '') {
        $query['sch_text'] = $target_mb_id;
    }
    $target_url = G5_LADMIN_URL.'/payment/sales.list.php?'.http_build_query($query);

    if ((int) $notification['is_read'] === 0) {
        if (!sql_query(
            "update l_notification
                set is_read = 1,
                    read_at = now()
              where ln_id = {$ln_id}
                and recipient_mb_id = '{$login_mb_id_sql}'
                and is_read = 0",
            false
        )) {
            alert('알림 처리 중 오류가 발생했습니다.', G5_LADMIN_URL);
            exit;
        }
    }
}

goto_url($target_url);
