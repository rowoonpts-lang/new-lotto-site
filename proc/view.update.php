<?
	include_once("_common.php");

	$mb_id = base64_decode($mb_id);
	$ly_type = base64_decode($ly_type);

	$sql = "update g5_member_etc set mb_yak = '{$ly_type}' where 1=1 and mb_id = '{$mb_id}' ";
	sql_query($sql);
	
	alert("정상적으로 약관에 동의하셨습니다.");
?>