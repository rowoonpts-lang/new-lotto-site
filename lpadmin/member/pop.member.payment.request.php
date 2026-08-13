<?php
include_once("_common.php");

$mb_id = isset($_POST['mb_id']) ? trim((string) $_POST['mb_id']) : '';
$product_type = isset($_POST['product_type']) ? trim((string) $_POST['product_type']) : '';
$request_amount_raw = isset($_POST['request_amount']) ? preg_replace('/[^0-9]/', '', (string) $_POST['request_amount']) : '';
$bank_account_id = isset($_POST['bank_account_id']) ? (int) $_POST['bank_account_id'] : 0;
$depositor_name = isset($_POST['depositor_name']) ? trim((string) $_POST['depositor_name']) : '';
$sms_send = isset($_POST['sms_send']) && (string) $_POST['sms_send'] === '1' ? 1 : 0;

$login_mb_id = isset($member['mb_id']) ? trim((string) $member['mb_id']) : '';
$login_level = isset($member['mb_level']) ? (int) $member['mb_level'] : 0;

if ($login_mb_id === '' || $login_level >= LOTTO_ROLE_ADMIN) {
    alert('무통장 승인요청은 직원/팀장 권한에서만 등록할 수 있습니다.');
    exit;
}

if ($mb_id === '') {
    alert('회원정보가 올바르지 않습니다.');
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

$bank_account_text = trim(
    (string) $bank_account['bank_name'].' '.
    (string) $bank_account['account_number'].' '.
    (string) $bank_account['account_holder']
);

$mb_id_sql = sql_real_escape_string($mb_id);
$target_member = sql_fetch(
    "select mb_id, mb_name, mb_hp
       from g5_member
      where mb_id = '{$mb_id_sql}'
      limit 1"
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
$assigned_staff_mb_id = isset($assignment['staff_mb_id']) ? trim((string) $assignment['staff_mb_id']) : '';

if ($assigned_staff_mb_id === '') {
    alert('담당자가 배정되지 않은 회원입니다.');
    exit;
}

$allowed_staff_ids = array($login_mb_id);
if ($login_level === LOTTO_ROLE_STAFF2 || $login_level === LOTTO_ROLE_TEAM_LEADER) {
    $child_staff_ids = lottoGetDirectChildStaffIds($login_mb_id);
    foreach ($child_staff_ids as $child_staff_id) {
        $allowed_staff_ids[] = (string) $child_staff_id;
    }
}
$allowed_staff_ids = array_values(array_unique(array_filter($allowed_staff_ids)));

if (!in_array($assigned_staff_mb_id, $allowed_staff_ids, true)) {
    alert('접근 권한이 없는 회원입니다.');
    exit;
}

$request_no = 'PAY'.date('YmdHis').strtoupper(bin2hex(random_bytes(3)));
$request_no_sql = sql_real_escape_string($request_no);
$staff_mb_id_sql = sql_real_escape_string($assigned_staff_mb_id);
$requested_by_sql = sql_real_escape_string($login_mb_id);
$product_type_sql = sql_real_escape_string($product_type);
$member_phone_sql = sql_real_escape_string((string) $target_member['mb_hp']);
$bank_account_sql = sql_real_escape_string($bank_account_text);
$depositor_name_sql = sql_real_escape_string($depositor_name);

$sql = "insert into l_payment_request set
            request_no = '{$request_no_sql}',
            mb_id = '{$mb_id_sql}',
            staff_mb_id = '{$staff_mb_id_sql}',
            requested_by = '{$requested_by_sql}',
            payment_method = '무통장',
            product_type = '{$product_type_sql}',
            request_amount = {$request_amount},
            request_status = '승인대기',
            member_phone = '{$member_phone_sql}',
            bank_account = '{$bank_account_sql}',
            depositor_name = '{$depositor_name_sql}',
            sms_send = {$sms_send},
            created_at = now(),
            updated_at = now()";
sql_query($sql);

fnSetLog($login_mb_id, $mb_id.'님의 무통장 결제 승인요청을 등록하였습니다.');

if ($sms_send === 1) {
    $message = (string) $target_member['mb_name'].'고객님 입금 안내 '.$bank_account_text.' / '.number_format($request_amount).'원';
    fnSendOneshot($config['cf_oneshot_tel'], (string) $target_member['mb_hp'], $message, '');
}

alert(
    '무통장 승인요청이 등록되었습니다.',
    G5_LADMIN_URL.'/member/pop.member.php?mb_id='.urlencode(base64_encode($mb_id))
);
?>
