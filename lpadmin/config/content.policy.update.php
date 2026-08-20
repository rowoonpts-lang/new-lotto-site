<?php
include_once("_common.php");

$login_mb_id = isset($member['mb_id']) ? trim((string) $member['mb_id']) : '';
$login_level = isset($member['mb_level']) ? (int) $member['mb_level'] : 0;

if ($login_mb_id === '' || $login_level < LOTTO_ROLE_ADMIN) {
    alert('관리자 이상만 처리할 수 있습니다.', G5_LADMIN_URL);
    exit;
}

lottoConfigTokenCheck();

$co_id = isset($_POST['co_id'])
    ? trim((string) $_POST['co_id'])
    : '';

$allowed_content = array(
    'provision' => array(
        'name' => '이용약관',
        'return_url' => G5_LADMIN_URL.'/config/terms.php'
    ),
    'privacy' => array(
        'name' => '개인정보처리방침',
        'return_url' => G5_LADMIN_URL.'/config/privacy.php'
    )
);

if (!isset($allowed_content[$co_id])) {
    alert('수정할 수 없는 콘텐츠입니다.', G5_LADMIN_URL);
    exit;
}

$co_content = isset($_POST['co_content'])
    ? trim((string) $_POST['co_content'])
    : '';

if ($co_content === '') {
    alert($allowed_content[$co_id]['name'].' 내용을 입력하세요.');
    exit;
}

$co_id_sql = sql_real_escape_string($co_id);
$co_content_sql = sql_real_escape_string($co_content);

$existing = sql_fetch("
    select co_id
      from g5_content
     where co_id = '{$co_id_sql}'
     limit 1
", false);

if (empty($existing['co_id'])) {
    alert(
        $allowed_content[$co_id]['name'].' 정보를 찾을 수 없습니다.',
        G5_LADMIN_URL
    );
    exit;
}

sql_query("
    update g5_content
       set co_content = '{$co_content_sql}',
           co_html = 0
     where co_id = '{$co_id_sql}'
");

if (function_exists('g5_delete_cache_by_prefix')) {
    g5_delete_cache_by_prefix('content-'.$co_id);
}

if (function_exists('fnSetLog')) {
    fnSetLog(
        $login_mb_id,
        $allowed_content[$co_id]['name'].'을 수정하였습니다.'
    );
}

alert(
    $allowed_content[$co_id]['name'].'이 저장되었습니다.',
    $allowed_content[$co_id]['return_url']
);
?>
