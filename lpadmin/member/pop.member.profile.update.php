<?php
include_once("_common.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    alert('올바른 요청이 아닙니다.');
    exit;
}

$mb_id = isset($_POST['mb_id'])
    ? trim((string) $_POST['mb_id'])
    : '';

$mb_name = isset($_POST['mb_name'])
    ? trim((string) $_POST['mb_name'])
    : '';

$mb_hp = isset($_POST['mb_hp'])
    ? trim((string) $_POST['mb_hp'])
    : '';

$mb_type = isset($_POST['mb_type'])
    ? trim((string) $_POST['mb_type'])
    : '';

$mb_password = isset($_POST['mb_password'])
    ? (string) $_POST['mb_password']
    : '';

$free_num_date = isset($_POST['free_num_date'])
    ? trim((string) $_POST['free_num_date'])
    : '';

$free_num_qty = isset($_POST['free_num_qty'])
    ? (int) $_POST['free_num_qty']
    : 0;

$distribution_day = isset($_POST['distribution_day'])
    ? trim((string) $_POST['distribution_day'])
    : 'thur';

$distribution_qty = isset($_POST['distribution_qty'])
    ? (int) $_POST['distribution_qty']
    : 20;

$distribution_days = array(
    'mon' => 'num_mon',
    'tue' => 'num_tue',
    'wed' => 'num_wed',
    'thur' => 'num_thur',
    'fri' => 'num_fri',
);

if ($mb_id === '' || $mb_name === '' || $mb_hp === '') {
    alert('회원정보를 확인해주세요.');
    exit;
}

if ($free_num_qty < 0) {
    alert('무료회원 조합 수량은 0 이상이어야 합니다.');
    exit;
}

if (!isset($distribution_days[$distribution_day])) {
    alert('로또 배분 요일이 올바르지 않습니다.');
    exit;
}

if ($distribution_qty < 1) {
    alert('로또 배분 수량은 1개 이상이어야 합니다.');
    exit;
}

if ($free_num_date !== '') {
    $date_pattern = '/^\d{4}-\d{2}-\d{2}$/';

    if (!preg_match($date_pattern, $free_num_date)) {
        alert('무료회원 조합 종료일이 올바르지 않습니다.');
        exit;
    }

    $date_parts = explode('-', $free_num_date);

    if (
        count($date_parts) !== 3
        || !checkdate(
            (int) $date_parts[1],
            (int) $date_parts[2],
            (int) $date_parts[0]
        )
    ) {
        alert('무료회원 조합 종료일이 올바르지 않습니다.');
        exit;
    }
}

$allowed_member_types = fnGetType();

if (!in_array($mb_type, $allowed_member_types, true)) {
    alert('회원등급이 올바르지 않습니다.');
    exit;
}

$mb_id_sql = sql_real_escape_string($mb_id);

$target = sql_fetch(
    "select a.mb_id,
            a.mb_name,
            a.mb_hp,
            a.mb_level,
            a.mb_type as current_mb_type,
            c.staff_mb_id
       from g5_member a
       left join l_member_assignment c on c.mb_id = a.mb_id
      where a.mb_id = '{$mb_id_sql}'
      limit 1",
    false
);

if (!$target || empty($target['mb_id'])) {
    alert('회원정보를 찾을 수 없습니다.');
    exit;
}

$login_mb_id = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$login_level = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

$can_view_all = lottoCanViewAllMembers($login_level);

if (!$can_view_all) {
    $allowed_staff_ids = lottoGetAccessibleStaffIds(
    $login_mb_id,
    $login_level
);

    $assigned_staff_mb_id = isset($target['staff_mb_id'])
        ? trim((string) $target['staff_mb_id'])
        : '';

    if (
        $assigned_staff_mb_id === ''
        || !in_array(
            $assigned_staff_mb_id,
            $allowed_staff_ids,
            true
        )
    ) {
        alert('접근 권한이 없는 회원입니다.');
        exit;
    }
}

$mb_hp_sql = sql_real_escape_string($mb_hp);

$duplicate_hp = sql_fetch(
    "select mb_id
       from g5_member
      where mb_hp = '{$mb_hp_sql}'
        and mb_id != '{$mb_id_sql}'
      limit 1",
    false
);

if (!empty($duplicate_hp['mb_id'])) {
    alert('이미 사용 중인 휴대폰번호입니다.');
    exit;
}

$mb_name_sql = sql_real_escape_string($mb_name);
$mb_type_sql = sql_real_escape_string($mb_type);

$member_set = array(
    "mb_name = '{$mb_name_sql}'",
    "mb_hp = '{$mb_hp_sql}'",
    "mb_type = '{$mb_type_sql}'",
);

if ($mb_password !== '') {
    $encrypted_password = get_encrypt_string($mb_password);
    $encrypted_password_sql = sql_real_escape_string($encrypted_password);

    $member_set[] = "mb_password = '{$encrypted_password_sql}'";
}

if (!sql_query(
    "update g5_member
        set ".implode(', ', $member_set)."
      where mb_id = '{$mb_id_sql}'",
    false
)) {
    alert('회원 기본정보 저장 중 오류가 발생했습니다.');
    exit;
}

$etc_row = sql_fetch(
    "select mb_id
       from g5_member_etc
      where mb_id = '{$mb_id_sql}'
      limit 1",
    false
);

if (!$etc_row || empty($etc_row['mb_id'])) {
    alert('회원 부가정보를 찾을 수 없습니다.');
    exit;
}

$etc_set = array();

if ($mb_type === '무료회원') {
    $free_num_date_sql = sql_real_escape_string(
        $free_num_date
    );

    $etc_set[] =
        "free_num_date = '{$free_num_date_sql}'";
    $etc_set[] =
        "free_num_qty = {$free_num_qty}";
} elseif ($mb_type !== '직원' && $mb_type !== '') {
    $distribution_values = array(
        'num_mon' => 0,
        'num_tue' => 0,
        'num_wed' => 0,
        'num_thur' => 0,
        'num_fri' => 0,
        'num_sat' => 0,
    );

    $distribution_column =
        $distribution_days[$distribution_day];

    $distribution_values[$distribution_column] =
        $distribution_qty;

    foreach (
        $distribution_values
        as $column_name => $column_value
    ) {
        $etc_set[] =
            $column_name . ' = ' . (int) $column_value;
    }
}

if (
    !empty($etc_set)
    && !sql_query(
        "update g5_member_etc
            set ".implode(', ', $etc_set)."
          where mb_id = '{$mb_id_sql}'",
        false
    )
) {
    alert('회원 부가정보 저장 중 오류가 발생했습니다.');
    exit;
}

if (function_exists('fnSetLog')) {
    fnSetLog(
        $login_mb_id,
        $mb_name.' 회원의 기본정보를 수정하였습니다.'
    );
}

alert(
    '회원정보가 저장되었습니다.',
    G5_LADMIN_URL
    .'/member/pop.member.php?mb_id='
    .urlencode(base64_encode($mb_id))
);
