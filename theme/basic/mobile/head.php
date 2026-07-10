<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가


include_once(G5_THEME_PATH.'/head.sub.php');
include_once(G5_LIB_PATH.'/latest.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');
include_once(G5_LIB_PATH.'/poll.lib.php');
include_once(G5_LIB_PATH.'/visit.lib.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/popular.lib.php');

$basename=basename($_SERVER["PHP_SELF"]);
include_once(G5_PATH.'/sub/head.tit.php');
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
	include G5_MOBILE_PATH.'/newwin.inc.php'; // 팝업레이어
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

<header id="hd">
    <div id="hd_wrapper">

        <div id="logo">
            <a href="<?php echo G5_URL ?>"><img src="<?=G5_THEME_IMG_URL?>/logo.png" alt=""></a>
        </div>

        <button type="button" id="gnb_open" class="hd_opener"><i class="fa fa-bars" aria-hidden="true"></i><span class="sound_only"> 메뉴열기</span></button>

        <div id="gnb" class="hd_div">
            <button type="button" id="gnb_close" class="hd_closer"><i class="fa fa-close" aria-hidden="true"></i></button>
			<?if($is_member){?>
			<p class="main_login_info"><span style="font-weight:600;color:#e31b23"><?=$member['mb_name']?>(<?=$member['mb_type']?>)</span> 회원님<br>환영합니다.</p>
			<?}?>
            <ul id="gnb_1dul">
				<li class="gnb_1dli">
                    <a href="<?=G5_URL?>/sub/about.php" class="gnb_1da">로또피크</a>
				</li>
				<li class="gnb_1dli">
                    <a href="<?=G5_URL?>/sub/system.php" class="gnb_1da">분석시스템</a>
				</li>
				<li class="gnb_1dli">
                    <a href="<?=G5_URL?>/sub/membership.php" class="gnb_1da">멤버쉽</a>
				</li>
				<li class="gnb_1dli">
                    <a href="<?=G5_URL?>/sub/data01.php" class="gnb_1da">로또자료실</a>
					<button type="button" class="btn_gnb_op">하위분류</button>
					<ul class="gnb_2dul">
						 <li class="gnb_2dli"><a href="<?=G5_URL?>/sub/data01.php" class="gnb_2da">로또 분석용어</a></li>
						 <li class="gnb_2dli"><a href="<?=G5_URL?>/sub/data02.php" class="gnb_2da">확률과 조합 분석</a></li>
						 <li class="gnb_2dli"><a href="<?=G5_URL?>/sub/data03.php" class="gnb_2da">로또 구입 잘하는 방법</a></li>
					</ul>
				</li>
				<li class="gnb_1dli">
                    <a href="<?=G5_BBS_URL?>/board.php?bo_table=notice" class="gnb_1da">고객센터</a>
					<button type="button" class="btn_gnb_op">하위분류</button>
					<ul class="gnb_2dul">
						 <li class="gnb_2dli"><a href="<?=G5_BBS_URL?>/board.php?bo_table=notice" class="gnb_2da">공지사항</a></li>
						 <li class="gnb_2dli"><a href="<?=G5_BBS_URL?>/qalist.php" class="gnb_2da">1:1 문의</a></li>
					</ul>
				</li>
				<?php if ($is_member) {  ?>
				<li class="gnb_1dli">
                    <a href="<?php echo G5_BBS_URL ?>/logout.php" class="gnb_1da">로그아웃</a>
				</li>
				<li class="gnb_1dli">
                    <a href="<?=G5_URL?>/sub/my_lotto.php" class="gnb_1da">마이페이지</a>
					<button type="button" class="btn_gnb_op">하위분류</button>
					<ul class="gnb_2dul">
						 <li class="gnb_2dli"><a href="<?=G5_URL?>/sub/my_lotto.php" class="gnb_2da">나의 당첨현황</a></li>
						 <li class="gnb_2dli"><a href="<?=G5_URL?>/sub/my_lotto02.php" class="gnb_2da">나의 로또 당첨 현황</a></li>
						 <li class="gnb_2dli"><a href="<?=G5_URL?>/sub/my_lotto03.php" class="gnb_2da">로또</a></li>
					</ul>
				</li>
				<?php if ($is_admin) {  ?>
				<li class="gnb_1dli">
                    <a href="<?php echo G5_ADMIN_URL ?>" class="gnb_1da">관리자</a>
				</li>
				<?php }  ?>
				<?php } else {  ?>
				<li class="gnb_1dli">
                    <a href="<?php echo G5_BBS_URL ?>/login.php" class="gnb_1da">로그인</a>
				</li>
				<li class="gnb_1dli">
                    <a href="<?php echo G5_BBS_URL ?>/register.php" class="gnb_1da">회원가입</a>
				</li>
				<?php }  ?>
				<li class="gnb_1dli"><a href="<?=G5_URL?>/sub/notmessage.php" class="gnb_1da">문자가 오지 않을 때</a></li>
				<li class="gnb_1dli"><a href="<?=G5_URL?>/sub/perfect_member.php" class="gnb_1da" style="color:red">퍼펙트 회원 전용</a></li>
			</ul>
			
        </div>

        <script>
        $(function () {

            $(".hd_opener").on("click", function() {
                var $this = $(this);
                var $hd_layer = $this.next(".hd_div");

                if($hd_layer.is(":visible")) {
                    $hd_layer.hide();
                    $this.find("span").text("열기");
                } else {
                    var $hd_layer2 = $(".hd_div:visible");
                    $hd_layer2.prev(".hd_opener").find("span").text("열기");
                    $hd_layer2.hide();

                    $hd_layer.show();
                    $this.find("span").text("닫기");
                }
            });

            $("#container").on("click", function() {
                $(".hd_div").hide();

            });

            $(".btn_gnb_op").click(function(){
                $(this).toggleClass("btn_gnb_cl").next(".gnb_2dul").slideToggle(300);
                
            });

            $(".hd_closer").on("click", function() {
                var idx = $(".hd_closer").index($(this));
                $(".hd_div:visible").hide();
                $(".hd_opener:eq("+idx+")").find("span").text("열기");
            });
        });
        </script>
        
    </div>
</header>

<?if($basename != "index.php") {?>
<div id="sub_div" class="zindex10">
	<?if($sub_top){?>
	<div class="sub_top" style="background:url(<?=G5_THEME_IMG_URL?>/<?=$sub_top?>) no-repeat center/cover;">
		<h2><?=$sub_title?></h2>
	</div>
	<?}?>
	<div class="inner <?=$inner?> <?=$pad0?>">
		<?if($sub_tit){?>
		<?include_once(G5_PATH."/sub/sub_tab.php");?>
		<div class="s3_tit"><?=$sub_tit?></div>
		<?}?>
<?}?>
