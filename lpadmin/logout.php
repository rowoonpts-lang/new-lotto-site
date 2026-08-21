<?php

/*
 * 로그아웃은 관리자 IP 제한과 관계없이 항상 실행할 수 있어야 하므로
 * lpadmin/_common.php 대신 사이트 공통 파일을 직접 불러옵니다.
 */
include_once("../common.php");

session_unset();
session_destroy();

/* 자동로그인 해제 */
set_cookie('ck_mb_id', '', 0);
set_cookie('ck_auto', '', 0);

/*
 * 관리자 전용 로그인 화면이 아니라
 * 홈페이지 첫 화면으로 이동합니다.
 */
goto_url(G5_URL);
?>
