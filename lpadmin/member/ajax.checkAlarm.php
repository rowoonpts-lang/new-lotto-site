<?
	include_once("_common.php");

	$sql = "update l_memo set lm_alarm_view = '1' where 1=1 and lm_id = '{$lm_id}' ";
	sql_query($sql);
echo $sql;
	$sql = "select * from l_memo where 1=1 and lm_id = '{$lm_id}'";
	$row = sql_fetch($sql);

?>