<?
	include_once("_common.php");

	fnSetLog($member[mb_id],'메모 작성');
	
	// 원본 값 선언 (이미 외부에서 할당되었다고 가정)
	$mb_id = $mb_id ?? '';
	$recent_select = $recent_select ?? '';
	$recent_memo = $recent_memo ?? '';
	$recent_misu = $recent_misu ?? '';
	$alarm_select = $alarm_select ?? '';
	$alarm_date = $alarm_date ?? '';

	// 필터링
	$mb_id         = trim(sql_real_escape_string($mb_id));
	$recent_select = trim(sql_real_escape_string($recent_select));
	$recent_memo   = trim(sql_real_escape_string(strip_tags($recent_memo)));
	$recent_misu   = trim(sql_real_escape_string($recent_misu));
	$alarm_select  = trim(sql_real_escape_string($alarm_select));
	$alarm_date    = trim(sql_real_escape_string($alarm_date));



	// 최신정보 회원테이블에 저장
	$sql = "update g5_member_etc set
				recent_select = '{$recent_select}'
				, recent_memo = '{$recent_memo}'
				, recent_misu = '{$recent_misu}'
			where 1=1
				and mb_id = '{$mb_id}'
			";
	sql_query($sql);


	// 알람테이블에 저장
	$sql = "insert into l_memo set
				mb_id = '{$mb_id}'
				, from_mb_id = '{$member[mb_id]}'
				, lm_memo_type = '{$recent_select}'
				, lm_memo = '{$recent_memo}'
				, lm_misu = '{$recent_misu}'
				, lm_alarm_type = '{$alarm_select}'
				, lm_alarm_date = '{$alarm_date}'
				, lm_datetime = now()
			";
	sql_query($sql);
	

	alert("정상적으로 저장되었습니다.")

?>