<?php
include_once("_common.php");
include_once(G5_PATH.'/include/lotto_card_security.lib.php');

$login_mb_id = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$login_level = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if ($login_mb_id === '' || $login_level >= LOTTO_ROLE_ADMIN) {
    alert('결제 승인요청 수정 권한이 없습니다.', G5_LADMIN_URL);
    exit;
}

if (
    !isset($_SERVER['REQUEST_METHOD'])
    || $_SERVER['REQUEST_METHOD'] !== 'POST'
) {
    alert('올바른 요청이 아닙니다.', G5_LADMIN_URL);
    exit;
}

$lpr_id = isset($_POST['lpr_id'])
    ? (int) $_POST['lpr_id']
    : 0;

$card_company = isset($_POST['card_company'])
    ? trim((string) $_POST['card_company'])
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

if ($lpr_id < 1) {
    alert('결제 승인요청 정보가 올바르지 않습니다.');
    exit;
}

if ($card_company === '' || strlen($card_company) > 100) {
    alert('카드사를 올바르게 입력하세요.');
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

if ($installment_months < 0 || $installment_months > 12) {
    alert('할부개월 정보가 올바르지 않습니다.');
    exit;
}

$entered_count = 0;

if ($card_number !== '') {
    $entered_count++;
}

if ($card_expiry !== '') {
    $entered_count++;
}

if ($card_password_prefix !== '') {
    $entered_count++;
}

if ($birth_date !== '') {
    $entered_count++;
}

if ($entered_count > 0 && $entered_count < 4) {
    alert('카드정보를 변경하려면 민감정보를 모두 입력하세요.');
    exit;
}

if ($entered_count === 4) {
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
}

if (!sql_query('START TRANSACTION', false)) {
    alert('카드 승인요청 수정을 시작할 수 없습니다.');
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

if (!in_array(
    (string) $request['request_status'],
    array('승인대기', '승인거절'),
    true
)) {
    sql_query('ROLLBACK', false);
    alert('현재 상태에서는 결제 승인요청을 수정할 수 없습니다.');
    exit;
}

if ((string) $request['payment_method'] !== '신용카드') {
    sql_query('ROLLBACK', false);
    alert('신용카드 승인요청이 아닙니다.');
    exit;
}

$secret_result = sql_query(
    "select *
       from l_payment_card_secret
      where lpr_id = {$lpr_id}
      for update",
    false
);

if (!$secret_result) {
    sql_query('ROLLBACK', false);
    alert('카드 민감정보 상태를 확인할 수 없습니다.');
    exit;
}

$secret = sql_fetch_array($secret_result);
$has_secret = $secret && !empty($secret['lpcs_id']);

if (!$has_secret && $entered_count !== 4) {
    sql_query('ROLLBACK', false);
    alert('카드 민감정보를 모두 다시 입력해야 합니다.');
    exit;
}

$card_company_sql = sql_real_escape_string($card_company);
$product_type_sql = sql_real_escape_string($product_type);

if (!sql_query(
    "update l_payment_request set
        card_company = '{$card_company_sql}',
        installment_months = {$installment_months},
        product_type = '{$product_type_sql}',
        request_amount = {$request_amount},
        request_status = '승인대기',
        rejected_by = '',
        rejected_at = null,
        reject_reason = '',
        updated_at = now()
      where lpr_id = {$lpr_id}",
    false
)) {
    sql_query('ROLLBACK', false);
    alert('카드 승인요청 수정 중 오류가 발생했습니다.');
    exit;
}

if ($entered_count === 4) {
    try {
        $encrypted = lottoCardEncryptPayload(array(
            'card_number' => $card_number,
            'card_expiry' => $card_expiry,
            'card_password_prefix' => $card_password_prefix,
            'birth_date' => $birth_date,
        ));
    } catch (Throwable $e) {
        sql_query('ROLLBACK', false);
        alert('카드정보 암호화 중 오류가 발생했습니다.');
        exit;
    }

    $encrypted_payload_sql = sql_real_escape_string(
        (string) $encrypted['payload']
    );

    $card_last4_sql = sql_real_escape_string(
        substr($card_number, -4)
    );

    $key_version = (int) $encrypted['key_version'];

    if ($has_secret) {
        $secret_saved = sql_query(
            "update l_payment_card_secret set
                encrypted_payload = '{$encrypted_payload_sql}',
                card_last4 = '{$card_last4_sql}',
                key_version = {$key_version},
                created_at = now(),
                expires_at = null,
                cleared_at = null
              where lpr_id = {$lpr_id}",
            false
        );
    } else {
        $secret_saved = sql_query(
            "insert into l_payment_card_secret set
                lpr_id = {$lpr_id},
                encrypted_payload = '{$encrypted_payload_sql}',
                card_last4 = '{$card_last4_sql}',
                key_version = {$key_version},
                created_at = now(),
                expires_at = null,
                cleared_at = null",
            false
        );
    }

    if (!$secret_saved) {
        sql_query('ROLLBACK', false);
        alert('카드 민감정보 저장 중 오류가 발생했습니다.');
        exit;
    }
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
    alert('카드 승인요청 수정 저장에 실패했습니다.');
    exit;
}

if (function_exists('fnSetLog')) {
    fnSetLog(
        $login_mb_id,
        (string) $request['mb_id'].'님의 카드 결제 승인요청을 수정하였습니다.'
    );
}

alert(
    '수정한 내용으로 다시 승인요청 되었습니다.',
    G5_LADMIN_URL.'/member/pop.member.php?mb_id='.
        urlencode(base64_encode((string) $request['mb_id']))
);
