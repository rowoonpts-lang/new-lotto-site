<?
	include_once("_common.php");

	session_unset(); // 모든 세션변수를 언레지스터 시켜줌
	session_destroy(); // 세션해제함

	// 자동로그인 해제 --------------------------------
	set_cookie('ck_mb_id', '', 0);
	set_cookie('ck_auto', '', 0);
	// 자동로그인 해제 end --------------------------------

	goto_url(G5_LADMIN_URL."/login.php");
?>
