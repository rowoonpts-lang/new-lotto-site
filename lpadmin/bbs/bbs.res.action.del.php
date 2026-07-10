<?
	include_once("_common.php");

	$sql = "update l_res set del_yn = '1' where lr_id = '{$lr_id}'";
	sql_query($sql);

	alert("정상적으로 처리되었습니다.");
?>