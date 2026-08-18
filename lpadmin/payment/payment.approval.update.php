<?php
include_once("_common.php");

$login_level = isset($member['mb_level']) ? (int) $member['mb_level'] : 0;
if ($login_level < LOTTO_ROLE_ADMIN) {
    alert('관리자 이상만 처리할 수 있습니다.');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    alert('올바른 요청이 아닙니다.', G5_LADMIN_URL.'/payment/payment.approval.php');
    exit;
}

$lpr_id = isset($_POST['lpr_id']) ? (int) $_POST['lpr_id'] : 0;

if ($lpr_id < 1) {
    alert('결제 승인요청 번호가 올바르지 않습니다.', G5_LADMIN_URL.'/payment/payment.approval.php');
    exit;
}

if (!sql_query('START TRANSACTION', false)) {
    alert('승인 처리를 시작할 수 없습니다.', G5_LADMIN_URL.'/payment/payment.approval.php');
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
    alert('결제 승인요청을 조회하지 못했습니다.', G5_LADMIN_URL.'/payment/payment.approval.php');
    exit;
}

$request = sql_fetch_array($request_result);
if (!$request || empty($request['lpr_id'])) {
    sql_query('ROLLBACK', false);
    alert('결제 승인요청을 찾을 수 없습니다.', G5_LADMIN_URL.'/payment/payment.approval.php');
    exit;
}

if ($request['request_status'] !== '승인대기') {
    sql_query('ROLLBACK', false);
    alert('이미 처리된 결제 승인요청입니다.', G5_LADMIN_URL.'/payment/payment.approval.php');
    exit;
}

$payment_method = trim((string) $request['payment_method']);

if (!in_array($payment_method, array('무통장', '신용카드'), true)) {
    sql_query('ROLLBACK', false);
    alert('지원하지 않는 결제수단입니다.', G5_LADMIN_URL.'/payment/payment.approval.php');
    exit;
}

$mb_id = trim((string) $request['mb_id']);
$staff_mb_id = trim((string) $request['staff_mb_id']);
$product_type = trim((string) $request['product_type']);
$request_amount = (int) $request['request_amount'];
$approved_by = isset($member['mb_id']) ? trim((string) $member['mb_id']) : '';

if ($mb_id === '' || $staff_mb_id === '' || $product_type === '' || $request_amount < 1 || $approved_by === '') {
    sql_query('ROLLBACK', false);
    alert('승인요청 필수 정보가 올바르지 않습니다.', G5_LADMIN_URL.'/payment/payment.approval.php');
    exit;
}

$mb_id_sql = sql_real_escape_string($mb_id);
$staff_mb_id_sql = sql_real_escape_string($staff_mb_id);
$product_type_sql = sql_real_escape_string($product_type);
$approved_by_sql = sql_real_escape_string($approved_by);
$payment_method_sql = sql_real_escape_string($payment_method);

if ($payment_method === '신용카드') {
    $card_secret = sql_fetch(
        "select lpcs_id
           from l_payment_card_secret
          where lpr_id = {$lpr_id}
          limit 1",
        false
    );

    if (empty($card_secret['lpcs_id'])) {
        sql_query('ROLLBACK', false);
        alert(
            '카드 민감정보가 없어 승인완료 처리할 수 없습니다.',
            G5_LADMIN_URL.'/payment/payment.approval.php'
        );
        exit;
    }
}

$member_row = sql_fetch("select mb_id, mb_name from g5_member where mb_id = '{$mb_id_sql}' limit 1");
if (!$member_row || empty($member_row['mb_id'])) {
    sql_query('ROLLBACK', false);
    alert('승인 대상 회원을 찾을 수 없습니다.', G5_LADMIN_URL.'/payment/payment.approval.php');
    exit;
}

if (!sql_query(
    "update g5_member
        set mb_type = '{$product_type_sql}'
      where mb_id = '{$mb_id_sql}'",
    false
)) {
    sql_query('ROLLBACK', false);
    alert('회원등급 반영 중 오류가 발생했습니다.', G5_LADMIN_URL.'/payment/payment.approval.php');
    exit;
}

if (!sql_query(
    "insert into l_sales set
        lpr_id = {$lpr_id},
        mb_id = '{$mb_id_sql}',
        staff_mb_id = '{$staff_mb_id_sql}',
        payment_method = '{$payment_method_sql}',
        product_type = '{$product_type_sql}',
        sale_amount = {$request_amount},
        approved_by = '{$approved_by_sql}',
        approved_at = now(),
        created_at = now()",
    false
)) {
    sql_query('ROLLBACK', false);
    alert('매출 등록 중 오류가 발생했습니다. 중복 승인 여부를 확인해주세요.', G5_LADMIN_URL.'/payment/payment.approval.php');
    exit;
}

$member_name = trim((string) $member_row['mb_name']);
if ($member_name === '') {
    $member_name = $mb_id;
}
$notification_title = '결제 승인완료';
$notification_message = $member_name.' 회원 '.number_format($request_amount).'원 승인 완료';
$notification_title_sql = sql_real_escape_string($notification_title);
$notification_message_sql = sql_real_escape_string($notification_message);

if (!sql_query(
    "insert into l_notification set
        recipient_mb_id = '{$staff_mb_id_sql}',
        mb_id = '{$mb_id_sql}',
        notification_type = 'payment_approved',
        title = '{$notification_title_sql}',
        message = '{$notification_message_sql}',
        reference_type = 'payment_request',
        reference_id = {$lpr_id},
        is_read = 0,
        created_at = now()",
    false
)) {
    sql_query('ROLLBACK', false);
    alert('담당자 알림 등록 중 오류가 발생했습니다.', G5_LADMIN_URL.'/payment/payment.approval.php');
    exit;
}

if (!sql_query(
    "update l_payment_request set
        request_status = '승인완료',
        approved_amount = {$request_amount},
        approved_by = '{$approved_by_sql}',
        approved_at = now(),
        updated_at = now()
      where lpr_id = {$lpr_id}
        and request_status = '승인대기'",
    false
)) {
    sql_query('ROLLBACK', false);
    alert('결제 승인상태 변경 중 오류가 발생했습니다.', G5_LADMIN_URL.'/payment/payment.approval.php');
    exit;
}

if ($payment_method === '신용카드') {
    if (!sql_query(
        "delete from l_payment_card_secret
          where lpr_id = {$lpr_id}",
        false
    )) {
        sql_query('ROLLBACK', false);
        alert(
            '카드 민감정보 삭제 중 오류가 발생했습니다.',
            G5_LADMIN_URL.'/payment/payment.approval.php'
        );
        exit;
    }
}

if (!sql_query('COMMIT', false)) {
    sql_query('ROLLBACK', false);
    alert('결제 승인완료 저장에 실패했습니다.', G5_LADMIN_URL.'/payment/payment.approval.php');
    exit;
}

if (function_exists('fnSetLog')) {
    fnSetLog($approved_by, $member_name.' 회원의 결제 승인요청을 승인완료 처리하였습니다.');
}

$from_card_detail = isset($_POST['from_card_detail'])
    && (string) $_POST['from_card_detail'] === '1';

if ($from_card_detail && $payment_method === '신용카드') {
    echo '<script>';
    echo 'alert("카드 결제 승인완료 처리되었습니다.");';
    echo 'if (window.opener && !window.opener.closed) { window.opener.location.reload(); }';
    echo 'window.close();';
    echo '</script>';
    exit;
}

alert(
    '결제 승인완료 처리되었습니다.',
    G5_LADMIN_URL.'/payment/payment.approval.php?sch_status=승인완료'
);
