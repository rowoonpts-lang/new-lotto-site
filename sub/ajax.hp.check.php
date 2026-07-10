<?
	include_once("_common.php");

	$sql = "select count(mb_no) cnt from g5_member where 1=1 and mb_hp = '{$mb_hp}'";
	$row = sql_fetch($sql);

	if($row[cnt] < 1){
		echo "1111";
	}
?>