<?
	include_once("_common.php");

	$sql = "update l_pay set 
				confirm_user = null 
				, confirm_user_name = null  
				, confirm_in1_name = null
				, confirm_in1 = null
				, confirm_in2 = null
				, confirm_in2_name = null
				, confirm_in1_price = null
				, confirm_in2_price = null
				, confirm_in_datetime = null
			where lp_id = '{$lp_id}'
			";
	sql_query($sql);

	alert("정상적으로 처리되었습니다.");

?>