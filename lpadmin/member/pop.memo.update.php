<?php
	include_once("_common.php");

	$memberId = $member['mb_id'] ?? '';
	$alarm_lm_id = isset($_REQUEST['alarm_lm_id']) ? (int) $_REQUEST['alarm_lm_id'] : 0;

	fnSetLog($memberId, '메모 작성');

	// 원본 값 선언 (이미 외부에서 할당되었다고 가정)
	$mb_id = $mb_id ?? '';
	$recent_select = $recent_select ?? '';
	$recent_memo = $recent_memo ?? '';
	$recent_misu = $recent_misu ?? '';
	$alarm_select = $alarm_select ?? '';
	$alarm_date = $alarm_date ?? '';

	$raw_mb_id = trim((string) $mb_id);
	$raw_recent_memo = trim((string) $recent_memo);

	if ($alarm_lm_id > 0 && $raw_recent_memo === '') {
		alert('통화예약 알림을 완료하려면 상담내용을 작성하세요.');
		exit;
	}

	if ($alarm_lm_id > 0) {
		$alarm_member_id_sql = sql_real_escape_string((string) $memberId);
		$alarm_mb_id_sql = sql_real_escape_string($raw_mb_id);
		$alarm_row = sql_fetch(
			"select lm_id
			   from l_memo
			  where lm_id = {$alarm_lm_id}
			    and mb_id = '{$alarm_mb_id_sql}'
			    and from_mb_id = '{$alarm_member_id_sql}'
			    and lm_alarm_view = 0
			  limit 1",
			false
		);

		if (empty($alarm_row['lm_id'])) {
			alert('처리할 수 없는 통화예약 알림입니다.');
			exit;
		}
	}

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
				, from_mb_id = '".sql_real_escape_string((string) $memberId)."'
				, lm_memo_type = '{$recent_select}'
				, lm_memo = '{$recent_memo}'
				, lm_misu = '{$recent_misu}'
				, lm_alarm_type = '{$alarm_select}'
				, lm_alarm_date = '{$alarm_date}'
				, lm_datetime = now()
			";
	$memo_insert_result = sql_query($sql, false);

	if (!$memo_insert_result) {
		alert('상담내용 저장 중 오류가 발생했습니다.');
		exit;
	}

	if ($alarm_lm_id > 0) {
		$alarm_member_id_sql = sql_real_escape_string((string) $memberId);
		if (!sql_query(
			"update l_memo
			    set lm_alarm_view = 1
			  where lm_id = {$alarm_lm_id}
			    and mb_id = '{$mb_id}'
			    and from_mb_id = '{$alarm_member_id_sql}'
			    and lm_alarm_view = 0",
			false
		)) {
			alert('상담내용은 저장되었지만 통화예약 알림 완료 처리에 실패했습니다.');
			exit;
		}
	}

	alert("정상적으로 저장되었습니다.");
?>