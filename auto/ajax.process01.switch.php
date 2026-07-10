<?
	include_once("_common.php");

	$sql = "update g5_config set cf_auto1_date = '".date("Y-m-d")."', cf_auto1_ing = '1' ";
	sql_query($sql);
	echo $sql;
?>