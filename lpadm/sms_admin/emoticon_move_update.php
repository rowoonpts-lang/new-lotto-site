<?php
$sub_menu = "900600";
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

check_admin_token();

$sw = isset($_POST['sw']) ? $_POST['sw'] : '';
$page = isset($_POST['page']) ? max(0, (int) $_POST['page']) : 0;
$url = isset($_POST['url']) ? clean_xss_tags($_POST['url'], 1, 1) : '';

if (!in_array($sw, array('move', 'copy'), true)) {
    alert('잘못된 작업 요청입니다.');
}

$post_chk_fg_no = isset($_POST['chk_fg_no']) && is_array($_POST['chk_fg_no'])
    ? array_values(array_filter(array_map('intval', $_POST['chk_fg_no'])))
    : array();

if (!count($post_chk_fg_no))
    alert('이모티콘을 이동할 그룹을 한개 이상 선택해 주십시오.', $url);

$post_fo_no_list = isset($_POST['fo_no_list']) ? explode(',', $_POST['fo_no_list']) : array();
$post_fo_no_list = array_values(array_filter(array_map('intval', $post_fo_no_list)));
$fo_no_list = implode(',', $post_fo_no_list);

if ($fo_no_list === '')
    alert('이동할 이모티콘을 선택해 주십시오.', $url);

$sql = "select * from {$g5['sms5_form_table']} where fo_no in ($fo_no_list) order by fo_no desc ";
$result = sql_query($sql);
$save = array();
for ($kk=0;$row = sql_fetch_array($result);$kk++)
{
    $fo_no = $row['fo_no'];
    for ($i=0; $i<count($post_chk_fg_no); $i++)
    {
        $fg_no = (int) $post_chk_fg_no[$i];
        if( !$fg_no ) continue;
        $group = sql_fetch("select * from {$g5['sms5_form_group_table']} where fg_no = '$fg_no'");
        if (!$group) continue;

        $fg_member = isset($group['fg_member']) ? (int) $group['fg_member'] : 0;

        $sql = " insert into {$g5['sms5_form_table']}
                    set fg_no='$fg_no',
                        fg_member='$fg_member',
                        fo_name='".addslashes($row['fo_name'])."',
                        fo_content='".addslashes($row['fo_content'])."',
                        fo_datetime='".G5_TIME_YMDHIS."' ";
        sql_query($sql);
        sql_query("update {$g5['sms5_form_group_table']} set fg_count = fg_count + 1 where fg_no='$fg_no'");
    }
    $save[$kk]['fo_no'] = $row['fo_no'];
    $save[$kk]['fg_no'] = $row['fg_no'];
}

if ($sw == 'move')
{
    foreach ($save as $v)
    {
        if( empty($v['fo_no']) ) continue;
        sql_query(" delete from {$g5['sms5_form_table']} where fo_no = '{$v['fo_no']}' ");
        sql_query("update {$g5['sms5_form_group_table']} set fg_count = fg_count - 1 where fg_no='{$v['fg_no']}'");
    }
}

$msg = '해당 이모티콘을 선택한 그룹으로 이동 하였습니다.';
$opener_href = './form_list.php?page='.$page;

echo <<<HEREDOC
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<script>
alert("$msg");
opener.document.location.href = "$opener_href";
window.close();
</script>
<noscript>
<p>
    "$msg"
</p>
<a href="$opener_href">돌아가기</a>
</noscript>
HEREDOC;
?>