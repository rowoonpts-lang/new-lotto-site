<?php
include_once("_common.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    alert('올바른 요청이 아닙니다.');
    exit;
}

$mb_id = isset($_POST['mb_id']) ? trim((string) $_POST['mb_id']) : '';
$start_date = isset($_POST['start_date']) ? trim((string) $_POST['start_date']) : '';
$end_date = isset($_POST['end_date']) ? trim((string) $_POST['end_date']) : '';

if ($mb_id === '') {
    alert('회원정보가 올바르지 않습니다.');
    exit;
}

$mb_id_sql = sql_real_escape_string($mb_id);
$member_row = sql_fetch(
    "select a.mb_id, a.mb_name, c.staff_mb_id
       from g5_member a
       left join l_member_assignment c on c.mb_id = a.mb_id
      where a.mb_id = '{$mb_id_sql}'
      limit 1",
    false
);

if (!$member_row || empty($member_row['mb_id'])) {
    alert('회원정보를 찾을 수 없습니다.');
    exit;
}

$login_mb_id = isset($member['mb_id']) ? trim((string) $member['mb_id']) : '';
$login_level = isset($member['mb_level']) ? (int) $member['mb_level'] : 0;
$can_view_all = lottoCanViewAllMembers($login_level);

if (!$can_view_all) {
    $allowed_staff_ids = array($login_mb_id);

    if ($login_level === LOTTO_ROLE_STAFF2 || $login_level === LOTTO_ROLE_TEAM_LEADER) {
        $child_staff_ids = lottoGetDirectChildStaffIds($login_mb_id);
        foreach ($child_staff_ids as $child_staff_id) {
            $allowed_staff_ids[] = (string) $child_staff_id;
        }
    }

    $allowed_staff_ids = array_values(array_unique(array_filter($allowed_staff_ids)));
    $assigned_staff_mb_id = isset($member_row['staff_mb_id']) ? trim((string) $member_row['staff_mb_id']) : '';

    if ($assigned_staff_mb_id === '' || !in_array($assigned_staff_mb_id, $allowed_staff_ids, true)) {
        alert('접근 권한이 없는 회원입니다.');
        exit;
    }
}

if (($start_date === '' && $end_date !== '') || ($start_date !== '' && $end_date === '')) {
    alert('유료기간 시작일과 종료일을 모두 선택해주세요.');
    exit;
}

if ($start_date !== '' && $end_date !== '') {
    $date_pattern = '/^\d{4}-\d{2}-\d{2}$/';
    if (!preg_match($date_pattern, $start_date) || !preg_match($date_pattern, $end_date)) {
        alert('유료기간 날짜가 올바르지 않습니다.');
        exit;
    }

    $start_parts = explode('-', $start_date);
    $end_parts = explode('-', $end_date);

    if (
        count($start_parts) !== 3 || !checkdate((int) $start_parts[1], (int) $start_parts[2], (int) $start_parts[0]) ||
        count($end_parts) !== 3 || !checkdate((int) $end_parts[1], (int) $end_parts[2], (int) $end_parts[0])
    ) {
        alert('유료기간 날짜가 올바르지 않습니다.');
        exit;
    }

    if ($end_date < $start_date) {
        alert('유료기간 종료일은 시작일보다 빠를 수 없습니다.');
        exit;
    }
}

$etc_row = sql_fetch("select mb_id from g5_member_etc where mb_id = '{$mb_id_sql}' limit 1", false);
if (!$etc_row || empty($etc_row['mb_id'])) {
    alert('회원 기간정보를 찾을 수 없습니다.');
    exit;
}

if ($start_date === '' && $end_date === '') {
    $period_sql = "start_date = NULL, end_date = NULL";
} else {
    $start_date_sql = sql_real_escape_string($start_date);
    $end_date_sql = sql_real_escape_string($end_date);
    $period_sql = "start_date = '{$start_date_sql}', end_date = '{$end_date_sql}'";
}

if (!sql_query(
    "update g5_member_etc
        set {$period_sql}
      where mb_id = '{$mb_id_sql}'",
    false
)) {
    alert('유료기간 저장 중 오류가 발생했습니다.');
    exit;
}

if (function_exists('fnSetLog')) {
    $log_period = ($start_date === '' && $end_date === '')
        ? '기간 해제'
        : $start_date.' ~ '.$end_date;
    fnSetLog($login_mb_id, $member_row['mb_name'].' 회원의 유료기간을 '.$log_period.'로 변경하였습니다.');
}

alert(
    '유료기간이 저장되었습니다.',
    G5_LADMIN_URL.'/member/pop.member.php?mb_id='.urlencode(base64_encode($mb_id))
);
