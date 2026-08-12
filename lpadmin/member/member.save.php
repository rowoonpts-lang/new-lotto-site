<?php
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.sub.php");

	$loginLevel = isset($member['mb_level'])
		? (int) $member['mb_level']
		: 0;

	if (!lottoIsStaffLevel($loginLevel)) {
		alert('회원등록 권한이 없습니다.');
	}

	$canCreateStaff = lottoCanCreateStaff($loginLevel);

	if (
		!isset($_SERVER['REQUEST_METHOD'])
		|| $_SERVER['REQUEST_METHOD'] !== 'POST'
	) {
		alert('잘못된 요청입니다.');
	}

	$mb_id = isset($_POST['mb_id'])
		? trim((string) $_POST['mb_id'])
		: '';

	$mb_password = isset($_POST['mb_password'])
		? (string) $_POST['mb_password']
		: '';

	$mb_name = isset($_POST['mb_name'])
		? trim((string) $_POST['mb_name'])
		: '';

	$mb_hp = isset($_POST['mb_hp'])
		? preg_replace('/[^0-9]/', '', (string) $_POST['mb_hp'])
		: '';

	$requestedLevel = 2;

	if ($canCreateStaff) {
		$requestedLevel = isset($_POST['mb_level'])
			? (int) $_POST['mb_level']
			: 2;
	}

	$allowedLevels = array(
		2,
		LOTTO_ROLE_STAFF1,
		LOTTO_ROLE_STAFF2,
		LOTTO_ROLE_TEAM_LEADER,
		LOTTO_ROLE_ADMIN,
	);

	if ($mb_id === '') {
		alert('아이디를 입력해주세요.');
	}

	if ($mb_password === '') {
		alert('패스워드를 입력해주세요.');
	}

	if ($mb_name === '') {
		alert('이름을 입력해주세요.');
	}

	if (!preg_match('/^01[0-9][0-9]{7,8}$/', $mb_hp)) {
		alert('핸드폰번호 형식이 올바르지 않습니다.');
	}

	if (!in_array($requestedLevel, $allowedLevels, true)) {
		alert('허용되지 않은 권한입니다.');
	}

	$mbIdSql = sql_real_escape_string($mb_id);
	$mbNameSql = sql_real_escape_string($mb_name);
	$mbHpSql = sql_real_escape_string($mb_hp);

	$duplicateId = sql_fetch(
		"select mb_id
		   from {$g5['member_table']}
		  where mb_id = '{$mbIdSql}'
		  limit 1",
		false
	);

	if (!empty($duplicateId['mb_id'])) {
		alert('이미 사용중인 아이디입니다.');
	}

	$duplicateHp = sql_fetch(
		"select mb_id
		   from {$g5['member_table']}
		  where mb_hp = '{$mbHpSql}'
		  limit 1",
		false
	);

	if (!empty($duplicateHp['mb_id'])) {
		alert('이미 사용중인 핸드폰번호입니다.');
	}

	$saveMbType = $requestedLevel === 2
		? '무료회원'
		: '직원';

	$saveMbTypeSql = sql_real_escape_string($saveMbType);
	$encryptedPassword = get_encrypt_string($mb_password);
	$encryptedPasswordSql = sql_real_escape_string($encryptedPassword);

	$mbCode = fnNewMbCode();
	$mbCodeSql = sql_real_escape_string($mbCode);

	$nick = 'nick'.date('YmdHisB');
	$nickSql = sql_real_escape_string($nick);

	$email = date('YmdHisB').'@lottopeak.co.kr';
	$emailSql = sql_real_escape_string($email);

	$remoteAddr = isset($_SERVER['REMOTE_ADDR'])
		? (string) $_SERVER['REMOTE_ADDR']
		: '';

	$remoteAddrSql = sql_real_escape_string($remoteAddr);

	$sql = "insert into {$g5['member_table']}
			set mb_id = '{$mbIdSql}',
				mb_code = '{$mbCodeSql}',
				mb_password = '{$encryptedPasswordSql}',
				mb_name = '{$mbNameSql}',
				mb_nick = '{$nickSql}',
				mb_nick_date = '".G5_TIME_YMD."',
				mb_email = '{$emailSql}',
				mb_tel = '{$mbHpSql}',
				mb_hp = '{$mbHpSql}',
				mb_datetime = '".G5_TIME_YMDHIS."',
				mb_ip = '{$remoteAddrSql}',
				mb_level = '{$requestedLevel}',
				mb_login_ip = '{$remoteAddrSql}',
				mb_open_date = '".G5_TIME_YMD."',
				mb_type = '{$saveMbTypeSql}'";

	sql_query($sql);

	setEtcInfo($mb_id, '');

	if (!$canCreateStaff) {
		$loginMbId = isset($member['mb_id'])
			? trim((string) $member['mb_id'])
			: '';

		$loginMbIdSql = sql_real_escape_string($loginMbId);

		sql_query(
			"insert into l_member_assignment (
				mb_id,
				staff_mb_id,
				assigned_by,
				assigned_at,
				updated_at
			) values (
				'{$mbIdSql}',
				'{$loginMbIdSql}',
				'{$loginMbIdSql}',
				now(),
				now()
			)
			on duplicate key update
				staff_mb_id = values(staff_mb_id),
				assigned_by = values(assigned_by),
				updated_at = now()"
		);
	}

	fnSetMemo(
		$mb_id,
		(string) $member['mb_id'],
		'대리가입',
		'대리가입 완료'
	);
?>
<script>
$(function(){
	alert("정상적으로 등록되었습니다.");
	window.opener.location.reload();
	window.close();
});
</script>
