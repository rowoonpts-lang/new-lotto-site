<?
	include_once("_common.php");

	/*$sql = "update l_pay set confirm_user = '{$member[mb_id]}' where lp_id = '{$lp_id}'";
	sql_query($sql);*/
	
	$sql = "select * from g5_member where 1=1 and mb_id = '{$confirm_in1}'";
	$row = sql_fetch($sql);

	$sql = "update l_pay set 
				confirm_in1 = '{$confirm_in1}'
				, confirm_in1_name = '{$row[mb_name]}'
				, confirm_in2 = '{$confirm_in2}'
				, confirm_in2_name = '{$member[mb_name]}'
				, confirm_in1_price = '".str_replace(",","",$confirm_in1_price)."'
				, confirm_in2_price = '".str_replace(",","",$confirm_in2_price)."'
				, confirm_user = '{$member[mb_id]}'
				, confirm_user_name = '{$member[mb_name]}'
				, confirm_in_datetime = now()
			where lp_id = '{$lp_id}'
			";
	sql_query($sql);
	
	// 미수금 알람
	$confirm_misu = str_replace(",","",$confirm_misu);

	if($confirm_misu_chk == 1){
		$sql = "update g5_member_etc set recent_misu = '0' where mb_id = '{$mb_id}'";
		sql_query($sql);

		//fnSetMemo($mb_id, $member[mb_id], '미수', '미수금 완납', 0, '미수');
	}else{
		$sql = "update g5_member_etc set recent_misu = '{$confirm_misu}' where mb_id = '{$mb_id}'";
		sql_query($sql);
	}


	$sql = "select * from l_confirm_memo where 1=1 and lcm_type = '일반승인' and lcm_cate = '{$confirm_mb_type_memo}'";
	$row = sql_fetch($sql);
	$memo = $row[lcm_content];

	if($confirm_misu_alarm_date){
		if($confirm_in1_price){
			$mmemo = "1차 인계금 완료";
			fnSetMemo($mb_id, $confirm_in1, '승인', $mmemo, $confirm_misu, '미수', $confirm_misu_alarm_date, str_replace(",","",$confirm_in1_price));
		}
		if($confirm_in2_price){
			fnSetMemo($mb_id, $member[mb_id], '승인', $memo, $confirm_misu, '미수', $confirm_misu_alarm_date, str_replace(",","",$confirm_in2_price));
		}
	}else{
		if($confirm_in1_price){
			$mmemo = "1차 인계금 완료";
			fnSetMemo($mb_id, $confirm_in1, '승인', $mmemo, $confirm_misu, '미수', '', str_replace(",","",$confirm_in1_price));
		}
		if($confirm_in2_price){
			fnSetMemo($mb_id, $member[mb_id], '승인', $memo, $confirm_misu, '미수', '', str_replace(",","",$confirm_in2_price));
		}
	}

	alert("정상적으로 처리되었습니다.");
	

?>