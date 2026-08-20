<?php
include_once("_common.php");

$login_mb_id = isset($member['mb_id']) ? trim((string) $member['mb_id']) : '';
$login_level = isset($member['mb_level']) ? (int) $member['mb_level'] : 0;

if ($login_mb_id === '' || $login_level < LOTTO_ROLE_ADMIN) {
    alert('관리자 이상만 처리할 수 있습니다.', G5_LADMIN_URL);
    exit;
}

lottoConfigTokenCheck();

$w = isset($_POST['w']) ? trim((string) $_POST['w']) : '';
$nw_id = isset($_POST['nw_id']) ? (int) $_POST['nw_id'] : 0;

if ($w === 'd') {
    if ($nw_id < 1) {
        alert('잘못된 팝업 번호입니다.', 'popup.list.php');
        exit;
    }

    $existing = sql_fetch("
        select nw_id, nw_subject
          from {$g5['new_win_table']}
         where nw_id = {$nw_id}
           and nw_division in ('comm', 'both')
           and nw_device in ('pc', 'both')
         limit 1
    ", false);

    if (empty($existing['nw_id'])) {
        alert('삭제할 팝업을 찾을 수 없습니다.', 'popup.list.php');
        exit;
    }

    sql_query("
        delete from {$g5['new_win_table']}
         where nw_id = {$nw_id}
         limit 1
    ");

    run_event('admin_newwin_deleted', $nw_id);

    if (function_exists('fnSetLog')) {
        fnSetLog(
            $login_mb_id,
            '중요공지 팝업을 삭제하였습니다. ID: '.$nw_id
        );
    }

    alert('팝업이 삭제되었습니다.', 'popup.list.php');
    exit;
}

if ($w !== '' && $w !== 'u') {
    alert('잘못된 요청입니다.', 'popup.list.php');
    exit;
}

$nw_subject = isset($_POST['nw_subject'])
    ? trim(strip_tags((string) $_POST['nw_subject']))
    : '';

$nw_content = isset($_POST['nw_content'])
    ? trim((string) $_POST['nw_content'])
    : '';

$nw_begin_time = isset($_POST['nw_begin_time'])
    ? trim((string) $_POST['nw_begin_time'])
    : '';

$nw_end_time = isset($_POST['nw_end_time'])
    ? trim((string) $_POST['nw_end_time'])
    : '';

$nw_disable_hours = isset($_POST['nw_disable_hours'])
    ? (int) $_POST['nw_disable_hours']
    : 0;

$nw_left = isset($_POST['nw_left'])
    ? (int) $_POST['nw_left']
    : 0;

$nw_top = isset($_POST['nw_top'])
    ? (int) $_POST['nw_top']
    : 0;

$nw_width = isset($_POST['nw_width'])
    ? (int) $_POST['nw_width']
    : 0;

$nw_height = isset($_POST['nw_height'])
    ? (int) $_POST['nw_height']
    : 0;

if ($nw_subject === '') {
    alert('팝업 제목을 입력하세요.');
    exit;
}

$date_pattern = '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/';

if (
    !preg_match($date_pattern, $nw_begin_time) ||
    !preg_match($date_pattern, $nw_end_time)
) {
    alert('시작일시와 종료일시는 YYYY-MM-DD HH:MM:SS 형식으로 입력하세요.');
    exit;
}

$begin_timestamp = strtotime($nw_begin_time);
$end_timestamp = strtotime($nw_end_time);

if ($begin_timestamp === false || $end_timestamp === false) {
    alert('올바른 날짜와 시간을 입력하세요.');
    exit;
}

if ($begin_timestamp > $end_timestamp) {
    alert('종료일시는 시작일시보다 늦어야 합니다.');
    exit;
}

if ($nw_disable_hours < 0) {
    alert('다시 보지 않기 시간은 0 이상이어야 합니다.');
    exit;
}

if ($nw_left < 0 || $nw_top < 0) {
    alert('팝업 위치는 0 이상이어야 합니다.');
    exit;
}

if ($nw_width < 1 || $nw_height < 1) {
    alert('팝업 크기는 1px 이상이어야 합니다.');
    exit;
}

$nw_subject_sql = sql_real_escape_string($nw_subject);
$nw_content_sql = sql_real_escape_string($nw_content);
$nw_begin_time_sql = sql_real_escape_string($nw_begin_time);
$nw_end_time_sql = sql_real_escape_string($nw_end_time);

$sql_common = "
    nw_division = 'comm',
    nw_device = 'pc',
    nw_begin_time = '{$nw_begin_time_sql}',
    nw_end_time = '{$nw_end_time_sql}',
    nw_disable_hours = {$nw_disable_hours},
    nw_left = {$nw_left},
    nw_top = {$nw_top},
    nw_height = {$nw_height},
    nw_width = {$nw_width},
    nw_subject = '{$nw_subject_sql}',
    nw_content = '{$nw_content_sql}',
    nw_content_html = 1
";

if ($w === '') {
    sql_query("
        insert {$g5['new_win_table']}
        set {$sql_common}
    ");

    $nw_id = (int) sql_insert_id();

    run_event('admin_newwin_created', $nw_id);

    if (function_exists('fnSetLog')) {
        fnSetLog(
            $login_mb_id,
            '중요공지 팝업을 등록하였습니다. ID: '.$nw_id
        );
    }

    alert(
        '팝업이 등록되었습니다.',
        'popup.form.php?w=u&nw_id='.$nw_id
    );
    exit;
}

if ($nw_id < 1) {
    alert('잘못된 팝업 번호입니다.', 'popup.list.php');
    exit;
}

$existing = sql_fetch("
    select nw_id
      from {$g5['new_win_table']}
     where nw_id = {$nw_id}
       and nw_division in ('comm', 'both')
       and nw_device in ('pc', 'both')
     limit 1
", false);

if (empty($existing['nw_id'])) {
    alert('수정할 팝업을 찾을 수 없습니다.', 'popup.list.php');
    exit;
}

sql_query("
    update {$g5['new_win_table']}
       set {$sql_common}
     where nw_id = {$nw_id}
     limit 1
");

run_event('admin_newwin_updated', $nw_id);

if (function_exists('fnSetLog')) {
    fnSetLog(
        $login_mb_id,
        '중요공지 팝업을 수정하였습니다. ID: '.$nw_id
    );
}

alert(
    '팝업이 저장되었습니다.',
    'popup.form.php?w=u&nw_id='.$nw_id
);
?>
