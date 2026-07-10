<?
	include_once("_common.php");

	$sql = "update g5_config set cf_ip = '{$v}' ";
	sql_query($sql);
?>