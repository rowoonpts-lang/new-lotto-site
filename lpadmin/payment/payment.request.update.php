<?php
include_once("_common.php");

$login_mb_id = isset($member['mb_id']) ? trim((string) $member['mb_id']) : '';
$login_level = isset($member['mb_level']) ? (int) $member['mb_level'] : 0;

if ($login_mb_id === '' || $login_level >= LOTTO_ROLE_ADMIN) {
    alert('결제 승인요청 수정 권한이 없습니다.', G5_LADMIN_URL);
    exit;
}

if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    alert('올바른 요청이 아닙니다.', G5_LADMIN_URL);
    exit;
}

$lpr_id = isset($_POST['lpr_id']) ? (int) $_POST['lpr_id'] : 0;
$product_type = isset($_POST['product_type']) ? trim((string) $_POST['product_type']) : '';
$request_amount_raw = isset($_POST['request_amount'])
    ? preg_replace('/[^0-9]/', '', (string) $_POST['request_amount'])
    : '';
$depositor_name = isset($_POST['depositor_name'])
    ? trim((string) $_POST['depositor_name'])
    : '';
$bank_account_id = isset($_POST['bank_account_id'])
    ? (int) $_POST['bank_account_id']
    : 0;

if ($lpr_id < 1) {
    alert('결제 승인요청 정보가 올바르지 않습니다.', G5_LADMIN_URL);
    exit;
}

$request_amount = (int) $request_amount_raw;

if ($request_amount < 1) {
    alert('입금 예정금액을 입력하세요.');
    exit;
}

if ($depositor_name === '') {
    alert('입금자명을 입력하세요.');
    exit;
}

$product_list = fnGetTypePre();

if (!is_array($product_list) || !in_array($product_type, $product_list, true)) {
    alert('상품정보가 올바르지 않습니다.');
    exit;
}

if ($bank_account_id < 1) {
    alert('입금계좌를 선택하세요.');
    exit;
}

$bank_account = sql_fetch(
    "select lpba_id, bank_name, account_number, account_holder
       from l_payment_bank_account
      where lpba_id = {$bank_account_id}
        and is_active = 1
      limit 1",
    false
);

if (empty($bank_account['lpba_id'])) {
    alert('사용할 수 없는 입금계좌입니다.');
    exit;
}

if (!sql_query('START TRANSACTION', false)) {
    alert('결제 승인요청 수정을 시작할 수 없습니다.');
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
    alert('결제 승인요청을 조회할 수 없습니다.');
    exit;
}

$request = sql_fetch_array($request_result);

if (!$request || empty($request['lpr_id'])) {
    sql_query('ROLLBACK', false);
    alert('결제 승인요청을 찾을 수 없습니다.');
    exit;
}

if ((string) $request['requested_by'] !== $login_mb_id) {
    sql_query('ROLLBACK', false);
    alert('본인이 등록한 결제 승인요청만 수정할 수 있습니다.');
    exit;
}

if (!in_array((string) $request['request_status'], array('승인대기', '승인거절'), true)) {
    sql_query('ROLLBACK', false);
    alert('현재 상태에서는 결제 승인요청을 수정할 수 없습니다.');
    exit;
}

if ((string) $request['payment_method'] !== '무통장') {
    sql_query('ROLLBACK', false);
    alert('현재는 무통장 승인요청만 수정할 수 있습니다.');
    exit;
}

$bank_account_text = trim(
    (string) $bank_account['bank_name'].' '.
    (string) $bank_account['account_number'].' '.
    (string) $bank_account['account_holder']
);

$product_type_sql = sql_real_escape_string($product_type);
$depositor_name_sql = sql_real_escape_string($depositor_name);
$bank_account_sql = sql_real_escape_string($bank_account_text);

if (!sql_query(
    "update l_payment_request set
        product_type = '{$product_type_sql}',
        request_amount = {$request_amount},
        depositor_name = '{$depositor_name_sql}',
        bank_account = '{$bank_account_sql}',
        request_status = '승인대기',
        rejected_by = '',
        rejected_at = null,
        reject_reason = '',
        updated_at = now()
      where lpr_id = {$lpr_id}",
    false
)) {
    sql_query('ROLLBACK', false);
    alert('결제 승인요청 수정 중 오류가 발생했습니다.');
    exit;
}

$login_mb_id_sql = sql_real_escape_string($login_mb_id);

sql_query(
    "update l_notification set
        is_read = 1,
        read_at = ifnull(read_at, now())
      where recipient_mb_id = '{$login_mb_id_sql}'
        and notification_type = 'payment_rejected'
        and reference_type = 'payment_request'
        and reference_id = {$lpr_id}
        and is_read = 0",
    false
);

if (!sql_query('COMMIT', false)) {
    sql_query('ROLLBACK', false);
    alert('결제 승인요청 수정 저장에 실패했습니다.');
    exit;
}

if (function_exists('fnSetLog')) {
    fnSetLog(
        $login_mb_id,
        (string) $request['mb_id'].'님의 결제 승인요청을 수정하였습니다.'
    );
}

alert(
    '수정한 내용으로 다시 승인요청 되었습니다.',
    G5_LADMIN_URL.'/member/pop.member.php?mb_id='.
        urlencode(base64_encode((string) $request['mb_id']))
);
