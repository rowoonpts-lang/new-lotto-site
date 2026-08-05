<?php
	include_once("_common.php");
	include_once(G5_PATH."/head.sub.php");

	if (!$is_member || !isset($member['mb_level']) || (int) $member['mb_level'] < 5) {
		goto_url(G5_LADMIN_URL."/login.php");
	}

	$step2_code = isset($config['cf_10']) ? trim((string) $config['cf_10']) : '';
	if ($step2_code === '') {
		alert("2차 인증 코드가 설정되지 않았습니다. 최고관리자에게 문의해주세요.");
	}

	if ((string) get_session('ss_step2') === $step2_code) {
		goto_url(G5_LADMIN_URL);
	}
?>

<link rel="stylesheet" href="<?=G5_URL?>/ad/style.css">

<div class="login_box">
	<form id="" name="" method="post" action="login.step2.check.php" onSubmit="return fnLogin();">
	<ul class="login_box_ul">
		<li class="logo_box" style="font-size:17px;font-weight:600">2차 보안 인증</li>
		<li><input type="text" name="step2" placeholder="코드입력(대소문자 구분)" required></li>
		<li><button>2차인증번호 입력</button></li>
	</ul>
	</form>
</div>

<script>
function fnLogin(){
	return true;
}
</script>