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
include_once(G5_LIB_PATH.'/thumbnail.lib.php');
include_once(G5_PATH.'/sub/head.tit.php');
?>

<link rel="stylesheet" type="text/css" href="https://cdn.rawgit.com/moonspam/NanumSquare/master/nanumsquare.css">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous">

<link rel="stylesheet" href="<?=G5_CSS_URL?>/swiper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/4.5.0/js/swiper.min.js"></script>

<link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,600,700,800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?=G5_CSS_URL?>/style.css" />
<link rel="stylesheet" href="<?=G5_CSS_URL?>/aos.css" />

<?php
if(defined('_INDEX_')) { // index에서만 실행
	include G5_BBS_PATH.'/newwin.inc.php'; // 팝업레이어
}
?>


<header class="header">
	<div class="inner">
		<ul class="header_ul_1">
			<li class="logo"><a href="<?=G5_URL?>"><img src="<?=G5_THEME_IMG_URL?>/logo.png" alt="로또중심"></a></li>
			<li class="top_menu">
				<?php if ($is_member) {  ?>
				<!--div><a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=<?php echo G5_BBS_URL ?>/register_form.php">정보수정</a></div-->
				<div><a href="<?php echo G5_BBS_URL ?>/logout.php">로그아웃</a></div>
				<div><a href="<?=G5_URL?>/sub/my_lotto.php">마이페이지</a></div>
				<?php if ($is_admin) {  ?>
				<div class="eng"><a href="<?php echo G5_ADMIN_URL ?>">관리자</a></div>
				<?php }  ?>
				<?php } else {  ?>
				<div><a href="<?php echo G5_BBS_URL ?>/login.php">로그인</a></div>
				<div><a href="<?php echo G5_BBS_URL ?>/register.php">회원가입</a></div>
				<?php }  ?>
				<!-- <div class="eng"><a class="pop_res_open" onClick="fnShowpop()">1등당첨번호 상담요청</a></div> -->
				<div><a href="<?=G5_URL?>/sub/sub0301.php">문자가 오지 않을 때</a></div>
			</li>
		</ul>
		<ul class="header_ul_2">
			<li>
				<ul class="menu clearfix">
					<li>
						<a href="<?=G5_URL?>/sub/sub0102.php">회사소개</a>
						<ul class="sub_menu">
							<li><a href="<?=G5_URL?>/sub/sub0102.php">인사말</a></li>
							<li><a href="<?=G5_URL?>/sub/sub0101.php">분석프로그램</a></li>
						</ul>
					</li>
					<li>
						<a href="<?=G5_URL?>/sub/sub0201.php">등급안내</a>
					</li>
<!-- 					<li>
						<a href="<?=G5_URL?>/sub/prize.php">당첨지역</a>
					</li> -->
					<li>
						<a href="<?=G5_URL?>/sub/stats.php">통계분석실</a>
						<ul class="sub_menu">
							<li><a href="<?=G5_URL?>/sub/stats.php">로또 분석용어</a></li>
							<li><a href="<?=G5_URL?>/sub/stats2.php">확률과 조합 분석</a></li>
							<li><a href="<?=G5_URL?>/sub/stats3.php">로또 구입 잘하는법</a></li>
						</ul>
					</li>
					<li>
						<a href="<?=G5_URL?>/bbs/board.php?bo_table=notice_">고객센터</a>
						<ul class="sub_menu">
							<li><a href="<?=G5_URL?>/bbs/board.php?bo_table=notice_">공지사항</a></li>
							<li><a href="<?=G5_URL?>/bbs/board.php?bo_table=faq">자주묻는 질문</a></li>
							<li><a href="<?=G5_URL?>/bbs/qalist.php">1:1 상담</a></li>
						</ul>
					</li>
					<li>
						<a href="<?=G5_URL?>/sub/my_lotto.php">마이페이지</a>
					</li>
					<li>
						<a href="<?=G5_URL?>/sub/deluxe.php">디럭스 그룹</a>
					</li>
				</ul>
			</li>
		</ul>
	</div>
</header>

<div class="pop_res">
<div class="pop_res_bg"  onClick="fnHidepop()"></div>
<div class="pop_res_cont">
	<div class="pop_res_close" onClick="fnHidepop()"><i class="fas fa-times"></i></div>
	<form id="frm_1" name="frm_1" method="post">
	<input type="hidden" name="lr_type" id="lr_type_1" value="결제시도">
	<input type="hidden" name="lr_etc" id="lr_etc_1" value="">
		<ul class="pop_res_ul">
			<li class="pop_res_li1">로또 1등 당첨번호 받기</li>
			<li class="pop_res_li2">항상 친절하게 상담하겠습니다.<br>로또중심에 궁금하시 점을 해결해드립니다.</li>
			<li class="pop_res_li3"><input type="text" placeholder="이름을 입력해 주세요." name="lr_name" id="lr_name_1"></li>
			<li class="pop_res_li4">
				<select id="lr_hp1_1" name="lr_hp1">
					<option value="010">010</option>
					<option value="02">02</option>
					<option value="011">011</option>
					<option value="016">016</option>
					<option value="019">019</option>
					<option value="070">070</option>
				</select><span>-</span>
				<input type="text" maxlength="4" id="lr_hp2_1" name="lr_hp2"><span>-</span>
				<input type="text" maxlength="4" id="lr_hp3_1" name="lr_hp3">
			</li>
			<li class="pop_res_li5">
				<div class="ck_box">
					<input type="checkbox" name="chk" id="chk_1" checked>
					<label for="chk_1">로또중심 이용약관</label>
				</div>
				<div class="ck_box">
					<input type="checkbox" name="chk2" id="chk2_1" checked>
					<label for="chk2_1">개인정보처리방침</label>
				</div>
			</li>
			<li class="pop_res_li6"><button type="button"  onclick="fnSubmit('_1')">신청하기</button></li>
		</ul>
	</form>
</div>
</div>

<script>
function fnSubmit(v){
	if($("#lr_name"+v).val() == ""){alert("이름은 필수 사항입니다.");$("#lr_name"+v).focus();return;	}

	if($("#lr_hp1"+v).val() == ""){alert("연락처는 필수 사항입니다.");$("#lr_hp1"+v).focus();return;	}
	if($("#lr_hp2"+v).val() == "" || $("#lr_hp2"+v).val().length < 3){alert("연락처는 필수 사항입니다.");$("#lr_hp2"+v).focus();return;	}
	if($("#lr_hp3"+v).val() == "" || $("#lr_hp3"+v).val().length < 4){alert("연락처는 필수 사항입니다.");$("#lr_hp3"+v).focus();return;	}
	
	if($("input:checkbox[id='chk"+v+"']").is(":checked") == false){
		alert("이용약관에 동의하셔야 합니다.");
		return;
	}

	if($("input:checkbox[id='chk2"+v+"']").is(":checked") == false){
		alert("개인정보처리방침에 동의하셔야 합니다.");
		return;
	}

	var string = $("form[name=frm"+v+"]").serialize();

	$.ajax({
		type: "POST",
		url: "<?=G5_URL?>/sub/ajax.res.php",
		data: string, 
		cache: false,
		async: false,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {
			alert("정상적으로 등록되었습니다.");
			$("#lr_name"+v).val("");
			//$("#lr_type"+v).val("");
			$("#lr_hp2"+v).val("");
			$("#lr_hp3"+v).val("");
			location.reload();
		}
	});
	return false;
}

	$(document).ready(function(){
		$('.menu > li').mouseenter(function(){
			//$(this).children().siblings('.sub_menu').stop().slideDown(200);
			$(this).children().siblings('.sub_menu').show();
		});
		$('.menu > li').mouseleave(function(){
			//$(this).children().siblings('.sub_menu').stop().slideUp(200);
			$(this).children().siblings('.sub_menu').hide();
		});

	});

	function fnShowpop(v){
		$('html, body').css('overflow-y','hidden');
		$('.pop_res').fadeIn(400);
		var v_val = v;
		
		if(v_val == 1){
			$('.pop_res_li1').text('실버 상담 문의');
			$('#lr_etc_1').val("실버");
		}
		else if(v_val == 2){
			$('.pop_res_li1').text('골드 상담 문의');
			$('#lr_etc_1').val("골드");
		}
		else if(v_val == 3){
			$('.pop_res_li1').text('플래티넘 상담 문의');
			$('#lr_etc_1').val("플래티넘");
		}
		else if(v_val == 4){
			$('.pop_res_li1').text('디럭스 그룹');
			$('#lr_etc_1').val("디럭스 그룹");
		}
		else{
			$('.pop_res_li1').text('로또 1등 당첨번호 받기');
		}
	}

	function fnHidepop(v){
		$('html, body').css('overflow-y','visible');
		$('.pop_res').fadeOut(400);
	}
</script>


<?if($basename != "index.php") {?>
<div id="sub_div">
	<?if($basename != "sub0101.php" && $basename != "sub0102.php" && $basename != "sub0201.php" && $basename != "prize.php" && $basename != "sub0301.php" && $basename != "deluxe.php") {?>
	<div class="sub_top <?=$sub_top_bg?>">
		<div class="s01_li1">LOTTO JOONGSI<span>M</span></div>
		<div class="s01_li2"><?=$s01_li2?></div>
		<ul class="sub_top_ul flex_center">
			<?=$sub_top_li1?>
			<?=$sub_top_li2?>
			<?=$sub_top_li3?>
		</ul>
	</div>
	<div class="sub_tit"><?=$sub_tit?></div>
	<?}?>
	<div class="inner_3 <?=$inner_x?>">
<?}?>