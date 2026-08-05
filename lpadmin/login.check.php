<?php
	include_once("_common.php");

	$mb_id = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';
	$mb_password = isset($_POST['mb_password']) ? trim($_POST['mb_password']) : '';

	if (!$mb_id || !$mb_password) {
		alert('회원아이디나 비밀번호가 공백이면 안됩니다.');
	}

	$mb = get_member($mb_id);
	if (!(isset($mb['mb_id']) && $mb['mb_id']) || !login_password_check($mb, $mb_password, $mb['mb_password'])) {
		alert('가입된 회원아이디가 아니거나 비밀번호가 틀립니다.\\n비밀번호는 대소문자를 구분합니다.');
	}

	$row = $mb;

	if ($row['mb_level'] < 5) {
		alert($config['cf_title']." 직원만 접속이 가능합니다.");
	}
	if($row['mb_leave_date']){
		alert("탈퇴한 아이디 입니다.");
	}

	

	set_session('ss_mb_id', $mb['mb_id']);
	generate_mb_key($mb);
	if (function_exists('update_auth_session_token')) {
		update_auth_session_token($mb['mb_datetime']);
	}
	set_session('ss_step2', '');

	/*$sql = "update g5_member set login_datetime = now() where lu_id = '{$lu_id}'";
	sql_query($sql);*/
	goto_url(G5_LADMIN_URL);

?>