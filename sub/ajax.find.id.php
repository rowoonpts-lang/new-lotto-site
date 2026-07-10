<?
	include_once("_common.php");

	$sql = "select count(*) cnt from g5_member where 1=1 and mb_id = '{$mb_id}'";
	$row = sql_fetch($sql);
	if($row[cnt] == 0){
		echo "0001";
	}else{
		echo "0000";
	}
?>