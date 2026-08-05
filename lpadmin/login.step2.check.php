<?php
	include_once("_common.php");

	if (!$is_member || !isset($member['mb_level']) || (int) $member['mb_level'] < 5) {
		goto_url(G5_LADMIN_URL."/login.php");
	}

	$step2 = isset($_POST['step2']) ? trim((string) $_POST['step2']) : '';
	$step2_code = isset($config['cf_10']) ? trim((string) $config['cf_10']) : '';

	if ($step2_code === '') {
		alert("2차 인증 코드가 설정되지 않았습니다. 최고관리자에게 문의해주세요.");
	}

	if ($step2 === '') {
		alert("2차인증 코드를 입력해주세요.");
	}

	$step2_matches = function_exists('hash_equals')
		? hash_equals($step2_code, $step2)
		: $step2_code === $step2;

	if (!$step2_matches) {
		alert('2차인증 코드가 맞지 않습니다.\\n접속 IP : '.get_real_client_ip());
	}

	set_session('ss_step2', $step2_code);

	goto_url(G5_LADMIN_URL);
?>
