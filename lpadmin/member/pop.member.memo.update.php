<?php
	include_once("_common.php");

	$mb_id = isset($_POST['mb_id']) ? trim((string) $_POST['mb_id']) : '';
	$recent_select = isset($_POST['recent_select']) ? trim((string) $_POST['recent_select']) : '';
	$recent_memo = isset($_POST['recent_memo']) ? trim((string) $_POST['recent_memo']) : '';
	$alarm_select = isset($_POST['alarm_select']) ? trim((string) $_POST['alarm_select']) : '';
	$alarm_date = isset($_POST['alarm_date']) ? trim((string) $_POST['alarm_date']) : '';
	$alarm_lm_id = isset($_POST['alarm_lm_id']) ? (int) $_POST['alarm_lm_id'] : 0;

	if ($mb_id === '') {
		alert('회원정보가 올바르지 않습니다.');
		exit;
	}

	$mb_id_sql = sql_real_escape_string($mb_id);
	$recent_select_sql = sql_real_escape_string($recent_select);
	$recent_memo_clean = trim(strip_tags($recent_memo));
	$recent_memo_sql = sql_real_escape_string($recent_memo_clean);
	$alarm_select_sql = sql_real_escape_string($alarm_select);
	$alarm_date_sql = sql_real_escape_string($alarm_date);

	if ($alarm_lm_id > 0 && $recent_memo_clean === '') {
		alert('통화예약 알림을 완료하려면 상담내용을 작성하세요.');
		exit;
	}

	$target_member = sql_fetch(
		"select a.mb_id
		   from g5_member a
		  where a.mb_id = '{$mb_id_sql}'
		  limit 1"
	);

	if (empty($target_member['mb_id'])) {
		alert('회원정보를 찾을 수 없습니다.');
		exit;
	}

	$assignment = sql_fetch(
		"select staff_mb_id
		   from l_member_assignment
		  where mb_id = '{$mb_id_sql}'
		  limit 1",
		false
	);

	$login_mb_id = isset($member['mb_id']) ? trim((string) $member['mb_id']) : '';
	$login_level = isset($member['mb_level']) ? (int) $member['mb_level'] : 0;
	$can_view_all = lottoCanViewAllMembers($login_level);

	if (!$can_view_all) {
		$allowed_staff_ids = array($login_mb_id);

		if ($login_level === LOTTO_ROLE_STAFF2 || $login_level === LOTTO_ROLE_TEAM_LEADER) {
			$child_staff_ids = lottoGetDirectChildStaffIds($login_mb_id);
			foreach ($child_staff_ids as $child_staff_id) {
				$allowed_staff_ids[] = (string) $child_staff_id;
			}
		}

		$allowed_staff_ids = array_values(array_unique(array_filter($allowed_staff_ids)));
		$assigned_staff_mb_id = isset($assignment['staff_mb_id']) ? trim((string) $assignment['staff_mb_id']) : '';

		if ($assigned_staff_mb_id === '' || !in_array($assigned_staff_mb_id, $allowed_staff_ids, true)) {
			alert('접근 권한이 없는 회원입니다.');
			exit;
		}
	}

	if ($alarm_lm_id > 0) {
		$login_mb_id_sql = sql_real_escape_string($login_mb_id);
		$call_alarm = sql_fetch(
			"select lm_id, mb_id, from_mb_id, lm_alarm_view
			   from l_memo
			  where lm_id = {$alarm_lm_id}
			    and mb_id = '{$mb_id_sql}'
			    and from_mb_id = '{$login_mb_id_sql}'
			    and lm_alarm_view = 0
			  limit 1",
			false
		);

		if (empty($call_alarm['lm_id'])) {
			alert('처리할 수 없는 통화예약 알림입니다.');
			exit;
		}
	}

	fnSetLog($login_mb_id, $mb_id.'님의 상담내용을 작성하였습니다.');

	$sql = "update g5_member_etc set
				recent_select = '{$recent_select_sql}',
				recent_memo = '{$recent_memo_sql}'
			where mb_id = '{$mb_id_sql}'";
	sql_query($sql);

	$sql = "insert into l_memo set
				mb_id = '{$mb_id_sql}',
				from_mb_id = '".sql_real_escape_string($login_mb_id)."',
				lm_memo_type = '{$recent_select_sql}',
				lm_memo = '{$recent_memo_sql}',
				lm_misu = '',
				lm_alarm_type = '{$alarm_select_sql}',
				lm_alarm_date = '{$alarm_date_sql}',
				lm_datetime = now()";
	sql_query($sql);

	if ($alarm_lm_id > 0) {
		if (!sql_query(
			"update l_memo
			    set lm_alarm_view = 1
			  where lm_id = {$alarm_lm_id}
			    and mb_id = '{$mb_id_sql}'
			    and from_mb_id = '".sql_real_escape_string($login_mb_id)."'
			    and lm_alarm_view = 0",
			false
		)) {
			alert('통화예약 알림 완료 처리 중 오류가 발생했습니다.');
			exit;
		}
	}

	alert(
		'정상적으로 저장되었습니다.',
		G5_LADMIN_URL.'/member/pop.member.php?mb_id='.urlencode(base64_encode($mb_id))
	);
?>
