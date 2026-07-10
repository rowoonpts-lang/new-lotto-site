<?
	include_once("_common.php");
	$gstrPartnerImgUrl = G5_URL."/event/event2";
	
	if (G5_IS_MOBILE) {
		include_once('./m.php');
		return;
	}
	include_once(G5_PATH.'/head.sub.php');
?>

<script src="<?=G5_JS_URL?>/jquery-1.8.3.min.js"></script>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?=G5_CSS_URL?>/noto-sans.css" />

<link type="text/css" rel="stylesheet" href="<?=$gstrPartnerImgUrl?>/css/mstyle.css" />

<div>
	<ul class="pc">
		<li>
			<img class="image100" src="<?=$gstrPartnerImgUrl?>/img/01.jpg" />
			<a href="javascript:fnPopOn()"></a>
		</li>
		<li>
			<img class="image100" src="<?=$gstrPartnerImgUrl?>/img/02.jpg" />
			<a href="javascript:fnPopOn()"></a>
		</li>
		<li>
			<img class="image100" src="<?=$gstrPartnerImgUrl?>/img/03.jpg" />
			<a href="javascript:fnPopOn()"></a>
		</li>
		<li>
			<img class="image100" src="<?=$gstrPartnerImgUrl?>/img/04.jpg" />
			<a href="javascript:fnPopOn()"></a>
		<!--</li>
		<li>
			<img class="image100" src="<?=$gstrPartnerImgUrl?>/img/05.jpg" />
			<a href="http://www.lottoclick.co.kr/sub/sub0201.php"></a>-->
		</li>
	</ul>
</div> 

<div class="form_pop">
	<div class="pop_bg" onclick="fnPopOff()"></div>
	<div class="pop_con">
		<form name="app_fm2" id="form2" class="form" action="./order_update.php" onsubmit="return fwrite_submit(this);" method="post" autocomplete="off" data-landing="1">
            <input type="hidden" name="w" value="">
			<input type="hidden" name="lu_type" value="PB1">
			<input type="hidden" name="lu_code" value="PB1">
			<div class="main2_res">
				<div class="subtit font_mont">LOTTO CLICK</div>
				<div class="tit">로또1등 당첨번호!<br>무료로 받아보세요</div>
				<ul class="main2_resul">
					<li><input type="text" class="text required" id="f21" name="name" required placeholder="이름을 입력하세요."></li>
					<li>
						<input type="tel" name="tel" id="f22" required class="text required telcellnum" maxlength="14" placeholder="연락처를 입력하세요…">
						<!--input class="input_tp input_tp2" type="text" name="" id="" maxlength="3" value="010" readonly="">
						<span>-</span>
						<input class="input_tp input_tp2" type="text" name="" id="" maxlength="4">
						<span>-</span>
						<input class="input_tp input_tp2" type="text" name="" id="" maxlength="4"-->
					</li>
				</ul>
				<div class="main2_ckbox">
					<input type="checkbox" name="agree1" value="1" checked>
					<label for="chk">개인정보 수집/이용 동의</label>
					<a class="main2_ck_a" href="<?=G5_BBS_URL?>/content.php?co_id=privacy" target="_blank">more +</a>
				</div>
				<button class="main2_btn">무료번호받기</button>
			</div>
		</form>
	</div>
</div>

<script>
function fnPopOn(){
	$(".form_pop").show();
}
function fnPopOff(){
	$(".form_pop").hide();
}

function fwrite_submit(f) {

    if(!f.agree1.checked){
        alert("개인정보취급방침에 동의가 필요합니다.");
        f.agree1.focus();
        return false;
    }

    if(!f.agree2.checked){
        alert("마케팅정보수신에 동의가 필요합니다.");
        f.agree2.focus();
        return false;
    }

    return true;
}
</script>

<?php
include_once(G5_PATH.'/tail.php');
?>