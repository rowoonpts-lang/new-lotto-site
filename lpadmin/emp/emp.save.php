<?php
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.sub.php");

	$loginLevel = isset($member['mb_level'])
		? (int) $member['mb_level']
		: 0;

	if (!lottoCanCreateStaff($loginLevel)) {
		alert('직원 계정을 저장할 권한이 없습니다.');
	}

	$allowedStaffLevels = array(
		LOTTO_ROLE_STAFF1,
		LOTTO_ROLE_STAFF2,
		LOTTO_ROLE_TEAM_LEADER,
		LOTTO_ROLE_ADMIN,
	);

	$requestedLevel = isset($_POST['mb_level'])
		? (int) $_POST['mb_level']
		: 0;

	if (!in_array($requestedLevel, $allowedStaffLevels, true)) {
		alert('허용되지 않은 직원 권한입니다.');
	}

	$mb_level = $requestedLevel;

	$mb_no = isset($_POST['mb_no'])
		? (int) $_POST['mb_no']
		: 0;

	$mb_id = isset($_POST['mb_id'])
		? trim((string) $_POST['mb_id'])
		: '';

	if ($mb_id === 'rwadmin') {
		alert('최고관리자 계정은 이 화면에서 수정할 수 없습니다.');
	}

	if ($mb_no > 0) {
		$existingStaff = sql_fetch(
			"select mb_id, mb_level
			   from g5_member
			  where mb_no = '{$mb_no}'
			  limit 1"
		);

		if (empty($existingStaff['mb_id'])) {
			alert('수정할 직원 정보를 찾을 수 없습니다.');
		}

		if (!lottoIsStaffLevel((int) $existingStaff['mb_level'])) {
			alert('직원 계정만 수정할 수 있습니다.');
		}

		if (
			$existingStaff['mb_id'] === 'rwadmin'
			|| (int) $existingStaff['mb_level'] === LOTTO_ROLE_SUPER_ADMIN
		) {
			alert('최고관리자 계정은 이 화면에서 수정할 수 없습니다.');
		}

		$mb_id = (string) $existingStaff['mb_id'];
	}
	if(!$mb_no){
		$mb_code = fnNewMbCode();
		
		$sql = " insert into {$g5['member_table']}
					set mb_id = '{$mb_id}',
						 mb_code = '{$mb_code}',
						 mb_password = '".get_encrypt_string($mb_password)."',
						 mb_name = '{$mb_name}',
						 mb_nick = 'nick".date("YmdHisB")."',
						 mb_nick_date = '".G5_TIME_YMD."',
						 mb_email = '".date("YmdHisB")."@kookminlotto.co.kr',
						 mb_homepage = '{$mb_homepage}',
						 mb_tel = '{$mb_hp}',
						 mb_hp = '{$mb_hp}',
						 mb_zip1 = '{$mb_zip1}',
						 mb_zip2 = '{$mb_zip2}',
						 mb_addr1 = '{$mb_addr1}',
						 mb_addr2 = '{$mb_addr2}',
						 mb_addr3 = '{$mb_addr3}',
						 mb_addr_jibeon = '{$mb_addr_jibeon}',
						 mb_signature = '{$mb_signature}',
						 mb_profile = '{$mb_profile}',
						 mb_datetime = '".G5_TIME_YMDHIS."',
						 mb_ip = '{$_SERVER['REMOTE_ADDR']}',
						 mb_level = '{$mb_level}',
						 mb_recommend = '{$mb_recommend}',
						 mb_login_ip = '{$_SERVER['REMOTE_ADDR']}',
						 mb_mailling = '{$mb_mailling}',
						 mb_sms = '{$mb_sms}',
						 mb_open = '{$mb_open}',
						 mb_open_date = '".G5_TIME_YMD."',
						 mb_type = '직원',
						 mb_1 = '{$mb_1}',
						 mb_2 = '{$mb_2}',
						 mb_3 = '{$mb_3}',
						 mb_4 = '{$mb_4}',
						 mb_5 = '{$mb_5}',
						 mb_6 = '{$mb_6}',
						 mb_7 = '{$mb_7}',
						 mb_8 = '{$mb_8}',
						 mb_9 = '{$mb_9}',
						 mb_10 = '{$mb_10}',
						 mb_team = '{$mb_team}',
						 emp_pw = '{$mb_password}'
				";
		sql_query($sql);

		$mb_no = sql_insert_id();

		setEtcInfo($mb_id, $mb_db);

		
	}else{
		$sql = "
				update g5_member set
					mb_name = '{$mb_name}'
					, mb_team = '{$mb_team}'
					, mb_password = '".get_encrypt_string($mb_password)."'
					, mb_level = '{$mb_level}'
					, emp_pw = '{$mb_password}'
				where 1=1
					and mb_id = '{$mb_id}'
				";
		sql_query($sql);
	}

?>
<script>
$(function(){
	alert("정상적으로 저장되었습니다.");
	window.opener.location.reload();
	window.close();
});
</script>