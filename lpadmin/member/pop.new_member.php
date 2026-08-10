<?php
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.sub.php");
?>
<!-- Main content -->
<section class="content">
	<div class="container-fluid">
	<div class="row">
		<!-- left column -->
		<div class="col-md-12 col-12">
			<!-- general form elements -->
			<div class="card card-primary">
				<div class="card-header">
					<h3 class="card-title">대리가입</h3>
				</div>
				<!-- /.card-header -->
				<!-- form start -->
				<form name="frm" id="frm" role="form" method="post" autocomplete="off" action="member.save.php" onSubmit="return fnSubmit();">
				<input type="hidden" id="mb_hp_chk" value="0">
				<input type="hidden" id="mb_id_chk" value="0">
					<div class="card-body">
						<div class="form-group">
							<label for="mb_hp">휴대폰번호</label>
							<div class="row">
								<div class="col-8">
									<input type="text" class="form-control" id="mb_hp" name="mb_hp" placeholder="">
								</div>
								<div class="col-4">
									<button type="button" class="btn btn-block btn-primary" onClick="fnFindHP();">중복검사</button>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="mb_name">이름</label>
							<input type="text" class="form-control" id="mb_name" name="mb_name" placeholder="" required>
						</div>
						<div class="form-group">
							<label for="mb_id">아이디</label>
							<div class="row">
								<div class="col-8">
									<input type="text" class="form-control" id="mb_id" name="mb_id" placeholder="">
								</div>
								<div class="col-4">
									<button type="button" class="btn btn-block btn-primary" onClick="fnFindID()">중복검사</button>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="mb_id">DB경로</label>
							<div class="row">
								<div class="col-12">
									<input type="text" class="form-control" id="mb_db" name="mb_db" placeholder="">
								</div>
							</div>
						</div>
						<!--div class="form-group">
							<label for="mb_password">패스워드</label>
							<input type="text" class="form-control" id="mb_password" name="mb_password" placeholder="" required>
						</div-->
						<input type="hidden" name="mb_type" value="무료회원">
						<?php if (lottoCanCreateStaff($member['mb_level'])) { ?>
						<div class="form-group">
							<label for="mb_level">권한설정</label>
							<select class="form-control" id="mb_level" name="mb_level">
								<option value="2">일반회원</option>
								<option value="<?=LOTTO_ROLE_STAFF1?>">직원1</option>
								<option value="<?=LOTTO_ROLE_STAFF2?>">직원2</option>
								<option value="<?=LOTTO_ROLE_TEAM_LEADER?>">팀장</option>
								<option value="<?=LOTTO_ROLE_ADMIN?>">관리자</option>
							</select>
						</div>
						<?php } ?>
					</div>
				</div>
				<!-- /.card-body -->

				<div class="card-footer">
					<button type="submit" class="btn btn-primary">생성</button>
				</div>
			</form>
		</div>
	</div>
</section>
<!-- /.card -->
<script>
function fnFindHP(){
	var mb_hp = $("#mb_hp").val().replace(/ /gi,'');
	mb_hp = mb_hp.replace(/-/gi,'');
	$("#mb_hp").val(mb_hp);
	if(mb_hp == ""){alert("휴대폰 번호를 입력해주세요.");return false;}
	
	$.ajax({
		type: "POST",
		url: "<?=G5_URL?>/ajax/ajax.find.mb_hp.php",
		data: {mb_hp : mb_hp}, 
		cache: false,
		async: false,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {
			if(data*1 > 0){
				alert("현재 사용중인 휴대폰 번호입니다.");
				$("#mb_hp").val('');
				$("#mb_id").val('');
				return false;
			}else{
				alert("사용이 가능한 휴대폰 번호입니다.");
				$("#mb_id").val(mb_hp);
				$("#mb_hp").attr('readonly', true);
				$("#mb_hp_chk").val("1");
				fnGetDb(mb_hp);
				return false;
			}
		}
	});
	return false;
}
function fnGetDb(mb_hp){
	$.ajax({
		type: "POST",
		url: "<?=G5_LADMIN_URL?>/member/ajax.find.db.php",
		data: {mb_hp : mb_hp}, 
		cache: false,
		async: false,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {
			$("#mb_db").val(data);
		}
	});
	return false;

}
function fnFindID(){
	var mb_id = $("#mb_id").val().replace(/ /gi,'');
	$("#mb_id").val(mb_id);
	if(mb_id == ""){alert("아이디를 입력해주세요.");return false;}
	
	$.ajax({
		type: "POST",
		url: "<?=G5_URL?>/ajax/ajax.find.mb_id.php",
		data: {mb_id : mb_id}, 
		cache: false,
		async: false,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {
			if(data*1 > 0){
				alert("현재 사용중인 아이디입니다.");
				$("#mb_id").val('');
				return false;
			}else{
				alert("사용이 가능한 아이디입니다.");
				$("#mb_id").attr('readonly', true);
				$("#mb_id_chk").val("1");
				return false;
			}
		}
	});
	return false;
}

function fnSubmit(){
	if($("#mb_hp_chk").val() == "0"){
		alert("휴대폰 번호 중복검사를 진행해주세요.");
		return false;
	}
	$("#mb_name").val($("#mb_name").val().replace(/ /gi,''));
	if($("#mb_name").val() == ""){
		alert("이름을 입력해주세요");
		return false;
	}
	if($("#mb_id_chk").val() == "0"){
		alert("아이디 중복검사를 진행해주세요.");
		return false;
	}
	/*$("#mb_password").val($("#mb_password").val().replace(/ /gi,''));
	if($("#mb_password").val() == ""){
		alert("패스워드를 입력해주세요");
		return false;
	}*/
	return true;
}
</script>