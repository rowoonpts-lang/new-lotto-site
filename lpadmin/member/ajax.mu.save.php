<?
	include_once("_common.php");

	$sql = "insert into l_pay set
				  mb_id = '{$mb_id}'
				, emp_id = '{$mu_emp_id}'
				, mb_hp = '{$mu_mb_hp}'
				, mu_num = '{$mu_num}'
				, mu_mb_name = '{$mu_mb_name}'
				, pay_method = '무통장'
				, lp_type = '{$mu_mb_type}'
				, lp_price ='".str_replace(',','',$mu_mb_price)."'
				, lp_datetime = now()
				, lp_status = '주문'
			";
	sql_query($sql);

	/*$lm_memo = $prodName." 무통장 입금요청 - ".$mu_mb_price."원";
	fnSetMemo($mb_id, $mu_emp_id, '결제', $lm_memo);*/

	if($mu_sms == "발송"){
		$msg = $mb_name."고객님 입금 안내 ".$mu_num." / ".$mu_mb_price."원";
		fnSendOneshot($config['cf_oneshot_tel'], $mu_mb_hp, $msg , '');
	}
?>