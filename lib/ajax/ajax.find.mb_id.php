<?
	include_once("_common.php");

	$sql = "select count(mb_no) cnt from g5_member where 1=1 and mb_id = '{$mb_id}'";
	$row = sql_fetch($sql);

	echo $row[cnt];
?>