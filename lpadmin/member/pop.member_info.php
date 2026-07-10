<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.sub.php");

	$mb_id2 = $mb_id;
	$mb_id = base64_decode($mb_id);

	$sql = "select * 
			from g5_member a left join g5_member_etc b on (a.mb_id = b.mb_id)
			where 1=1 
				and a.mb_id = '{$mb_id}'
			";
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
				<?include_once("./member.head.php");?>
				<!-- /.card-header -->
				<!-- form start -->
				<form name="frm" id="frm" role="form" autocomplete="off" action="pop.member_info.update.php" onSubmit="return fnSubmit();">
				<input type="hidden" id="mb_hp_chk" value="1">
				<input type="hidden" id="mb_id_chk" value="1">
				<input type="hidden" id="mb_id_temp" name="mb_id_temp" value="<?=$mb_id?>">
				<input type="hidden" id="mb_hp_temp" name="mb_hp_temp" value="<?=$row[mb_hp]?>">
					<div class="row">
						<div class="col-md-6 col-6">
							<div class="card-body">
								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">휴대폰번호</label>
										</div>
										<div class="col-5">
											<input type="text" class="form-control" id="mb_hp" name="mb_hp" placeholder="" value="<?=$row[mb_hp]?>" onChange="fnCnValue('mb_hp')" required>
										</div>
										<div class="col-4" id="chk_hp_btn" style="display:none">
											<button type="button" class="btn btn-block btn-primary" onClick="fnFindHP();">중복검사</button>
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">아이디</label>
										</div>
										<div class="col-5">
											<input type="text" class="form-control" id="mb_id" name="mb_id" placeholder="" value="<?=$row[mb_id]?>" onChange="fnCnValue('mb_id')" required readonly>
										</div>
										<div class="col-4" id="chk_id_btn" style="display:none">
											<button type="button" class="btn btn-block btn-primary" onClick="fnFindID()">중복검사</button>
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">이름</label>
										</div>
										<div class="col-9">
											<input type="text" class="form-control" id="mb_name" name="mb_name" value="<?=$row[mb_name]?>" placeholder="">
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">패스워드</label>
										</div>
										<div class="col-9">
											<input type="text" class="form-control" id="mb_password" name="mb_password" placeholder="">
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">등급</label>
										</div>
										<div class="col-9">
											<div class="row">
											<?
												$mb_type_ary = fnGetType();
												for($i=0; $i < count($mb_type_ary); $i++){
											?>
											
												<div class="icheck-primary d-inline col-6">
													<input type="radio" id="radioPrimary<?=$i?>" name="mb_type" <?if($row[mb_type] == $mb_type_ary[$i] || (!$row[mb_type] && $i == 0)){echo "checked";}?> value="<?=$mb_type_ary[$i]?>">
													<label for="radioPrimary<?=$i?>">
														<?=$mb_type_ary[$i]?>
													</label>
												</div>
											
											<?	}?>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">일시정지</label>
										</div>
										<div class="col-9">
											<?if($row['left_day'] < 1){?>
											<button type="button" class="btn btn-block btn-danger" onClick="fnStop('<?=base64_encode($mb_id)?>')">일시정지</button>
											<?}else{?>
											<button type="button" class="btn btn-block btn-primary" onClick="fnStart('<?=base64_encode($mb_id)?>')">일시정지 해제</button>
											<?}?>
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">무료문자 종료일</label>
										</div>
										<div class="col-9">
											<input type="text" class="form-control dateinput" id="free_num_date" name="free_num_date" placeholder="" value="<?=$row[free_num_date]?>" onChange="setDateSE()">
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">무료문자 갯수</label>
										</div>
										<div class="col-9">
											<div class="row">
												<div class="col-3">
													<input type="text" class="form-control" id="free_num_qty" name="free_num_qty" placeholder="" value="<?=$row[free_num_qty]?>">
												</div>
												<button type="button" class="btn btn-success text-sm" onClick="setFreeNum(0)">0</button>&nbsp;
												<button type="button" class="btn btn-success text-sm" onClick="setFreeNum(5)">5</button>&nbsp;
												<button type="button" class="btn btn-success text-sm" onClick="setFreeNum(10)">10</button>&nbsp;
												<button type="button" class="btn btn-success text-sm" onClick="setFreeNum(20)">20</button>
												<script>
												function setFreeNum(num){
													$("#free_num_qty").val(num);
												}
												</script>
											</div>
										</div>

									</div>
								</div>
							</div>
						</div>
						<div class="col-md-6 col-6">
							<div class="card-body">
								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">이용기간시작</label>
										</div>
										<div class="col-3">
											<input type="text" class="form-control dateinput" id="start_date" name="start_date" placeholder="" value="<?if($row[start_date] != "0000-00-00"){echo $row[start_date];}?>" onChange="setDateSE()">
										</div>
										<div class="col-3">
											<label for="mb_hp">이용기간종료</label>
										</div>
										<div class="col-3">
											<input type="text" class="form-control dateinput" id="end_date" name="end_date" placeholder="" value="<?if($row[end_date] != "0000-00-00"){echo $row[end_date];}?>" onChange="setDateSE()">
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">남은일수</label>
										</div>
										<div class="col-9">
											<?
												$left_date_tmp = 0;
												if(intval((strtotime($row[end_date]) - strtotime(date("Y-m-d"))) / 86400) > 0){
													$left_date_tmp = intval((strtotime($row[end_date]) - strtotime(date("Y-m-d"))) / 86400);
												}
											?>
											<input type="text" class="form-control" id="left_day" name="left_day" placeholder="" value="<?=$left_date_tmp;?>" onChange="setDate('','','chg', this.value)">
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-12">
											<div class="row">
												<button type="button" class="btn btn-success text-sm" onClick="setDate('<?=date("Y-m-d")?>','<?=date("Y-m-d",strtotime("+14 day"))?>','set')">2주</button>&nbsp
												<button type="button" class="btn btn-success text-sm" onClick="setDate('<?=date("Y-m-d")?>','<?=date("Y-m-d",strtotime("+1 month"))?>','set')">1개월</button>&nbsp
												<button type="button" class="btn btn-success text-sm" onClick="setDate('<?=date("Y-m-d")?>','<?=date("Y-m-d",strtotime("+2 month"))?>','set')">2개월</button>&nbsp
												<button type="button" class="btn btn-success text-sm" onClick="setDate('<?=date("Y-m-d")?>','<?=date("Y-m-d",strtotime("+6 month"))?>','set')">6개월</button>&nbsp
												<button type="button" class="btn btn-success text-sm" onClick="setDate('<?=date("Y-m-d")?>','<?=date("Y-m-d",strtotime("+12 month"))?>','set')">1년</button>&nbsp
												<button type="button" class="btn btn-success text-sm" onClick="setDate('<?=date("Y-m-d")?>','<?=date("Y-m-d",strtotime("+15 month"))?>','set')">15개월</button>&nbsp
												<button type="button" class="btn btn-success text-sm" onClick="setDate('<?=date("Y-m-d")?>','<?=date("Y-m-d",strtotime("+24 month"))?>','set')">2년</button>&nbsp
												<button type="button" class="btn btn-success text-sm" onClick="setDate('<?=date("Y-m-d")?>','<?=date("Y-m-d",strtotime("+36 month"))?>','set')">3년</button>&nbsp
												<button type="button" class="btn btn-success text-sm" onClick="setDate('<?=date("Y-m-d")?>','<?=date("Y-m-d",strtotime("+48 month"))?>','set')">4년</button>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">요일선택</label>
										</div>
										<div class="col-9">
											<table class="table table-hover text-nowrap text-sm">
											<thead>
											<tr>
												<th>월</th>
												<th>화</th>
												<th>수</th>
												<th>목</th>
												<th>금</th>
												<th>토</th>
											</tr>
											</thead>
											<tbody>
											<tr>
												<td><input type="text" class="form-control" id="num_mon" name="num_mon" placeholder="" maxlength="2" value="<?=$row[num_mon]?>" onChange="fnCalcDay()"></td>
												<td><input type="text" class="form-control" id="num_tue" name="num_tue" placeholder="" maxlength="2" value="<?=$row[num_tue]?>" onChange="fnCalcDay()"></td>
												<td><input type="text" class="form-control" id="num_wed" name="num_wed" placeholder="" maxlength="2" value="<?=$row[num_wed]?>" onChange="fnCalcDay()"></td>
												<td><input type="text" class="form-control" id="num_thur" name="num_thur" placeholder="" maxlength="2" value="<?=$row[num_thur]?>" onChange="fnCalcDay()"></td>
												<td><input type="text" class="form-control" id="num_fri" name="num_fri" placeholder="" maxlength="2" value="<?=$row[num_fri]?>" onChange="fnCalcDay()"></td>
												<td><input type="text" class="form-control" id="num_sat" name="num_sat" placeholder="" maxlength="2" value="<?=$row[num_sat]?>" onChange="fnCalcDay()"></td>
											</tr>
											</tbody>
											</table>
										</div>
									</div>
								</div>
								<script>
								function fnCalcDay(){
									var totDay = 0;
									totDay = parseInt($("#num_mon").val()*1)+parseInt($("#num_tue").val()*1)+parseInt($("#num_wed").val()*1)+parseInt($("#num_thur").val()*1)+parseInt($("#num_fri").val()*1)+parseInt($("#num_sat").val()*1);
									$("#left_num").val(totDay);
								}
								</script>
								<?
									// 남은 조합수
									$sql2 = "select * from g5_member_etc where 1=1 and mb_id = '{$mb_id}'";
									$row2 = sql_fetch($sql2);
									$weekTotal = $row2[num_mon]+$row2[num_tue]+$row2[num_wed]+$row2[num_thur]+$row2[num_fri]+$row2[num_sat];
									$leftNum = $weekTotal - $row2[use_num];
								?>
								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">남은조합수</label>
										</div>
										<div class="col-9">
											<input type="text" class="form-control" id="left_num" name="left_num" value="<?=$leftNum?>">
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">이번주조합</label>
										</div>
										<div class="col-9">
											
											총 조합 <?=$weekTotal?>개 중 <?=$row2[use_num]?>개를 사용하였습니다.
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">수동보내기</label>
										</div>
										<div class="col-6">
											<input type="number" class="form-control" id="dr_num" name="dr_num" placeholder="">
										</div>
										<div class="col-3">
											<button class="btn btn-block btn-primary">즉시전송</button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<!-- row 끝-->
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
function fnStop(mb_id){
	if(confirm("회원을 일시정지 시키겠습니까?") == true){
		location.href="./pop.member_info.stop.php?mb_id="+mb_id+"&type=hold";
	}
}
function fnStart(mb_id){
	if(confirm("회원을 일시정지를 해제시키겠습니까?") == true){
		location.href="./pop.member_info.stop.php?mb_id="+mb_id+"&type=start";
	}
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
var isAjax = true;
function fnCreateNumber(mb_id, mb_type){
	if(isAjax == false){
		alert("처리중입니다. 잡시만 기다려주세요.");
		return false;
	}

	

	if($("#dr_num").val() < 1){
		alert("수동보내기 갯수를 입력하세요.");
		$("#dr_num").focus();
		return false;
	}
	if(confirm("조합을 전송하시겠습니까?") == true){
		isAjax = false;
		$.ajax({
			type: "POST",
			url: "ajax.create.number.php",
			data: {mb_id : mb_id, cnt : $("#dr_num").val(), mb_type: mb_type}, 
			cache: false,
			async: false,
			contentType : "application/x-www-form-urlencoded; charset=UTF-8",
			success: function(data) {
				alert("정상적으로 처리되었습니다.");
				isAjax = true;
				location.reload();
			}
		});
	}
}

function fnSubmit(){
	if($("#mb_hp_chk").val() == "0"){
		alert("휴대폰 번호 중복검사를 진행해주세요.");
		return false;
	}
	if($("#mb_id_chk").val() == "0"){
		alert("아이디 중복검사를 진행해주세요.");
		return false;
	}
	if($("#mb_name").val() == ""){
		alert("이름을 입력하세요.");
		return false;
	}
	if($("#dr_num").val()*1 > 0){
		if(confirm("조합을 전송하시겠습니까?") == true){
			return true;
		}else{
			return false;
		}
	}
	/*$("#mb_password").val($("#mb_password").val().replace(/ /gi,''));
	if($("#mb_password").val() == ""){
		alert("패스워드를 입력해주세요");
		return false;
	}*/
	return true;
}

function setDate(s, e, t, l){
	if(t == "set"){
		if($("#start_date").val() == ""){
			$("#start_date").val(s);
		}
		$("#end_date").val(e);
		/*if($("#start_date").val()){
			s = $("#start_date").val();
		}else{
			$("#start_date").val(s);
		}

		if($("#end_date").val()){
			e = $("#end_date").val();
		}else{
			$("#end_date").val(e);
		}*/


		$.ajax({
			type: "POST",
			url: "ajax.calc.leftday.php",
			data: {s:s, e:e, t:t},
			cache: false,
			async: false,
			contentType : "application/x-www-form-urlencoded; charset=UTF-8",
			success: function(data) {
				$("#left_day").val(data);
			}
		});
		return false;
	}
	if(t == "chg"){
		s = $("#start_date").val();
		$.ajax({
			type: "POST",
			url: "ajax.calc.leftday.php",
			data: {s:s, l:l, t:t},
			cache: false,
			async: false,
			dataType : "json",
			//contentType : "application/x-www-form-urlencoded; charset=UTF-8",
			success: function(data) {
				//$("#start_date").val(data['s']);
				$("#end_date").val(data['e']);
			}
		});
		return false;
	}
	/*if(t == "set"){
		$("#start_date").val(s);
		$("#end_date").val(e);



		$.ajax({
			type: "POST",
			url: "ajax.calc.leftday.php",
			data: {s:s, e:e, t:t},
			cache: false,
			async: false,
			contentType : "application/x-www-form-urlencoded; charset=UTF-8",
			success: function(data) {
				$("#left_day").val(data);
			}
		});
		return false;
	}
	if(t == "chg"){
		s = $("#start_date").val();
		$.ajax({
			type: "POST",
			url: "ajax.calc.leftday.php",
			data: {s:s, l:l, t:t},
			cache: false,
			async: false,
			dataType : "json",
			//contentType : "application/x-www-form-urlencoded; charset=UTF-8",
			success: function(data) {
				$("#start_date").val(data['s']);
				$("#end_date").val(data['e']);
			}
		});
		return false;
	}*/
}

function setDateSE(){
	if(!$("#start_date").val() || !$("#end_date").val()){
		$("left_day").val("");
	}else{
		//setDate($("#start_date").val(), $("#end_date").val(), 'set');
		setDate("<?=date('Y-m-d')?>", $("#end_date").val(), 'set', '');
		/*if("$row[start_date]" != "0000-00-00"){
			setDate($("#start_date").val(), $("#end_date").val(), 'set');
		}else{
			setDate("<?=date('Y-m-d')?>", $("#end_date").val(), 'set', '','chg');
		}*/
	}
	/*if(!$("#start_date").val() || !$("#end_date").val()){
		$("left_day").val("");
	}else{
		setDate($("#start_date").val(), $("#end_date").val(), 'set');
	}*/
}

function fnFindHP(){
	var mb_hp = $("#mb_hp").val().replace(/ /gi,'');
	mb_hp = mb_hp.replace(/-/gi,'');
	$("#mb_hp").val(mb_hp);
	if(mb_hp == ""){alert("휴대폰 번호를 입력해주세요.");return false;}
	
	$.ajax({
		type: "POST",
		url: "<?=G5_URL?>/ajax/ajax.find.mb_hp.php",
		data: {mb_hp : mb_hp, nochk:"y"}, 
		cache: false,
		async: false,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {
			if(data*1 > 0){
				alert("현재 사용중인 휴대폰 번호입니다.");
				$("#mb_hp").val('');
				return false;
			}else{
				alert("사용이 가능한 휴대폰 번호입니다.");
				$("#mb_hp_chk").val("1");
				$("#chk_hp_btn").hide();
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
				$("#mb_id_chk").val("1");
				$("#chk_id_btn").hide();
				return false;
			}
		}
	});
	return false;
}

function fnCnValue(t){
	if(t == "mb_hp"){
		$("#mb_hp_chk").val("0");
		$("#chk_hp_btn").show();
	}
	if(t == "mb_id"){
		$("#mb_id_chk").val("0");
		$("#chk_id_btn").show();
	}
}

</script>