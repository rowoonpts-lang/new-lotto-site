<?
	include_once("_common.php");

	$sql = "update l_pay set confirm_user = null, confirm_user_name = null where lp_id = '{$lp_id}'";
	sql_query($sql);

	alert("정상적으로 처리되었습니다.");

?>