<?
	include_once("_common.php");


	$sql = "delete from Msg_Spam where 1=1 and Phone_No= '{$pn}'";
	sql_query($sql);

	alert("정상적으로 삭제되었습니다.");
?>