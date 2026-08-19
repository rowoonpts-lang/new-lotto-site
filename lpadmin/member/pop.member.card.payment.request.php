<?php
include_once("_common.php");
include_once(G5_PATH.'/include/lotto_card_security.lib.php');

$mb_id = isset($_POST['mb_id'])
    ? trim((string) $_POST['mb_id'])
    : '';

$card_company = isset($_POST['card_company'])
    ? trim((string) $_POST['card_company'])
    : '';

$card_number = isset($_POST['card_number'])
    ? preg_replace('/[^0-9]/', '', (string) $_POST['card_number'])
    : '';

$card_expiry = isset($_POST['card_expiry'])
    ? trim((string) $_POST['card_expiry'])
    : '';

$card_password_prefix = isset($_POST['card_password_prefix'])
    ? preg_replace('/[^0-9]/', '', (string) $_POST['card_password_prefix'])
    : '';

$birth_date = isset($_POST['birth_date'])
    ? preg_replace('/[^0-9]/', '', (string) $_POST['birth_date'])
    : '';

$product_type = isset($_POST['product_type'])
    ? trim((string) $_POST['product_type'])
    : '';

$request_amount_raw = isset($_POST['request_amount'])
    ? preg_replace('/[^0-9]/', '', (string) $_POST['request_amount'])
    : '';

$installment_months = isset($_POST['installment_months'])
    ? (int) $_POST['installment_months']
    : -1;

$login_mb_id = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$login_level = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if ($login_mb_id === '' || $login_level >= LOTTO_ROLE_ADMIN) {
    alert('카드 승인요청은 직원/팀장 권한에서만 등록할 수 있습니다.');
    exit;
}

if ($mb_id === '') {
    alert('회원정보가 올바르지 않습니다.');
    exit;
}

if ($card_company === '' || strlen($card_company) > 100) {
    alert('카드사를 올바르게 입력하세요.');
    exit;
}

if (!preg_match('/^[0-9]{15,16}$/', $card_number)) {
    alert('카드번호를 15~16자리 숫자로 입력하세요.');
    exit;
}

if (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $card_expiry)) {
    alert('유효기간을 MM/YY 형식으로 입력하세요.');
    exit;
}

if (!preg_match('/^[0-9]{2}$/', $card_password_prefix)) {
    alert('카드 비밀번호 앞 2자리를 입력하세요.');
    exit;
}

if (!preg_match('/^[0-9]{6}$/', $birth_date)) {
    alert('생년월일을 6자리로 입력하세요.');
    exit;
}

$birth_yy = (int) substr($birth_date, 0, 2);
$birth_mm = (int) substr($birth_date, 2, 2);
$birth_dd = (int) substr($birth_date, 4, 2);

if (!checkdate($birth_mm, $birth_dd, 2000 + $birth_yy)) {
    alert('생년월일 형식이 올바르지 않습니다.');
    exit;
}

if ($installment_months < 0 || $installment_months > 12) {
    alert('할부개월 정보가 올바르지 않습니다.');
    exit;
}

$request_amount = (int) $request_amount_raw;

if ($request_amount < 1) {
    alert('결제 요청금액을 입력하세요.');
    exit;
}

$product_list = fnGetTypePre();

if (
    !is_array($product_list)
    || !in_array($product_type, $product_list, true)
) {
    alert('상품정보가 올바르지 않습니다.');
    exit;
}

$mb_id_sql = sql_real_escape_string($mb_id);

$target_member = sql_fetch(
    "select mb_id, mb_name, mb_hp
       from g5_member
      where mb_id = '{$mb_id_sql}'
      limit 1",
    false
);

if (empty($target_member['mb_id'])) {
    alert('회원정보를 찾을 수 없습니다.');
    exit;
}

$assignment = sql_fetch(
    "select staff_mb_id
       from l_member_assignment
      where mb_id = '{$mb_id_sql}'
      limit 1",
    false
);

$assigned_staff_mb_id = isset($assignment['staff_mb_id'])
    ? trim((string) $assignment['staff_mb_id'])
    : '';

if ($assigned_staff_mb_id === '') {
    alert('담당자가 배정되지 않은 회원입니다.');
    exit;
}

$allowed_staff_ids = lottoGetAccessibleStaffIds(
    $login_mb_id,
    $login_level
);

if (!in_array($assigned_staff_mb_id, $allowed_staff_ids, true)) {
    alert('접근 권한이 없는 회원입니다.');
    exit;
}

try {
    $encrypted = lottoCardEncryptPayload(array(
        'card_number' => $card_number,
        'card_expiry' => $card_expiry,
        'card_password_prefix' => $card_password_prefix,
        'birth_date' => $birth_date,
    ));
} catch (Throwable $e) {
    alert('카드정보 암호화 준비 중 오류가 발생했습니다.');
    exit;
}

$request_no = 'PAY'.date('YmdHis').strtoupper(bin2hex(random_bytes(3)));

$request_no_sql = sql_real_escape_string($request_no);
$staff_mb_id_sql = sql_real_escape_string($assigned_staff_mb_id);
$requested_by_sql = sql_real_escape_string($login_mb_id);
$product_type_sql = sql_real_escape_string($product_type);
$member_phone_sql = sql_real_escape_string((string) $target_member['mb_hp']);
$card_company_sql = sql_real_escape_string($card_company);
$encrypted_payload_sql = sql_real_escape_string((string) $encrypted['payload']);
$card_last4_sql = sql_real_escape_string(substr($card_number, -4));
$key_version = (int) $encrypted['key_version'];

if (!sql_query('START TRANSACTION', false)) {
    alert('카드 승인요청 저장을 시작할 수 없습니다.');
    exit;
}

if (!sql_query(
    "insert into l_payment_request set
        request_no = '{$request_no_sql}',
        mb_id = '{$mb_id_sql}',
        staff_mb_id = '{$staff_mb_id_sql}',
        requested_by = '{$requested_by_sql}',
        payment_method = '신용카드',
        product_type = '{$product_type_sql}',
        request_amount = {$request_amount},
        request_status = '승인대기',
        member_phone = '{$member_phone_sql}',
        bank_account = '',
        depositor_name = '',
        sms_send = 0,
        card_company = '{$card_company_sql}',
        installment_months = {$installment_months},
        created_at = now(),
        updated_at = now()",
    false
)) {
    sql_query('ROLLBACK', false);
    alert('카드 승인요청 저장 중 오류가 발생했습니다.');
    exit;
}

$insert_id_row = sql_fetch(
    "select last_insert_id() as lpr_id",
    false
);

$lpr_id = isset($insert_id_row['lpr_id'])
    ? (int) $insert_id_row['lpr_id']
    : 0;

if ($lpr_id < 1) {
    sql_query('ROLLBACK', false);
    alert('카드 승인요청 번호를 확인할 수 없습니다.');
    exit;
}

if (!sql_query(
    "insert into l_payment_card_secret set
        lpr_id = {$lpr_id},
        encrypted_payload = '{$encrypted_payload_sql}',
        card_last4 = '{$card_last4_sql}',
        key_version = {$key_version},
        created_at = now(),
        expires_at = null,
        cleared_at = null",
    false
)) {
    sql_query('ROLLBACK', false);
    alert('카드 민감정보 저장 중 오류가 발생했습니다.');
    exit;
}

if (!sql_query('COMMIT', false)) {
    sql_query('ROLLBACK', false);
    alert('카드 승인요청 저장에 실패했습니다.');
    exit;
}

if (function_exists('fnSetLog')) {
    fnSetLog(
        $login_mb_id,
        $mb_id.'님의 카드 결제 승인요청을 등록하였습니다.'
    );
}

alert(
    '카드 승인요청이 등록되었습니다.',
    G5_LADMIN_URL.'/member/pop.payment.php?mb_id='.
        urlencode(base64_encode($mb_id))
);
