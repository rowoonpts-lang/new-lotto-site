<?
	include_once("_common.php");
	$idx = $_REQUEST['idx'];
	$sql = "update l_ad_list set del_yn = '1' where idx = '{$idx}'";

	sql_query($sql);

	alert("정상적으로 처리되었습니다.");
?>