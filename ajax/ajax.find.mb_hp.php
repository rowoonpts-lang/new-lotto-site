<?
	include_once("_common.php");
	if($nochk == "y"){
		$sql = "select count(mb_no) cnt from g5_member where 1=1 and (replace(mb_hp,'-','') = '{$mb_hp}' )";
	}else{
		$sql = "select count(mb_no) cnt from g5_member where 1=1 and (replace(mb_hp,'-','') = '{$mb_hp}' or replace(mb_tel,'-','') = '{$mb_hp}')";
	}
	$row = sql_fetch($sql);

	echo $row['cnt'];
?>