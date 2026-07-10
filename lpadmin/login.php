<?
	include_once("_common.php");
	include_once(G5_PATH."/head.sub.php");

	error_reporting(E_ALL);
	ini_set("display_errors", 1);
?>

<link rel="stylesheet" href="<?=G5_URL?>/ad/style.css">

<div class="login_box">
	<form id="" name="" method="post" action="login.check.php" onSubmit="return fnLogin();">
	<ul class="login_box_ul">
		<li class="logo_box"><a href="<?=G5_URL?>" target="_blank"><img src="<?=G5_THEME_IMG_URL?>/logo.png"></a></li>
		<li><input type="text" name="mb_id" placeholder="아이디 입력" required></li>
		<li><input type="password" name="mb_password" placeholder="패스워드 입력" required></li>
		<li><button>로그인</button></li>
	</ul>
	</form>
</div>

<script>
function fnLogin(){
	return true;
}
</script>