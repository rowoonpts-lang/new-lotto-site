<?
	include_once("_common.php");

	$sql = "select * from l_pay where 1=1 and lp_id = '{$_GET['lp_id']}'";
	$row = sql_fetch($sql);


	$sql = "update l_pay set lp_status = '취소', lp_cancel_datetime = now() where lp_id = '{$lp_id}'";
	sql_query($sql);

	/*$lm_memo = $row[lp_type]." ".$row[pay_method]." 결제 취소 - ".number_format($row[lp_price])."원";
	fnSetMemo($row[mb_id], $member['mb_id'], '결제', $lm_memo);*/

	if($row[pay_method] == "신용카드"){
		// 카드결제 취소
		include_once(G5_LADMIN_PATH."/payment/payment.cancel.php");
	}else{
		// 무통장일 시 바로 종료
		alert("정상적으로 처리되었습니다.");
	}
?>