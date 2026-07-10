<?
	include_once("_common.php");
	print_r($chk);

	for($i=0; $i < count($chk); $i++){
		$sql = "delete from g5_member where 1=1 and mb_id = '{$chk[$i]}'";
		sql_query($sql);

		$sql = "delete from g5_member_etc where 1=1 and mb_id = '{$chk[$i]}'";
		sql_query($sql);
	}
?>