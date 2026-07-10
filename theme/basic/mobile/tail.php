<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가


if($basename != "index.php") {
?>
	</div>
</div>
<?}?>


<footer id="footer" class="zindex10">
	<!--div class="ft_1">
		<div class="inner">
			<div class="ft_1_wrap">
				<div class="ft_1_div">
					<p class="ft_1_ic"><img src="<?=G5_THEME_IMG_URL?>/ft_1_img1.png" alt=""></p>
					<div class="ft_1_box">
						<p class="ft_1_txt3">1800 - 6803</p>
					</div>
				</div>
				<div class="ft_1_div">
					<p class="ft_1_ic"><img src="<?=G5_THEME_IMG_URL?>/ft_1_img2.png" alt=""></p>
					<div class="ft_1_box">
						<p class="ft_1_txt1">월 - 금 10:00 ~ 18:00</p>
						<p class="ft_1_txt2">점심시간 12:30 ~ 13:30 ㅣ 주말·공휴일 휴무</p>
					</div>
				</div>
				<div class="ft_1_div">
					<p class="ft_1_ic"><img src="<?=G5_THEME_IMG_URL?>/ft_1_img3.png" alt=""></p>
					<div class="ft_1_box">
						<p class="ft_1_txt1">농협 301-0299-9334-81</p>
						<p class="ft_1_txt2">예금주 : 박기범(비케이솔루션)</p>
					</div>
				</div>
			</div>
		</div>
	</div-->
	<div class="ft_2">
		<div class="inner">
			<div class="ft_2_wrap">
				<!--h1 class="ft_logo"><img src="<?=G5_THEME_IMG_URL?>/ft_logo.png" alt=""></h1-->
				<div class="ft_info">
					<p class="ft_comp">
						<a href="<?=G5_BBS_URL?>/content.php?co_id=privacy">개인정보보호방침</a>
						<a href="<?=G5_BBS_URL?>/content.php?co_id=provision">이용약관</a>
					</p>
					<p class="ft_2_txt ft_addr">
						디 컴퍼니 ㅣ 대표 : 최화정 ㅣ 개인정보책임자 : 최화정 ㅣ 사업자등록번호 : 562-61-00552<br>
						E-MAIL : dee_company@naver.com ㅣ 주소 : <!-- 인천광역시 미추홀구 경원대로 834번길 27-12, 3층 301호 302호 308호 (주안동,NS빌딩) -->경기도 안양시 동안구 시민대로 187 
					</p>
					<p class="ft_2_txt ft_desc">
						당사의 분석시스템은 전체 로또번호 조합 중 등급별 압축 필터링한 조합 정보제공만을 목적으로 하며,<br>
						당첨 확정 서비스가 아니므로 서비스 이용 과정에서 기대이익을 얻지 못하거나 발생한 손해 등에 대한 최종책임은 서비스 이용자 본인에게 있습니다.
					</p>
				</div>
			</div>
		</div>
	</div>
	<div class="ft_3">
		<div class="inner">
			<p class="ft_copy">Copyright ⓒ 디 컴퍼니 All rights reserved</p>
		</div>
	</div><map name="">
		<area shape="" href="" coords="" alt="">
	</map>
</footer>

<div class="mbs_back"></div>
<div class="mbs_pop">
	<p class="mbs_pop_x" onClick="fnBbsPopOff();"><img src="<?=G5_THEME_IMG_URL?>/mbs_pop_x.png" alt=""></p>
	<form id="frm_2" name="frm_2" method="post">
		<input class="input_tp input_tp1" type="hidden" name="lr_type" id="lr_type_2" value="상담요청">
		<p class="mbs_pop_icon"><img src="<?=G5_THEME_IMG_URL?>/mbs_pop_icon.png" alt=""></p>
		<h3 class="mbs_pop_tit">정회원 상담 문의</h3>
		<p class="mbs_pop_desc">
			무엇이 궁금하신가요?<br>
			로또피크가 친절하게 상담해드리겠습니다!
		</p>
		<input type="text" name="lr_name" id="lr_name_2" class="mbs_pop_ipt" placeholder="성함을 입력해주세요.">
		<div class="mbs_frm_tel">
			<input type="text" name="lr_hp1" id="lr_hp1_2" class="mbs_pop_ipt" placeholder="" maxlength="3" onkeyup="value=value.replace(/[^\d]/g,'')">
			<p>-</p>
			<input type="text" name="lr_hp2" id="lr_hp2_2" class="mbs_pop_ipt" maxlength="4" onkeyup="value=value.replace(/[^\d]/g,'')">
			<p>-</p>
			<input type="text" name="lr_hp3" id="lr_hp3_2" class="mbs_pop_ipt" maxlength="4" onkeyup="value=value.replace(/[^\d]/g,'')">
		</div>
		<ul class="mbs_pop_chk">
			<li>
				<input type="checkbox" id="res_chk1">
				<label for="res_chk1" checked>로또피크 이용약관 동의</label>
			</li>
			<li>
				<input type="checkbox" checked name="chk2" id="chk2_2">
				<label for="chk2_2">개인정보처리방침</label>
			</li>
		</ul>
		<button type="button" class="mbs_pop_btn" onclick="fnSubmit('_2')">신청하기</button>
	</form> 
</div>

<script>
function fnSubmit(v){
	if($("#lr_name"+v).val() == ""){alert("이름은 필수 사항입니다.");$("#lr_name"+v).focus();return;	}

	if($("#lr_hp1"+v).val() == ""){alert("연락처는 필수 사항입니다.");$("#lr_hp1"+v).focus();return;	}
	if($("#lr_hp2"+v).val() == "" || $("#lr_hp2"+v).val().length < 3){alert("연락처는 필수 사항입니다.");$("#lr_hp2"+v).focus();return;	}
	if($("#lr_hp3"+v).val() == "" || $("#lr_hp3"+v).val().length < 4){alert("연락처는 필수 사항입니다.");$("#lr_hp3"+v).focus();return;	}
	
	/*if($("input:checkbox[id='chk"+v+"']").is(":checked") == false){
		alert("이용약관에 동의하셔야 합니다.");
		return;
	}*/

	if($("input:checkbox[id='chk2"+v+"']").is(":checked") == false){
		alert("개인정보처리방침에 동의하셔야 합니다.");
		return;
	}

	var string = $("form[name=frm"+v+"]").serialize();

	$.ajax({
		type: "POST",
		url: "/sub/ajax.res.php",
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

function fnBbsPopOn(v){
	$(".mbs_back, .mbs_pop").show();
	$("#wr_category").val(v);
}
function fnBbsPopOff(){
	$(".mbs_back, .mbs_pop").hide();
	$("#wr_category").val("");
}

function fnResSubmit(v){
	if($("#wr_subject"+v).val() == ""){
		alert("성함을 입력해주세요.");
		$("#wr_subject"+v).focus();
		return false;
	}
	if($("#wr_tel_1"+v).val() == ""){
		alert("연락처를 입력해주세요.");
		$("#wr_tel_1"+v).focus();
		return false;
	}
	if($("#wr_tel_2"+v).val() == ""){
		alert("연락처를 입력해주세요.");
		$("#wr_tel_2"+v).focus();
		return false;
	}
	if($("#wr_tel_3"+v).val() == ""){
		alert("연락처를 입력해주세요.");
		$("#wr_tel_3"+v).focus();
		return false;
	}

	if(v == "_2"){
	
	}
}
</script>

<?php
if(G5_DEVICE_BUTTON_DISPLAY && !G5_IS_MOBILE) { ?>
<?php
}

if ($config['cf_analytics']) {
    echo $config['cf_analytics'];
}
?>


<!-- } 하단 끝 -->



<?php
include_once(G5_THEME_PATH."/tail.sub.php");
?>