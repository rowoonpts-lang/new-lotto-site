<?
	include_once("_common.php");
	
	$sql = "select * from g5_member where 1=1 and mb_id = '{$mb_id}'";
	$row = sql_fetch($sql);

	$url = G5_URL."/proc/view.php?mb_id=".base64_encode($mb_id)."&ly_type=".base64_encode("|".$ly_type."|");
	$msg = "아래 링크에 접속하여 약관에 동의해주세요.\n".$url;

	fnSendOneshot($config['cf_oneshot_tel'], $row[mb_hp], $msg , '');
?>