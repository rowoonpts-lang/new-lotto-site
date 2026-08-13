<?php
include_once("_common.php");

$login_mb_id = isset($member['mb_id']) ? trim((string) $member['mb_id']) : '';
$login_level = isset($member['mb_level']) ? (int) $member['mb_level'] : 0;

if ($login_mb_id === '' || $login_level < LOTTO_ROLE_ADMIN) {
    alert('관리자 이상만 처리할 수 있습니다.', G5_LADMIN_URL);
    exit;
}

$mode = isset($_POST['mode']) ? trim((string) $_POST['mode']) : '';
$lpba_id = isset($_POST['lpba_id']) ? (int) $_POST['lpba_id'] : 0;
$bank_name = isset($_POST['bank_name']) ? trim((string) $_POST['bank_name']) : '';
$account_number = isset($_POST['account_number']) ? trim((string) $_POST['account_number']) : '';
$account_holder = isset($_POST['account_holder']) ? trim((string) $_POST['account_holder']) : '';
$sort_order = isset($_POST['sort_order']) ? max(0, (int) $_POST['sort_order']) : 0;
$is_active = isset($_POST['is_active']) && (string) $_POST['is_active'] === '0' ? 0 : 1;

if ($mode !== 'create' && $mode !== 'update') {
    alert('처리 방식이 올바르지 않습니다.');
    exit;
}

if ($mode === 'update' && $lpba_id < 1) {
    alert('수정할 계좌정보가 올바르지 않습니다.');
    exit;
}

if ($bank_name === '' || $account_number === '' || $account_holder === '') {
    alert('은행명, 계좌번호, 예금주를 모두 입력하세요.');
    exit;
}

if (strlen($bank_name) > 100 || strlen($account_number) > 100 || strlen($account_holder) > 100) {
    alert('입력값이 허용 길이를 초과했습니다.');
    exit;
}

$bank_name_sql = sql_real_escape_string($bank_name);
$account_number_sql = sql_real_escape_string($account_number);
$account_holder_sql = sql_real_escape_string($account_holder);
$login_mb_id_sql = sql_real_escape_string($login_mb_id);

$duplicate_sql = "select lpba_id
                    from l_payment_bank_account
                   where bank_name = '{$bank_name_sql}'
                     and account_number = '{$account_number_sql}'";
if ($mode === 'update') {
    $duplicate_sql .= " and lpba_id != {$lpba_id}";
}
$duplicate_sql .= " limit 1";
$duplicate = sql_fetch($duplicate_sql, false);

if (!empty($duplicate['lpba_id'])) {
    alert('이미 등록된 은행명과 계좌번호입니다.');
    exit;
}

if ($mode === 'create') {
    $sql = "insert into l_payment_bank_account set
                bank_name = '{$bank_name_sql}',
                account_number = '{$account_number_sql}',
                account_holder = '{$account_holder_sql}',
                is_active = {$is_active},
                sort_order = {$sort_order},
                created_by = '{$login_mb_id_sql}',
                updated_by = '{$login_mb_id_sql}',
                created_at = now(),
                updated_at = now()";
    sql_query($sql);
    fnSetLog($login_mb_id, '입금계좌를 등록하였습니다. 은행: '.$bank_name);
    $message = '입금계좌가 등록되었습니다.';
} else {
    $existing = sql_fetch(
        "select lpba_id
           from l_payment_bank_account
          where lpba_id = {$lpba_id}
          limit 1",
        false
    );

    if (empty($existing['lpba_id'])) {
        alert('수정할 계좌를 찾을 수 없습니다.');
        exit;
    }

    $sql = "update l_payment_bank_account set
                bank_name = '{$bank_name_sql}',
                account_number = '{$account_number_sql}',
                account_holder = '{$account_holder_sql}',
                is_active = {$is_active},
                sort_order = {$sort_order},
                updated_by = '{$login_mb_id_sql}',
                updated_at = now()
            where lpba_id = {$lpba_id}";
    sql_query($sql);
    fnSetLog($login_mb_id, '입금계좌를 수정하였습니다. 계좌ID: '.$lpba_id);
    $message = '입금계좌가 수정되었습니다.';
}

alert($message, G5_LADMIN_URL.'/payment/bank.account.php');
?>
