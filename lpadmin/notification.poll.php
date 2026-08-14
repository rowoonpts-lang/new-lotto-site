<?php
include_once("_common.php");

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$login_mb_id = isset($member['mb_id']) ? trim((string) $member['mb_id']) : '';

if ($login_mb_id === '') {
    http_response_code(401);
    echo json_encode(array('ok' => false, 'notifications' => array()), JSON_UNESCAPED_UNICODE);
    exit;
}

$login_mb_id_sql = sql_real_escape_string($login_mb_id);
$notifications = array();

$notification_result = sql_query(
    "select ln_id, mb_id, notification_type, title, message, reference_type, reference_id, created_at
       from l_notification
      where recipient_mb_id = '{$login_mb_id_sql}'
        and is_read = 0
      order by created_at asc, ln_id asc",
    false
);

if ($notification_result) {
    while ($notification_row = sql_fetch_array($notification_result)) {
        $notification_type = isset($notification_row['notification_type']) ? (string) $notification_row['notification_type'] : '';
        $ln_id = isset($notification_row['ln_id']) ? (int) $notification_row['ln_id'] : 0;
        $open_url = '';

        if ($notification_type === 'payment_approved' && $ln_id > 0) {
            $open_url = G5_LADMIN_URL.'/notification.open.php?ln_id='.$ln_id;
        }

        $notifications[] = array(
            'id' => $ln_id,
            'type' => $notification_type,
            'title' => isset($notification_row['title']) ? (string) $notification_row['title'] : '',
            'message' => isset($notification_row['message']) ? (string) $notification_row['message'] : '',
            'created_at' => isset($notification_row['created_at']) ? (string) $notification_row['created_at'] : '',
            'open_url' => $open_url,
        );
    }
}

echo json_encode(array(
    'ok' => true,
    'count' => count($notifications),
    'notifications' => $notifications,
), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
