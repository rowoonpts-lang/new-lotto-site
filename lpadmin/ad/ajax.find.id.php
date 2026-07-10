<?
	include_once("_common.php");

	$sql = "select count(idx) cnt from l_ad_user where 1=1 and lu_id = '{$lu_id}' ";
	$row = sql_fetch($sql);

	echo $row[cnt];
?>