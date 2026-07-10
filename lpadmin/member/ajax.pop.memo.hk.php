<?
	include_once("_common.php");

	sql_query("alter table `g5_member` add column `mb_hyunkm` varchar(255) NOT NULL");

	$sql = "update g5_member set mb_hyunkm = '{$v}' where 1=1 and mb_id = '{$mb_id}'";
	sql_query($sql);
	echo $sql;
?>