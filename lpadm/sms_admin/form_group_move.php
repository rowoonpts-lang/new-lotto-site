<?php
// 이모티콘 그룹 이동
$sub_menu = "900500";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

check_admin_token();

$fg_no = isset($_REQUEST['fg_no']) ? (int) $_REQUEST['fg_no'] : 0;
$move_no = isset($_REQUEST['move_no']) ? (int) $_REQUEST['move_no'] : 0;

if ($move_no < 1) {
    alert('이동할 그룹을 선택해 주세요.');
}

if ($fg_no === $move_no) {
    alert('같은 그룹으로 이동할 수 없습니다.');
}

$group = sql_fetch("select * from {$g5['sms5_form_group_table']} where fg_no = '$move_no'");
if (!$group) {
    alert('이동할 그룹이 존재하지 않습니다.');
}

$fg_member = isset($group['fg_member']) ? (int) $group['fg_member'] : 0;

if ($fg_no) 
{
    $res = sql_fetch("select * from {$g5['sms5_form_group_table']} where fg_no = '$fg_no'");
    if (!$res) {
        alert('이동할 원본 그룹이 존재하지 않습니다.');
    }

    $fg_count = (int) $res['fg_count'];
    sql_query("update {$g5['sms5_form_group_table']} set fg_count = fg_count + $fg_count where fg_no = '$move_no'");
    sql_query("update {$g5['sms5_form_group_table']} set fg_count = 0 where fg_no='$fg_no'");
}
else
{
    $fg_count = sql_fetch("select count(*) as cnt from {$g5['sms5_form_table']} where fg_no = 0");
    $fg_count = $fg_count['cnt'];
    sql_query("update {$g5['sms5_form_group_table']} set fg_count = fg_count + $fg_count where fg_no = '$move_no'");
}

sql_query("update {$g5['sms5_form_table']} set fg_no = '$move_no', fg_member = '$fg_member' where fg_no = '$fg_no'");

goto_url('./form_group.php');
?>