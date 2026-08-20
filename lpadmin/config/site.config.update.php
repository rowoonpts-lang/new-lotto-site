<?php
include_once("_common.php");

$login_mb_id = isset($member['mb_id']) ? trim((string) $member['mb_id']) : '';
$login_level = isset($member['mb_level']) ? (int) $member['mb_level'] : 0;

if ($login_mb_id === '' || $login_level < LOTTO_ROLE_ADMIN) {
    alert('관리자 이상만 처리할 수 있습니다.', G5_LADMIN_URL);
    exit;
}

lottoConfigTokenCheck();

$fields = array(
    'brand_name',
    'company_name',
    'representative_name',
    'business_number',
    'mail_order_number',
    'privacy_manager',
    'company_address',
    'contact_phone',
    'contact_email',
    'contact_hours',
    'contact_closed',
    'common_notice',
    'copyright_name'
);

$values = array();

foreach ($fields as $field) {
    $values[$field] = isset($_POST[$field])
        ? trim((string) $_POST[$field])
        : '';
}

if ($values['brand_name'] === '') {
    alert('브랜드명을 입력하세요.');
    exit;
}

if ($values['company_name'] === '') {
    alert('회사명을 입력하세요.');
    exit;
}

if (
    $values['contact_email'] !== ''
    && !filter_var($values['contact_email'], FILTER_VALIDATE_EMAIL)
) {
    alert('이메일 형식이 올바르지 않습니다.');
    exit;
}

$max_lengths = array(
    'brand_name' => 100,
    'company_name' => 150,
    'representative_name' => 100,
    'business_number' => 50,
    'mail_order_number' => 100,
    'privacy_manager' => 100,
    'company_address' => 255,
    'contact_phone' => 50,
    'contact_email' => 150,
    'contact_hours' => 255,
    'contact_closed' => 255,
    'copyright_name' => 150
);

foreach ($max_lengths as $field => $max_length) {
    $length = function_exists('mb_strlen')
        ? mb_strlen($values[$field], 'UTF-8')
        : strlen($values[$field]);

    if ($length > $max_length) {
        alert('입력값이 허용 길이를 초과했습니다.');
        exit;
    }
}

$sql_values = array();

foreach ($values as $field => $value) {
    $sql_values[$field] = sql_real_escape_string($value);
}

$login_mb_id_sql = sql_real_escape_string($login_mb_id);

$existing = sql_fetch("
    select lsc_id
      from l_site_config
     where lsc_id = 1
     limit 1
", false);

if (empty($existing['lsc_id'])) {
    alert('홈페이지 설정 정보를 찾을 수 없습니다.');
    exit;
}

$sql = "
    update l_site_config set
        brand_name = '{$sql_values['brand_name']}',
        company_name = '{$sql_values['company_name']}',
        representative_name = '{$sql_values['representative_name']}',
        business_number = '{$sql_values['business_number']}',
        mail_order_number = '{$sql_values['mail_order_number']}',
        privacy_manager = '{$sql_values['privacy_manager']}',
        company_address = '{$sql_values['company_address']}',
        contact_phone = '{$sql_values['contact_phone']}',
        contact_email = '{$sql_values['contact_email']}',
        contact_hours = '{$sql_values['contact_hours']}',
        contact_closed = '{$sql_values['contact_closed']}',
        common_notice = '{$sql_values['common_notice']}',
        copyright_name = '{$sql_values['copyright_name']}',
        updated_by = '{$login_mb_id_sql}',
        updated_at = now()
    where lsc_id = 1
";

sql_query($sql);

if (function_exists('fnSetLog')) {
    fnSetLog($login_mb_id, '홈페이지 환경설정을 수정하였습니다.');
}

alert(
    '홈페이지 설정이 저장되었습니다.',
    G5_LADMIN_URL.'/config/site.config.php'
);
?>
