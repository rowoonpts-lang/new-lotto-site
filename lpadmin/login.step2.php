<?
	include_once("_common.php");
	include_once(G5_PATH."/head.sub.php");

	error_reporting(E_ALL);
	ini_set("display_errors", 1);
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