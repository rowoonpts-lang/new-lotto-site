<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.sub.php");

	$mb_id = base64_decode($mb_id);
	$sql = "select * from g5_member where 1=1 and mb_id = '{$mb_id}'";
	$row = sql_fetch($sql);
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
				<form name="frm" id="frm" role="form" method="post" autocomplete="off" action="emp.save.php" onSubmit="return fnSubmit();">
				<input type="hidden" id="mb_no" name="mb_no" value="<?=$row[mb_no]?>">
				<input type="hidden" id="mb_hp_chk" value="<?if($mb_id){echo "1";}else{echo "0";}?>">
				<input type="hidden" id="mb_id_chk" value="<?if($mb_id){echo "1";}else{echo "0";}?>">
					<div class="card-body">
						<div class="form-group">
							<label for="mb_hp">휴대폰번호</label>
							<div class="row">
								<div class="col-8">
									<input type="text" class="form-control" id="mb_hp" name="mb_hp" placeholder="" value=<?=$row[mb_hp]?> <?if($mb_id){?>readonly<?}?>>
								</div>
								<?if(!$mb_id){?>
								<div class="col-4">
									<button type="button" class="btn btn-block btn-primary" onClick="fnFindHP();">중복검사</button>
								</div>
								<?}?>
							</div>
						</div>
						<div class="form-group">
							<label for="mb_name">이름</label>
							<input type="text" class="form-control" id="mb_name" name="mb_name" placeholder="" required value=<?=$row[mb_name]?>>
						</div>
						<div class="form-group">
							<label for="mb_id">아이디</label>
							<div class="row">
								<div class="col-8">
									<input type="text" class="form-control" id="mb_id" name="mb_id" placeholder="" value="<?=$row[mb_id]?>" <?if($mb_id){?>readonly<?}?>>
								</div>
								<div class="col-4">
									<button type="button" class="btn btn-block btn-primary" onClick="fnFindID()">중복검사</button>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="mb_id">패스워드</label>
							<div class="row">
								<div class="col-12">
									<input type="text" class="form-control" id="mb_password" name="mb_password" placeholder="" value="<?=$row[emp_pw]?>" required>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="mb_id">팀선택</label>
							<div class="row">
								<div class="col-12">
									<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="mb_team" aria-hidden="true" autocomplete="off">
										<?
											$list = getTeamList();	
											for($i=0; $i < count($list); $i++){
										?>
										<option value="<?=$list[$i]?>" <?if($row[mb_team] ==$list[$i]){echo "selected";}?>><?=$list[$i]?></option>
										<?}?>
									</select>
								</div>
							</div>
						</div>
						<!--div class="form-group">
							<label for="mb_password">패스워드</label>
							<input type="text" class="form-control" id="mb_password" name="mb_password" placeholder="" required>
						</div-->
						<div class="form-group">
							<label for="mb_id">권한선택</label>
							<div class="row">
								<div class="col-12">
									<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="mb_level" aria-hidden="true" autocomplete="off">
										<?
											$list = getLevelList();	
											for($i=0; $i < count($list); $i++){
										?>
										<option value="<?=$i+5?>" <?if($row[mb_level] == ($i+5)){echo "selected";}?>><?=$list[$i]?></option>
										<?}?>
									</select>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- /.card-body -->

				<div class="card-footer">
					<button type="submit" class="btn btn-primary">저장</button>
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
				return false;
			}
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