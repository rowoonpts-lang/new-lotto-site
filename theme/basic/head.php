<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
    include_once(G5_THEME_MOBILE_PATH.'/head.php');
    return;
}

include_once(G5_THEME_PATH.'/head.sub.php');
include_once(G5_LIB_PATH.'/latest.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');
include_once(G5_LIB_PATH.'/poll.lib.php');
include_once(G5_LIB_PATH.'/visit.lib.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/popular.lib.php');

$basename=basename($_SERVER["PHP_SELF"]);
include_once(G5_PATH."/sub/head.tit.php");
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/noto-sans.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.rawgit.com/moonspam/NanumSquare/master/nanumsquare.css">
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/earlyaccess/notosanskr.css">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous">

<link rel="stylesheet" href="/css/swiper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.5.0/js/swiper.min.js"></script>

<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
<script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>

<script type="text/javascript" src="/js/jquery.easing.1.3.js"></script>
<script type="text/javascript" src="/js/jquery.nicescroll.js"></script>
<script type="text/javascript" src="http://ianlunn.co.uk/plugins/jquery-parallax/scripts/jquery.scrollTo-1.4.2-min.js"></script>

<?php
if(defined('_INDEX_')) { // index에서만 실행
	include G5_BBS_PATH.'/newwin.inc.php'; // 팝업레이어
}
?>
<?if($basename == "index.php"){?>
<script>
$(document).ready(function() {
	var nice = $("html").niceScroll({
		cursorcolor:"red",
	});  // The document page (body)
});
</script>
<?}?>

<header class="header <?if($basename != "index.php"){?>sub_hd<?}?>">
	<div class="inner">
		<h1 class="logo"><a href="<?=G5_URL?>"><img src="<?=G5_THEME_IMG_URL?>/logo.png" alt=""></a></h1>
		<ul class="gnb">
			<li><a href="<?=G5_URL?>/sub/about.php">로또피크</a></li>
			<li><a href="<?=G5_URL?>/sub/system.php">분석시스템</a></li>
			<li><a href="<?=G5_URL?>/sub/membership.php">멤버쉽</a></li>
			<li><a href="<?=G5_URL?>/sub/data01.php">로또자료실</a></li>
			<li><a href="<?=G5_BBS_URL?>/board.php?bo_table=notice">고객센터</a></li>
			<li><a href="<?=G5_URL?>/sub/my_lotto.php">마이페이지</a></li>
			<li><a href="<?=G5_URL?>/sub/notmessage.php">문자가 오지 않을 때</a></li>
			<li><a href="<?=G5_URL?>/sub/perfect_member.php" style="color:red;font-weight:900">퍼펙트 회원 전용</a></li>
		</ul>
	</div>
</header>


<?if($basename != "index.php") {?>
<div id="sub_div" class="zindex10">
	<?if($sub_top){?>
	<div class="sub_top <?=$addClass?>" style="background:url(<?=G5_THEME_IMG_URL?>/<?=$sub_top?>) no-repeat center/cover;">
		<h2><?=$sub_title?></h2>
	</div>
	<?}?>
	<div class="inner <?=$inner?> <?=$pad0?>">
		<?if($sub_tit){?>
		<?include_once(G5_PATH."/sub/sub_tab.php");?>
		<div class="s3_tit"><?=$sub_tit?></div>
		<?}?>
<?}?>
