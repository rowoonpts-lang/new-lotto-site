<?
	include_once("_common.php");

	$sql = "update l_ad_user set del_yn = '1' where idx = '{$idx}'";
	sql_query($sql);

	alert("정상적으로 처리되었습니다.");
?>