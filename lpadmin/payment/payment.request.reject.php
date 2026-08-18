<?php
include_once("_common.php");

$login_mb_id = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$login_level = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if ($login_mb_id === '' || $login_level < LOTTO_ROLE_ADMIN) {
    alert('관리자 이상만 결제 승인요청을 반려할 수 있습니다.');
    exit;
}

if (
    !isset($_SERVER['REQUEST_METHOD'])
    || $_SERVER['REQUEST_METHOD'] !== 'POST'
) {
    alert(
        '올바른 요청이 아닙니다.',
        G5_LADMIN_URL.'/payment/payment.approval.php'
    );
    exit;
}

$lpr_id = isset($_POST['lpr_id'])
    ? (int) $_POST['lpr_id']
    : 0;

$reject_reason = isset($_POST['reject_reason'])
    ? trim((string) $_POST['reject_reason'])
    : '';

if ($lpr_id < 1) {
    alert(
        '결제 승인요청 번호가 올바르지 않습니다.',
        G5_LADMIN_URL.'/payment/payment.approval.php'
    );
    exit;
}

if ($reject_reason === '') {
    alert('반려사유를 입력해주세요.');
    exit;
}

if (strlen($reject_reason) > 255) {
    alert('반려사유는 255자 이내로 입력해주세요.');
    exit;
}

if (!sql_query('START TRANSACTION', false)) {
    alert(
        '반려 처리를 시작할 수 없습니다.',
        G5_LADMIN_URL.'/payment/payment.approval.php'
    );
    exit;
}

$request_result = sql_query(
    "select *
       from l_payment_request
      where lpr_id = {$lpr_id}
      for update",
    false
);

if (!$request_result) {
    sql_query('ROLLBACK', false);
    alert(
        '결제 승인요청을 조회하지 못했습니다.',
        G5_LADMIN_URL.'/payment/payment.approval.php'
    );
    exit;
}

$request = sql_fetch_array($request_result);

if (!$request || empty($request['lpr_id'])) {
    sql_query('ROLLBACK', false);
    alert(
        '결제 승인요청을 찾을 수 없습니다.',
        G5_LADMIN_URL.'/payment/payment.approval.php'
    );
    exit;
}

if ((string) $request['request_status'] !== '승인대기') {
    sql_query('ROLLBACK', false);
    alert(
        '승인대기 상태의 요청만 반려할 수 있습니다.',
        G5_LADMIN_URL.'/payment/payment.approval.php'
    );
    exit;
}

if ((string) $request['payment_method'] === '신용카드') {
    if (!sql_query(
        "delete from l_payment_card_secret
          where lpr_id = {$lpr_id}",
        false
    )) {
        sql_query('ROLLBACK', false);
        alert(
            '카드 민감정보 정리 중 오류가 발생했습니다.',
            G5_LADMIN_URL.'/payment/payment.approval.php'
        );
        exit;
    }
}

$requested_by = trim((string) $request['requested_by']);

if ($requested_by === '') {
    sql_query('ROLLBACK', false);
    alert(
        '결제 요청자 정보가 없습니다.',
        G5_LADMIN_URL.'/payment/payment.approval.php'
    );
    exit;
}

$login_mb_id_sql = sql_real_escape_string($login_mb_id);
$requested_by_sql = sql_real_escape_string($requested_by);
$mb_id_sql = sql_real_escape_string((string) $request['mb_id']);
$reject_reason_sql = sql_real_escape_string($reject_reason);

if (!sql_query(
    "update l_payment_request set
        request_status = '승인거절',
        rejected_by = '{$login_mb_id_sql}',
        rejected_at = now(),
        reject_reason = '{$reject_reason_sql}',
        updated_at = now()
      where lpr_id = {$lpr_id}
        and request_status = '승인대기'",
    false
)) {
    sql_query('ROLLBACK', false);
    alert(
        '결제 승인요청 반려 처리 중 오류가 발생했습니다.',
        G5_LADMIN_URL.'/payment/payment.approval.php'
    );
    exit;
}

$member_name = '';
$member_row = sql_fetch(
    "select mb_name
       from g5_member
      where mb_id = '{$mb_id_sql}'
      limit 1",
    false
);

if (!empty($member_row['mb_name'])) {
    $member_name = trim((string) $member_row['mb_name']);
}

if ($member_name === '') {
    $member_name = (string) $request['mb_id'];
}

$title = '결제 승인요청 반려';
$message = $member_name.' 회원 결제 승인요청이 반려되었습니다. 사유: '.$reject_reason;

$title_sql = sql_real_escape_string($title);
$message_sql = sql_real_escape_string($message);

if (!sql_query(
    "insert into l_notification set
        recipient_mb_id = '{$requested_by_sql}',
        mb_id = '{$mb_id_sql}',
        notification_type = 'payment_rejected',
        title = '{$title_sql}',
        message = '{$message_sql}',
        reference_type = 'payment_request',
        reference_id = {$lpr_id},
        is_read = 0,
        created_at = now()",
    false
)) {
    sql_query('ROLLBACK', false);
    alert(
        '반려 알림 등록 중 오류가 발생했습니다.',
        G5_LADMIN_URL.'/payment/payment.approval.php'
    );
    exit;
}

if (!sql_query('COMMIT', false)) {
    sql_query('ROLLBACK', false);
    alert(
        '결제 승인요청 반려 저장에 실패했습니다.',
        G5_LADMIN_URL.'/payment/payment.approval.php'
    );
    exit;
}

if (function_exists('fnSetLog')) {
    fnSetLog(
        $login_mb_id,
        $member_name.' 회원의 결제 승인요청을 반려하였습니다.'
    );
}

alert(
    '결제 승인요청을 반려했습니다.',
    G5_LADMIN_URL.'/payment/payment.approval.php?sch_status=승인거절'
);
