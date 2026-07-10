<?
	include_once("_common.php");

	$sql = "update l_pay set confirm_user = '{$member[mb_id]}', confirm_user_name = '{$member[mb_name]}{$member[mb_team]}',confirm_in_datetime = now() where lp_id = '{$lp_id}'";
	sql_query($sql);

	/*$sql = "update g5_member set mb_type = '{$confirm_mb_type}' where 1=1 and mb_id = '{$mb_id}'";
	sql_query($sql);*/
	
	// 미수금 알람
	$confirm_misu = str_replace(",","",$confirm_misu);
	/*if($confirm_misu > 0){
		
		if($confirm_misu_alarm_date){
			fnSetMemo($mb_id, $member[mb_id], '미수', '미수금 발생', $confirm_misu, '미수', $confirm_misu_alarm_date);
		}else{
			fnSetMemo($mb_id, $member[mb_id], '미수', '미수금 발생', $confirm_misu, '미수');
		}
	}*/

	if($confirm_misu_chk == 1){
		$sql = "update g5_member_etc set recent_misu = '0' where mb_id = '{$mb_id}'";
		sql_query($sql);

		//fnSetMemo($mb_id, $member[mb_id], '미수', '미수금 완납', 0, '미수');
	}else{
		$sql = "update g5_member_etc set recent_misu = '{$confirm_misu}' where mb_id = '{$mb_id}'";
		sql_query($sql);
	}


	/*if($confirm_mb_type_memo){
		$sql = "select * from l_confirm_memo where 1=1 and lcm_type = '일반승인' and lcm_cate = '{$confirm_mb_type_memo}'";
		$row = sql_fetch($sql);
		$memo = $row[lcm_content];

		if($confirm_misu_alarm_date){
			fnSetMemo($mb_id, $member[mb_id], '승인', $memo, $confirm_misu, '미수', $confirm_misu_alarm_date, $lp_price);
		}else{
			fnSetMemo($mb_id, $member[mb_id], '승인', $memo, $confirm_misu, '미수', '', $lp_price);
		}
	}else{

		fnSetMemo($mb_id, $member[mb_id], '승인', '', '', '', '', $lp_price);
	}*/
	$sql = "select * from l_confirm_memo where 1=1 and lcm_type = '일반승인' and lcm_cate = '{$confirm_mb_type_memo}'";
	$row = sql_fetch($sql);
	$memo = $row[lcm_content];

	if($confirm_misu_alarm_date){
		fnSetMemo($mb_id, $member[mb_id], '승인', $memo, $confirm_misu, '미수', $confirm_misu_alarm_date, $lp_price);
	}else{
		fnSetMemo($mb_id, $member[mb_id], '승인', $memo, $confirm_misu, '미수', '', $lp_price);
	}

	alert("정상적으로 처리되었습니다.");

?>