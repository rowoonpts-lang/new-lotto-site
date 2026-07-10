<?
	include_once("_common.php");
	
	$sql = "update g5_member_etc set mb_in = '{$type}' where 1=1 and mb_id = '{$mb_id}'";
	sql_query($sql);

	alert("정상적으로 {$type}되었습니다.");
?>