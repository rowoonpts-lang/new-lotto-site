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
    alert('관리자 이상만 처리할 수 있습니다.', G5_LADMIN_URL);
    exit;
}

lottoConfigTokenCheck();

$sender_phone = isset($_POST['sender_phone'])
    ? trim((string) $_POST['sender_phone'])
    : '';

$combination_header = isset($_POST['combination_header'])
    ? trim((string) $_POST['combination_header'])
    : '';

$combination_footer = isset($_POST['combination_footer'])
    ? trim((string) $_POST['combination_footer'])
    : '';

$winner_header = isset($_POST['winner_header'])
    ? trim((string) $_POST['winner_header'])
    : '';

$winner_footer = isset($_POST['winner_footer'])
    ? trim((string) $_POST['winner_footer'])
    : '';

if (
    $sender_phone !== ''
    && !preg_match('/^[0-9-]+$/', $sender_phone)
) {
    alert('발신번호는 숫자와 하이픈만 입력할 수 있습니다.');
    exit;
}

$normalized_sender = preg_replace(
    '/[^0-9]/',
    '',
    $sender_phone
);

if (
    $normalized_sender !== ''
    && (
        strlen($normalized_sender) < 8
        || strlen($normalized_sender) > 15
    )
) {
    alert('발신번호 형식이 올바르지 않습니다.');
    exit;
}

$text_fields = array(
    'combination_header' => $combination_header,
    'combination_footer' => $combination_footer,
    'winner_header' => $winner_header,
    'winner_footer' => $winner_footer,
);

foreach ($text_fields as $value) {
    $length = function_exists('mb_strlen')
        ? mb_strlen($value, 'UTF-8')
        : strlen($value);

    if ($length > 1000) {
        alert('SMS 고정문구는 각 항목당 1000자를 넘을 수 없습니다.');
        exit;
    }
}

$existing = sql_fetch(
    "select lsc_id
       from l_sms_config
      where lsc_id = 1
      limit 1",
    false
);

if (empty($existing['lsc_id'])) {
    alert('SMS 설정 정보를 찾을 수 없습니다.');
    exit;
}

$sender_phone_sql = sql_real_escape_string(
    $normalized_sender
);

$combination_header_sql = sql_real_escape_string(
    $combination_header
);

$combination_footer_sql = sql_real_escape_string(
    $combination_footer
);

$winner_header_sql = sql_real_escape_string(
    $winner_header
);

$winner_footer_sql = sql_real_escape_string(
    $winner_footer
);

$login_mb_id_sql = sql_real_escape_string(
    $login_mb_id
);

$sql = "
    update l_sms_config
       set sender_phone = '{$sender_phone_sql}',
           combination_header = '{$combination_header_sql}',
           combination_footer = '{$combination_footer_sql}',
           winner_header = '{$winner_header_sql}',
           winner_footer = '{$winner_footer_sql}',
           updated_by = '{$login_mb_id_sql}',
           updated_at = now()
     where lsc_id = 1
";

if (sql_query($sql, false) === false) {
    alert('SMS 설정 저장에 실패했습니다.');
    exit;
}

if (function_exists('fnSetLog')) {
    fnSetLog(
        $login_mb_id,
        'SMS 환경설정을 수정하였습니다.'
    );
}

alert(
    'SMS 설정이 저장되었습니다.',
    G5_LADMIN_URL.'/config/sms.config.php'
);
?>
