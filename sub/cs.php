<?
	include_once("_common.php");
	include_once(G5_PATH."/_head.php");
?>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.2.2/jquery.form.min.js" ></script>
<section class="cs">
	<h3 class="cs_h3"><b>CS·문의&nbsp;</b>알파색채(주)에 궁금한점 또는 불편사항을 보내주시면 담당자가 확인 후 신속히 회신 드리겠습니다.</h3>
	<div id="res">
		<div class="res_box2 mid_box">
			<form id="frm" name="frm" method="post" enctype="multipart/form-data">

				<ul class="res_ul">
					<li class="res_li1">
						<div class="form_div">
							<input type="hidden" id="bo_table" name="bo_table" value="res">
							<ul class="tbl001">
								<li class="sol_li">
									<p>
										<input type="text" maxlength="20" name="wr_1" id="wr_1" class="form-control requ_box in_ipt"><span class="input_focus"><span>*</span>&nbsp;&nbsp;이름</span>	
									</p>
								</li>
								<li class="sol_li">
									<p>
										<input type="text" size="30" maxlength="11" name="wr_2" id="wr_2" class="form-control requ_box in_ipt"><span class="input_focus"><span>*</span>&nbsp;&nbsp;연락처</span>	
									</p>
								</li>
								<li class="sol_li">
									<p>
										<input type="text" maxlength="" name="wr_3" id="wr_3" class="form-control requ_box in_ipt"><span class="input_focus"><span>*</span>&nbsp;&nbsp;이메일</span>	
									</p>
								</li>
								<li class="sol_li">
									<p>
										<input type="text" maxlength="20" name="wr_subject" id="wr_subject" class="form-control requ_box in_ipt"><span class="input_focus"><span>*</span>&nbsp;&nbsp;제목</span>	
									</p>
								</li>
								<li class="sol_li">
									<p>
										<textarea  maxlength="" name="wr_content" id="wr_content" class="form-control in_ipt"></textarea><span class="input_focus"><span>*</span>&nbsp;&nbsp;문의내용</span>	
										<label for="wr_content"></label>
									</p>
								</li>
								<li class="sol_li">
									<?if(!G5_IS_MOBILE){?>
									<p class="mid04_chk">
										<input type="checkbox" id="cs_agree">
										<label for="cs_agree">
											<span class="ck"></span>
											<a onClick="privacy();">개인정보처리방침 내용</a>을 확인하였으며, 동의 합니다.
										</label>
									</p>
									<?}else{?>

									<?}?>
								</li>
							</ul>
							<div id="chart" class="chart_pc" >
								<button class="def_btn bluest" type="button" onClick="fnSubmit()">문의하기</button>
							</div>
						</div>
					</li>
					<li class="res_li2">
						<div class="not_use" style="display:block;">
							<p class="res_li2_tit">첨부 파일 업로드</p>
							<div class="res_li2_txt">
								<ul class="file_ul" id="file_box2">
								</ul>
								<div class="res_file_desc">
									<!-- <span>참고 자료</span>를 여기에 드롭하거나<br> -->
									참고 자료를 [파일추가] 버튼을 이용해 업로드 해주세요.<br>
									(zip, 7z, dwg, stl, 3dm, jpeg, gif, png, pdf, pptx, hwp 등)
								</div>
							</div>
							<ul class="res_li2_file_ul">
								<li class="res_li2_file_li1">첨부 개수 : <span id="upfilecnt">0</span>/4</li>
								<li class="res_li2_file_li2">첨부 용량 : <span id="upfilesize">0</span>/10.0MB</li>
								<li class="res_li2_file_li3"><a onClick="fnAddFile()">파일추가</a></li>
							</ul>
						</div>
					</li>
				</ul>

				<div id="file_add">
					<?for($i=0; $i< 100; $i++){?>
					<input type="file" id="file_<?=$i?>" name="multi_file[]" onChange="fnAddFile_chg()">
					<?}?>
				</div>
				
				<?if(!G5_IS_MOBILE){?>
									
				<?}else{?>
						<p class="mid04_chk">
							<input type="checkbox" id="cs_agree">
							<label for="cs_agree">
								<span class="ck"></span>
								<a onClick="privacy();">개인정보처리방침 내용</a>을 확인하였으며, 동의 합니다.
							</label>
						</p>		
				<?}?>
				<div id="chart" class="chart_mb" >
					<button class="def_btn bluest" type="button" onClick="fnSubmit()">문의하기</button>
				</div>
			</form>

		</div>
	</div>
</section>

<div class="prv_pop">
	<p class="prv_x" onClick="prvX();"><i class="fas fa-times"></i></p>
	<div class="prv_wrap">
		<b>목적</b><br>
		상담문의 답변회신<br><br>
		<b>항목</b><br>
		이름, 연락처, 이메일, 제목, 문의내용<br><br>
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

var file_i = 0;
var tot_i = 0;
var tot_size = 0;
function fnAddFile(){
	if(tot_i >= 4){
		alert("첨부개수 허용수가 초과되었습니다.");
		return false;
	}
	$('#file_'+file_i).trigger('click');
}
function fnAddFile_chg(){
	var fileValue = $('#file_'+file_i).val().split("\\");
	var fileName = fileValue[fileValue.length-1]; // 파일명

	/*thumbext = fileName.slice(fileName.indexOf(".") + 1).toLowerCase();
	if(thumbext != "jpg" && thumbext != "png" &&  thumbext != "gif" &&  thumbext != "bmp"){ //확장자를 확인합니다.
		alert("첨부파일의 확장자를 체크해주세요.");
		$('#file_'+file_i).val('');
		return false;
	}*/

	fileSize = document.getElementById("file_"+file_i).files[0].size/1024/1024;
	if((fileSize*1) + (tot_size*1) >= 10){
		alert("첨부파일 용량이 초과하였습니다.");
		$('#file_'+file_i).val('');
		return false;
	}


	var addFileDesc = '<p class="file_box2_p" id="file_desc_'+file_i+'"><span><img src="<?=G5_THEME_IMG_URL?>/x_bar.png" onClick="fnDelFile('+file_i+')"></span>'+fileName+'</p>';
	var addFileDesc	= '<li id="file_desc_'+file_i+'">';
		addFileDesc	+=		'<dl>';
		addFileDesc	+=			'<dd class="file_dl_img"><i class="fas fa-file-upload"></i></dd>';
		addFileDesc	+=			'<dd class="file_dl_subject">'+fileName+'</dd>';
		addFileDesc	+=			'<dd class="file_dl_del"><button type="button" onClick="fnDelFile('+file_i+')">삭제</button></dd>';
		addFileDesc	+=		'</dl>';
		addFileDesc	+='</li>';

	$("#file_box2").append(addFileDesc);
	file_i++;
	tot_i++;
	tot_size = (tot_size*1)+(fileSize*1);

	$("#upfilecnt").text(tot_i);
	$("#upfilesize").text(Math.round(tot_size*10)/10);

	if(tot_i > 0){
		$(".res_file_desc").hide();
	}
	

}
function fnDelFile(i){
	fileSize = document.getElementById("file_"+i).files[0].size/1024/1024;
	tot_size = (tot_size*1)-(fileSize*1);
	$('#file_'+i).val('');
	$("#file_desc_"+i).remove();
	tot_i--;

	$("#upfilecnt").text(tot_i);
	$("#upfilesize").text(Math.round(tot_size*10)/10);
	if(tot_i < 1){
		$(".res_file_desc").show();
	}
}
</script>

<script>
function fnSubmit(){
	if($("#wr_1").val() == ""){alert("이름은 필수 사항입니다.");$("#wr_1").focus();return;	}
	if($("#wr_2").val() == ""){alert("연락처는 필수 사항입니다.");$("#wr_2").focus();return;	}
	if($("#wr_3").val() == ""){alert("이메일은 필수 사항입니다.");$("#wr_3").focus();return;	}
	//if($("#wr_password").val() == ""){alert("비밀번호는 필수 사항입니다.");$("#wr_password").focus();return;	}
	if($("#wr_subject").val() == ""){alert("제목은 필수 사항입니다.");$("#wr_subject").focus();return;	}
	if($("#wr_content").val() == ""){alert("문의내용은 필수 사항입니다.");$("#wr_content").focus();return;	}

	
	if($("input:checkbox[id='cs_agree']").is(":checked") == false){
		alert("개인정보 수집 및 이용에 동의하셔야 합니다.");
		return;
	}

	//var formData = new FormData(); 
	var formData = new FormData($('#frm')[0]);

	
	$.ajax({ 
		url: '<?=G5_URL?>/sub/ajax.submit2.php',
		enctype: 'multipart/form-data', // 필수
		data: formData, 
		processData: false, 
		contentType: false,
		type: 'POST', 
		success: function (result) { 
			alert("정상적으로 등록되었습니다.");
			location.reload();
		}, 
		error: function (e) { 
			alert("정상적으로 등록되었습니다.");
			location.reload();
		}
			
	});
	return false;
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
</script>


<?
	include_once(G5_PATH."/_tail.php");
?>