<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.sub.php");

	$sql = "select * from l_ad_user where idx = '{$idx}'";
	$row = sql_fetch($sql);
	$w = "";
	if($idx){
		$w = "u";
	}
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
					<h3 class="card-title">매체사 추가</h3>
				</div>
				<!-- /.card-header -->
				<!-- form start -->
				<form name="frm" id="frm" role="form" autocomplete="off" action="pop.new_ad.update.php" onSubmit="return fnSubmit();">
				<input type="hidden" id="lu_code_chk" value="<?if($w == ""){?>0<?}else{?>1<?}?>">
				<input type="hidden" id="lu_id_chk" value="<?if($w == ""){?>0<?}else{?>1<?}?>">
				<input type="hidden" name="idx" value="<?=$idx?>">
					<div class="card-body">
						<div class="form-group">
							<label for="lu_type">매체사 코드</label>
							<div class="row">
								<div class="col-6">
									<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="lu_type_select" id="lu_type_select" onChange="fnCngLu(this.value)" aria-hidden="true">
									<?if($w == ""){?>
										<option value="">저장된 매체사 선택</option>
										<?
											$sql2 = "select * from (select lu_type, lu_name from l_ad_user where 1=1 and del_yn = '0' group by lu_type) a order by lu_type asc";
											$result2 = sql_query($sql2);
											for($j=0; $row2 = sql_fetch_array($result2); $j++){
										?>
										<option value="<?=$row2[lu_type]?>|<?=$row2[lu_name]?>"><?=$row2[lu_name]?></option>
										<?
											}
										?>
										<?}else{?>
										<option><?=$row['lu_name']?></option>
										<?}?>
									</select>
								</div>
								<div class="col-6">
									<input type="text" class="form-control" id="lu_type" name="lu_type" placeholder="매체사 코드" value="<?=$row['lu_type']?>" <?if($w == "u"){echo "readonly";}?>>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="mb_name">매체사 이름</label>
							<input type="text" class="form-control" id="lu_name" name="lu_name" placeholder="" required value="<?=$row['lu_name']?>" <?if($w == "u"){echo "readonly";}?>>
						</div>
						<div class="form-group">
							<label for="mb_name">광고 코드</label>
							<div class="row">
								<div class="col-8">
									<input type="text" class="form-control" id="lu_code" name="lu_code" placeholder="" required value="<?=$row['lu_code']?>" <?if($w == "u"){echo "readonly";}?>>
								</div>
								<?if($w == ""){?>
								<div class="col-4">
									<button type="button" class="btn btn-block btn-primary" onClick="fnFindCode()">중복검사</button>
								</div>
								<?}?>
						</div>
						<div class="form-group">
							<label for="mb_id">아이디</label>
							<div class="row">
								<div class="col-8">
									<input type="text" class="form-control" id="lu_id" name="lu_id" placeholder="" value="<?=$row['lu_id']?>" <?if($w == "u"){echo "readonly";}?>>
								</div>
								<?if($w == ""){?>
								<div class="col-4">
									<button type="button" class="btn btn-block btn-primary" onClick="fnFindID()">중복검사</button>
								</div>
								<?}?>
							</div>
						</div>
						<div class="form-group">
							<label for="mb_name">패스워드</label>
							<input type="text" class="form-control" id="lu_pw" name="lu_pw" placeholder="" value="<?=$row['lu_pw']?>" required>
						</div>
						<!--div class="form-group">
							<label for="mb_password">패스워드</label>
							<input type="text" class="form-control" id="mb_password" name="mb_password" placeholder="" required>
						</div-->
						<div class="form-group">
							<label for="exampleInputPassword1">관리자 페이지 권한</label>
							<div class="form-group clearfix">
								<div class="icheck-primary d-inline">
									<input type="radio" id="radioPrimary1" name="st_tp" <?if($w == ""){?>checked<?}else{if($row[st_tp] == "1"){echo "checked";}}?> value="1">
									<label for="radioPrimary1">
										접속가능
									</label>
								</div>
								<div class="icheck-primary d-inline">
									<input type="radio" id="radioPrimary2" name="st_tp" <?if($row[st_tp] == "0"){echo "checked";}?> value="0">
									<label for="radioPrimary2">
										접속금지
									</label>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- /.card-body -->

				<div class="card-footer">
					<?if($w == ""){?>
					<button type="submit" class="btn btn-primary">생성</button>
					<?}else{?>
					<button type="submit" class="btn btn-primary">저장</button>
					<?}?>
				</div>
			</form>
		</div>
	</div>
</section>
<!-- /.card -->
<script>
function fnCngLu(v){
	if(v){
		var spli = v.split('|');
		$("#lu_type").val(spli[0]);
		$("#lu_name").val(spli[1]);
	}else{

	}
}

function fnFindCode(){
	var lu_type = $("#lu_type").val().replace(/ /gi,'');
	$("#lu_type").val(lu_type);
	var lu_code = $("#lu_code").val().replace(/ /gi,'');
	$("#lu_code").val(lu_code);
	
	if(lu_type == ""){alert("매체사 코드를 입력해주세요.");return false;}
	if(lu_code == ""){alert("광고 코드를 입력해주세요.");return false;}
	
	$.ajax({
		type: "POST",
		url: "<?=G5_LADMIN_URL?>/ad/ajax.find.code.php",
		data: {lu_type : lu_type, lu_code : lu_code}, 
		cache: false,
		async: false,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {
			if(data*1 > 0){
				alert("현재 사용중인 광고 코드 입니다.");
				$("#lu_code").val('');
				return false;
			}else{
				alert("사용이 가능한 광고 코드 입니다.");
				$("#lu_type").attr('readonly', true);
				$("#lu_code").attr('readonly', true);
				$("#lu_name").attr('readonly', true);
				$("#lu_code_chk").val("1");
				$("#lu_type_select").empty();
				$("#lu_type_select").append("<option>"+$("#lu_name").val()+"</option>");

				return false;
			}
		}
	});
	return false;
}
function fnFindID(){
	var lu_id = $("#lu_id").val().replace(/ /gi,'');
	$("#lu_id").val(lu_id);
	if(lu_id == ""){alert("아이디를 입력해주세요.");return false;}
	
	$.ajax({
		type: "POST",
		url: "<?=G5_LADMIN_URL?>/ad/ajax.find.id.php",
		data: {lu_id : lu_id}, 
		cache: false,
		async: false,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {
			if(data*1 > 0){
				alert("현재 사용중인 아이디 입니다.");
				$("#lu_id").val('');
				$("#lu_id").val('');
				return false;
			}else{
				alert("사용이 가능한 아이디 입니다.");
				$("#lu_id").val(lu_id);
				$("#lu_id").attr('readonly', true);
				$("#lu_id_chk").val("1");
				return false;
			}
		}
	});
	return false;
}


function fnSubmit(){
	$("#lu_type").val($("#lu_type").val().replace(/ /gi,''));
	if($("#lu_type").val() == ""){
		alert("매체사 코드를 입력하세요.");
		return false;
	}

	$("#lu_name").val($("#lu_name").val().replace(/ /gi,''));
	if($("#lu_name").val() == ""){
		alert("매체사 이름을 입력하세요.");
		return false;
	}

	$("#lu_code").val($("#lu_code").val().replace(/ /gi,''));
	if($("#lu_code").val() == ""){
		alert("광고 코드를 입력하세요.");
		return false;
	}

	if($("#lu_code_chk").val() == "0"){
		alert("광고코드 중복검사를 진행해주세요.");
		return false;
	}
	
	if($("#lu_id_chk").val() == "0"){
		alert("아이디 중복검사를 진행해주세요.");
		return false;
	}

	$("#lu_pw").val($("#lu_pw").val().replace(/ /gi,''));
	if($("#lu_pw").val() == ""){
		alert("패스워드를 입력하세요.");
		return false;
	}

	return true;
}
</script>