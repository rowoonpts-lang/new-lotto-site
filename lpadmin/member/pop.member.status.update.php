<?php
include_once("_common.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    alert('올바른 요청이 아닙니다.');
    exit;
}

$mb_id = isset($_POST['mb_id'])
    ? trim((string) $_POST['mb_id'])
    : '';

$action = isset($_POST['action'])
    ? trim((string) $_POST['action'])
    : '';

if ($mb_id === '') {
    alert('회원정보가 올바르지 않습니다.');
    exit;
}

if (!in_array($action, array('hold', 'start', 'leave'), true)) {
    alert('처리 유형이 올바르지 않습니다.');
    exit;
}

$mb_id_sql = sql_real_escape_string($mb_id);

$target = sql_fetch(
    "select a.mb_id,
            a.mb_name,
            a.mb_leave_date,
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

if ($action === 'leave') {
    if (trim((string) $target['mb_leave_date']) !== '') {
        alert('이미 탈퇴 처리된 회원입니다.');
        exit;
    }

    $leave_date = date('Ymd', G5_SERVER_TIME);

    if (!sql_query(
        "update g5_member
            set mb_leave_date = '{$leave_date}'
          where mb_id = '{$mb_id_sql}'",
        false
    )) {
        alert('회원 탈퇴 처리 중 오류가 발생했습니다.');
        exit;
    }

    if (function_exists('fnSetLog')) {
        fnSetLog(
            $login_mb_id,
            $target['mb_name'].' 회원을 탈퇴 처리하였습니다.'
        );
    }

    alert(
        '회원이 탈퇴 처리되었습니다.',
        G5_LADMIN_URL
        .'/member/pop.member.php?mb_id='
        .urlencode(base64_encode($mb_id))
    );

    exit;
}

$etc_row = sql_fetch(
    "select mb_id,
            start_date,
            end_date,
            stop_start_date,
            left_day,
            hold_datetime
       from g5_member_etc
      where mb_id = '{$mb_id_sql}'
      limit 1",
    false
);

if (!$etc_row || empty($etc_row['mb_id'])) {
    alert('회원 이용정보를 찾을 수 없습니다.');
    exit;
}

if ($action === 'hold') {
    if (!empty($etc_row['hold_datetime'])) {
        alert('이미 일시정지 상태입니다.');
        exit;
    }

    $end_date = trim((string) $etc_row['end_date']);

    if ($end_date === '') {
        alert('이용기간 종료일이 없어 일시정지할 수 없습니다.');
        exit;
    }

    $today = date('Y-m-d', G5_SERVER_TIME);
    $remaining_days = (int) floor(
        (strtotime($end_date) - strtotime($today)) / 86400
    );

    if ($remaining_days < 1) {
        alert('남은 이용기간이 없어 일시정지할 수 없습니다.');
        exit;
    }

    if (!sql_query(
        "update g5_member_etc
            set stop_start_date = start_date,
                left_day = {$remaining_days},
                hold_datetime = now(),
                start_date = NULL,
                end_date = NULL
          where mb_id = '{$mb_id_sql}'",
        false
    )) {
        alert('일시정지 처리 중 오류가 발생했습니다.');
        exit;
    }

    if (function_exists('fnSetLog')) {
        fnSetLog(
            $login_mb_id,
            $target['mb_name'].' 회원을 일시정지 처리하였습니다.'
        );
    }

    $message = '회원 이용기간이 일시정지되었습니다.';
}

if ($action === 'start') {
    if (empty($etc_row['hold_datetime'])) {
        alert('일시정지 상태가 아닙니다.');
        exit;
    }

    $remaining_days = (int) $etc_row['left_day'];
    $stop_start_date = trim((string) $etc_row['stop_start_date']);

    if ($remaining_days < 1) {
        alert('복원할 남은 이용기간이 없습니다.');
        exit;
    }

    if ($stop_start_date === '') {
        alert('일시정지 시작 정보를 찾을 수 없습니다.');
        exit;
    }

    $new_end_date = date(
        'Y-m-d',
        strtotime('+'.$remaining_days.' day', G5_SERVER_TIME)
    );

    $stop_start_date_sql = sql_real_escape_string($stop_start_date);

    if (!sql_query(
        "update g5_member_etc
            set left_day = 0,
                hold_datetime = NULL,
                start_date = '{$stop_start_date_sql}',
                end_date = '{$new_end_date}'
          where mb_id = '{$mb_id_sql}'",
        false
    )) {
        alert('일시정지 해제 중 오류가 발생했습니다.');
        exit;
    }

    if (function_exists('fnSetLog')) {
        fnSetLog(
            $login_mb_id,
            $target['mb_name'].' 회원의 일시정지를 해제하였습니다.'
        );
    }

    $message = '회원 이용기간 일시정지가 해제되었습니다.';
}

alert(
    $message,
    G5_LADMIN_URL
    .'/member/pop.member.php?mb_id='
    .urlencode(base64_encode($mb_id))
);
