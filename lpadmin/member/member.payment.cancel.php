<?php

include_once("_common.php");

$login_mb_id = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$login_level = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if (
    $login_mb_id === ''
    || $login_level < LOTTO_ROLE_ADMIN
) {
    alert('관리자 이상만 결제취소를 처리할 수 있습니다.');
    exit;
}

if (
    !isset($_SERVER['REQUEST_METHOD'])
    || $_SERVER['REQUEST_METHOD'] !== 'POST'
) {
    alert('올바른 요청이 아닙니다.');
    exit;
}

if (!lottoMemberTokenCheck()) {
    alert(
        '올바른 요청이 아닙니다. 페이지를 새로고침한 후 다시 시도해주세요.'
    );
    exit;
}

$ls_id = isset($_POST['ls_id'])
    ? (int) $_POST['ls_id']
    : 0;

$cancel_amount_raw = isset($_POST['cancel_amount'])
    ? preg_replace('/[^0-9]/', '', (string) $_POST['cancel_amount'])
    : '';

$cancel_amount = (int) $cancel_amount_raw;

$cancel_reason = isset($_POST['cancel_reason'])
    ? trim((string) $_POST['cancel_reason'])
    : '';

if ($ls_id < 1) {
    alert('취소할 결제정보가 올바르지 않습니다.');
    exit;
}

if ($cancel_amount < 1) {
    alert('취소금액을 입력해주세요.');
    exit;
}

if ($cancel_reason === '') {
    alert('취소사유를 입력해주세요.');
    exit;
}

if (strlen($cancel_reason) > 255) {
    alert('취소사유는 255자 이내로 입력해주세요.');
    exit;
}

if (!sql_query('START TRANSACTION', false)) {
    alert('결제취소 처리를 시작할 수 없습니다.');
    exit;
}

$sale_result = sql_query(
    "select *
       from l_sales
      where ls_id = {$ls_id}
      for update",
    false
);

if (!$sale_result) {
    sql_query('ROLLBACK', false);
    alert('결제정보를 조회하지 못했습니다.');
    exit;
}

$sale = sql_fetch_array($sale_result);

if (!$sale || empty($sale['ls_id'])) {
    sql_query('ROLLBACK', false);
    alert('결제정보를 찾을 수 없습니다.');
    exit;
}

$lpr_id = (int) $sale['lpr_id'];

$request_result = sql_query(
    "select *
       from l_payment_request
      where lpr_id = {$lpr_id}
      for update",
    false
);

if (!$request_result) {
    sql_query('ROLLBACK', false);
    alert('결제 승인정보를 조회하지 못했습니다.');
    exit;
}

$request = sql_fetch_array($request_result);

if (!$request || empty($request['lpr_id'])) {
    sql_query('ROLLBACK', false);
    alert('결제 승인정보를 찾을 수 없습니다.');
    exit;
}

$request_status = trim((string) $request['request_status']);

if ($request_status === '승인취소') {
    sql_query('ROLLBACK', false);
    alert('이미 전액 취소된 결제입니다.');
    exit;
}

if (
    !in_array(
        $request_status,
        array('승인완료', '부분취소'),
        true
    )
) {
    sql_query('ROLLBACK', false);
    alert('현재 상태에서는 결제를 취소할 수 없습니다.');
    exit;
}

$sale_amount = (int) $sale['sale_amount'];

$cancel_sum_row = sql_fetch(
    "select coalesce(sum(cancel_amount), 0) as cancel_amount
       from l_sales_cancel
      where ls_id = {$ls_id}",
    false
);

$cancelled_amount = isset($cancel_sum_row['cancel_amount'])
    ? (int) $cancel_sum_row['cancel_amount']
    : 0;

$remaining_amount = $sale_amount - $cancelled_amount;

if ($remaining_amount < 1) {
    sql_query('ROLLBACK', false);
    alert('이미 취소 가능한 금액이 없습니다.');
    exit;
}

if ($cancel_amount > $remaining_amount) {
    sql_query('ROLLBACK', false);
    alert(
        '취소금액이 남은 결제금액 '
        . number_format($remaining_amount)
        . '원을 초과했습니다.'
    );
    exit;
}

$mb_id = trim((string) $sale['mb_id']);
$mb_id_sql = sql_real_escape_string($mb_id);
$cancel_reason_sql = sql_real_escape_string($cancel_reason);
$cancelled_by_sql = sql_real_escape_string($login_mb_id);

if (!sql_query(
    "insert into l_sales_cancel set
        ls_id = {$ls_id},
        lpr_id = {$lpr_id},
        mb_id = '{$mb_id_sql}',
        cancel_amount = {$cancel_amount},
        cancel_reason = '{$cancel_reason_sql}',
        cancelled_by = '{$cancelled_by_sql}',
        cancelled_at = now(),
        created_at = now()",
    false
)) {
    sql_query('ROLLBACK', false);
    alert('결제 취소이력 저장에 실패했습니다.');
    exit;
}

$total_cancelled_amount =
    $cancelled_amount + $cancel_amount;

$new_status = (
    $total_cancelled_amount >= $sale_amount
)
    ? '승인취소'
    : '부분취소';

$new_status_sql = sql_real_escape_string($new_status);

if (!sql_query(
    "update l_payment_request set
        request_status = '{$new_status_sql}',
        updated_at = now()
      where lpr_id = {$lpr_id}",
    false
)) {
    sql_query('ROLLBACK', false);
    alert('결제 취소상태 저장에 실패했습니다.');
    exit;
}

if (!sql_query('COMMIT', false)) {
    sql_query('ROLLBACK', false);
    alert('결제 취소 저장에 실패했습니다.');
    exit;
}

if (function_exists('fnSetLog')) {
    fnSetLog(
        $login_mb_id,
        $mb_id
        . '님의 결제 '
        . number_format($cancel_amount)
        . '원을 '
        . $new_status
        . ' 처리하였습니다.'
    );
}

alert(
    number_format($cancel_amount)
    . '원이 '
    . $new_status
    . ' 처리되었습니다.',
    G5_LADMIN_URL
    . '/member/pop.payment.php?mb_id='
    . urlencode(base64_encode($mb_id))
);
