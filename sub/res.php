<?
	include_once("_common.php");
	include_once(G5_PATH."/_head.php");
?>

<div id="res" class="sub_pg">
	<div class="sub_box">
		<div class="form_div">
			<form id="frm" name="frm" method="post">
				<input type="hidden" id="bo_table" name="bo_table" value="res">
				<ul class="tbl001">
					
					<li class="sol_li">
						<h5 style="margin-top:0;">이름</h5>
						<p>
							<input type="text" size="30" maxlength="20" placeholder="" name="wr_subject" id="wr_subject" class="form-control">
							<label for="wr_subject"></label>
						</p>
					</li>
					<li class="sol_li">
						<h5>연락 처</h5>
						<p>
							<input type="text" maxlength="11" name="wr_1" id="wr_1" class="form-control" placeholder="ex) 01012345678">
							<label for="wr_1"></label>
						</p>
					</li>
					<li class="sol_li">
						<h5>이메일</h5>
						<p>
							<input type="text" maxlength="" name="wr_2" id="wr_2" class="form-control" placeholder="ex) abc@naver.com">
							<label for="wr_2"></label>
						</p>
					</li>
					<li class="sol_li">
						<h5>제목</h5>
						<p>
							<input type="text" maxlength="" name="wr_3" id="wr_3" class="form-control" placeholder="ex) 궁금사항">
							<label for="wr_3"></label>
						</p>
					</li>
					<li class="sol_li">
						<h5>문의내용</h5>
						<p>
							<textarea  maxlength="" name="wr_content" id="wr_content" class="form-control"></textarea>
							<label for="wr_content"></label>
						</p>
					</li>
					<li class="sol_li">
						<p class="yak_p">
							<b>목적</b><br>
							가격문의 답변회신<br><br>
							<b>항목</b><br>
							회사명, 담당자, 전화번호<br><br>
							<b>보유 및 이용기간</b><br>
							문의인의 동의 철회 요청시 까지<br><br>
						</p>
						<p style="margin-top:10px">
							<input type="checkbox" id="chk" name="chk" style="margin-top:-6px;"> <label for="chk">개인정보보호정책에 동의합니다.</label>
						</p>
					</li>
				</ul>
			</form>
			<div id="chart" style="padding:30px 0px;text-align:center;width:100%;overflow:hidden;">
				<button class="def_btn bluest" onClick="fnSubmit()">신청하기</button>
			</div>
		</div>
	</div>
</div>

<script>
function fnSubmit(){
	if($("#wr_subject").val() == ""){alert("이름은 필수 사항입니다.");$("#wr_subject").focus();return;	}
	if($("#wr_1").val() == ""){alert("연락처는 필수 사항입니다.");$("#wr_1").focus();return;	}
	if($("#wr_2").val() == ""){alert("이메일은 필수 사항입니다.");$("#wr_2").focus();return;	}
	if($("#wr_3").val() == ""){alert("제목은 필수 사항입니다.");$("#wr_3").focus();return;	}
	if($("#wr_content").val() == ""){alert("문의내용은 필수 사항입니다.");$("#wr_content").focus();return;	}
	
	if($("input:checkbox[id='chk']").is(":checked") == false){
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