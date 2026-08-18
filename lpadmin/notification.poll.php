<?php
include_once("_common.php");

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$login_mb_id = isset($member['mb_id']) ? trim((string) $member['mb_id']) : '';
$login_level = isset($member['mb_level']) ? (int) $member['mb_level'] : 0;

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

        if (
            in_array(
                $notification_type,
                array('payment_approved', 'payment_rejected'),
                true
            )
            && $ln_id > 0
        ) {
            $open_url = G5_LADMIN_URL.'/notification.open.php?ln_id='.$ln_id;
        }

        $open_mode = 'same';

        if ($notification_type === 'payment_rejected') {
            $open_mode = 'popup';
        }

        $notifications[] = array(
            'id' => 'notification-'.$ln_id,
            'type' => $notification_type,
            'title' => isset($notification_row['title']) ? (string) $notification_row['title'] : '',
            'message' => isset($notification_row['message']) ? (string) $notification_row['message'] : '',
            'created_at' => isset($notification_row['created_at']) ? (string) $notification_row['created_at'] : '',
            'open_url' => $open_url,
            'open_mode' => $open_mode,
        );
    }
}

if ($login_level < LOTTO_ROLE_ADMIN) {
    $call_alarm_result = sql_query(
        "select a.lm_id,
                a.mb_id,
                a.lm_alarm_type,
                a.lm_alarm_date,
                m.mb_name
           from l_memo a
           left join g5_member m on m.mb_id = a.mb_id
          where a.from_mb_id = '{$login_mb_id_sql}'
            and a.lm_alarm_view = 0
            and trim(a.lm_alarm_date) <> ''
            and a.lm_alarm_date <> '0000-00-00 00:00:00'
            and left(a.lm_alarm_date, 16) <= date_format(date_add(utc_timestamp(), interval 9 hour), '%Y-%m-%d %H:%i')
          order by a.lm_alarm_date asc, a.lm_id asc",
        false
    );

    if ($call_alarm_result) {
        while ($call_alarm_row = sql_fetch_array($call_alarm_result)) {
            $lm_id = isset($call_alarm_row['lm_id']) ? (int) $call_alarm_row['lm_id'] : 0;
            $mb_id = isset($call_alarm_row['mb_id']) ? trim((string) $call_alarm_row['mb_id']) : '';
            if ($lm_id < 1 || $mb_id === '') {
                continue;
            }

            $member_name = isset($call_alarm_row['mb_name']) ? trim((string) $call_alarm_row['mb_name']) : '';
            if ($member_name === '') {
                $member_name = $mb_id;
            }
            $alarm_type = isset($call_alarm_row['lm_alarm_type']) ? trim((string) $call_alarm_row['lm_alarm_type']) : '';
            $alarm_message = $member_name.' 회원 통화예약';
            if ($alarm_type !== '') {
                $alarm_message .= ' · '.$alarm_type;
            }

            $open_url = G5_LADMIN_URL.'/member/pop.member.php?'.http_build_query(array(
                'mb_id' => base64_encode($mb_id),
                'alarm_lm_id' => $lm_id,
            ));

            $notifications[] = array(
                'id' => 'call-'.$lm_id,
                'type' => 'call_reservation',
                'title' => '통화예약',
                'message' => $alarm_message,
                'created_at' => isset($call_alarm_row['lm_alarm_date']) ? (string) $call_alarm_row['lm_alarm_date'] : '',
                'open_url' => $open_url,
                'open_mode' => 'popup',
            );
        }
    }
}

if ($login_level >= LOTTO_ROLE_ADMIN) {
    $pending_payment_result = sql_query(
        "select a.lpr_id,
                a.request_no,
                a.mb_id,
                a.requested_by,
                a.payment_method,
                a.request_amount,
                a.created_at,
                m.mb_name as member_name,
                r.mb_name as requested_by_name
           from l_payment_request a
           left join g5_member m on m.mb_id = a.mb_id
           left join g5_member r on r.mb_id = a.requested_by
          where a.request_status = '승인대기'
          order by a.created_at asc, a.lpr_id asc",
        false
    );

    if ($pending_payment_result) {
        while ($pending_payment_row = sql_fetch_array($pending_payment_result)) {
            $lpr_id = isset($pending_payment_row['lpr_id']) ? (int) $pending_payment_row['lpr_id'] : 0;
            $request_no = isset($pending_payment_row['request_no']) ? trim((string) $pending_payment_row['request_no']) : '';
            if ($lpr_id < 1 || $request_no === '') {
                continue;
            }

            $member_name = isset($pending_payment_row['member_name']) ? trim((string) $pending_payment_row['member_name']) : '';
            if ($member_name === '') {
                $member_name = isset($pending_payment_row['mb_id']) ? trim((string) $pending_payment_row['mb_id']) : '';
            }
            $requested_by_name = isset($pending_payment_row['requested_by_name']) ? trim((string) $pending_payment_row['requested_by_name']) : '';
            if ($requested_by_name === '') {
                $requested_by_name = isset($pending_payment_row['requested_by']) ? trim((string) $pending_payment_row['requested_by']) : '';
            }
            $payment_method = isset($pending_payment_row['payment_method']) ? trim((string) $pending_payment_row['payment_method']) : '';
            $request_amount = isset($pending_payment_row['request_amount']) ? (int) $pending_payment_row['request_amount'] : 0;

            $message_parts = array();
            if ($member_name !== '') {
                $message_parts[] = $member_name;
            }
            if ($requested_by_name !== '') {
                $message_parts[] = $requested_by_name.' 요청';
            }
            if ($payment_method !== '') {
                $message_parts[] = $payment_method;
            }
            if ($request_amount > 0) {
                $message_parts[] = number_format($request_amount).'원';
            }

            $open_url = G5_LADMIN_URL.'/payment/payment.approval.php?'.http_build_query(array(
                'sch_status' => '승인대기',
                'sch_text' => $request_no,
            ));

            $notifications[] = array(
                'id' => 'payment-request-'.$lpr_id,
                'type' => 'payment_request',
                'title' => '결제 승인요청',
                'message' => implode(' · ', $message_parts),
                'created_at' => isset($pending_payment_row['created_at']) ? (string) $pending_payment_row['created_at'] : '',
                'open_url' => $open_url,
                'open_mode' => 'same',
            );
        }
    }
}

usort($notifications, function ($a, $b) {
    return strcmp((string) $a['created_at'], (string) $b['created_at']);
});

echo json_encode(array(
    'ok' => true,
    'count' => count($notifications),
    'notifications' => $notifications,
), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
