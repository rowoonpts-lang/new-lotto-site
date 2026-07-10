<?
	include_once("_common.php");
	include_once(G5_PATH."/_head.php");
?>

<section class="post">
	<h3 class="cs_h3"><b>알파소식 받기&nbsp;</b>알파색채의 새로운소식을 빠르게 받아보고 싶으시다면 신청해주세요!</h3>
	<ul class="post_ul">
		<li class="post_li">
			<img src="<?=G5_THEME_IMG_URL?>/post.jpg" alt="">
		</li>
		<li class="post_li">
			<form id="frm" name="frm" method="post">
				<input type="hidden" id="bo_table" name="bo_table" value="res2">
				<ul class="post_form">
					<li class="post_form_li post_form_li1"><h4>이벤트·행사 알림 소식받기 신청</h4></li>
					<li class="post_form_li post_form_li2 post_text">
						<input type="text" maxlength="20" name="wr_subject" id="wr_subject" class="in_ipt"><span class="input_focus">
						<span>*</span>&nbsp;&nbsp;이름</span>
					</li>
					<li class="post_form_li post_form_li3 post_text">
						<input type="text" size="30" maxlength="11" name="wr_1" id="wr_1" class="in_ipt" onkeyup="value=value.replace(/[^\d]/g,'')">
						<span class="input_focus"><span>*</span>&nbsp;&nbsp;연락처</span>
					</li>
					<li class="post_form_li post_form_li4 post_text">
						<input type="text" maxlength="" name="wr_2" id="wr_2" class="in_ipt">
						<span class="input_focus"><span>*</span>&nbsp;&nbsp;이메일</span>
					</li>
					<li class="post_form_li post_form_li5">
						<input type="checkbox" id="post_agree">
						<label for="post_agree">
							<div class="ck"></div>
							<a onClick="privacy();">개인정보처리방침 내용</a>을 확인하였으며, 동의 합니다.
						</label>
					</li>
					<li class="post_form_li post_form_li6"><button type="button" onClick="fnSubmit()">신청하기</button></li>
				</ul>
				
			</form>
		</li>
	</ul>	
</section>

<div class="prv_pop">
	<p class="prv_x" onClick="prvX();"><i class="fas fa-times"></i></p>
	<div class="prv_wrap">
		<b>목적</b><br>
		알림소식 전달<br><br>
		<b>항목</b><br>
		이름, 연락처, 이메일<br><br>
		<b>보유 및 이용기간</b><br>
		문의인의 동의 철회 요청시까지
	</div>
</div>

<script>
function prvX(){
	$('.prv_pop').hide();
}
function privacy(){
	$('.prv_pop').show();
}

$('.in_ipt').focus(function(){
	$(this).siblings().addClass('off');
});
$('.in_ipt').blur(function(){
	var inVal = $(this).val();
	if(!inVal){
		$(this).siblings().removeClass('off');
	}
});

function fnSubmit(){
	if($("#wr_subject").val() == ""){alert("이름은 필수 사항입니다.");$("#wr_subject").focus();return;	}
	if($("#wr_1").val() == ""){alert("연락처는 필수 사항입니다.");$("#wr_1").focus();return;	}
	if($("#wr_2").val() == ""){alert("이메일은 필수 사항입니다.");$("#wr_2").focus();return;	}
	
	if($("input:checkbox[id='post_agree']").is(":checked") == false){
		alert("개인정보 수집 및 이용에 동의하셔야 합니다.");
		return;
	}

	var string = $("form[name=frm]").serialize();

	$.ajax({
		type: "POST",
		url: "./ajax.submit.php",
		data: string, 
		cache: false,
		async: false,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {
			alert("정상적으로 등록되었습니다.");
			$("#wr_subject").val("");
			$("#wr_1").val("");
			$("#wr_2").val("");
			$("#wr_3").val("");
			$("#wr_content").val("");
		}
	});
	return false;
}
</script>


<?
	include_once(G5_PATH."/_tail.php");
?>