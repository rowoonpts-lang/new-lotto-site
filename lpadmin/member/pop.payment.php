<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.sub.php");

	$mb_id2 = $mb_id;
	$mb_id = base64_decode($mb_id);

	$sql= "select * from g5_member a, g5_member_etc b where 1=1 and a.mb_id = b.mb_id and a.mb_id = '{$mb_id}'";
	$row = sql_fetch($sql);
?>
<link rel="stylesheet" href="//mugifly.github.io/jquery-simple-datetimepicker/jquery.simple-dtpicker.css">
<script src="//mugifly.github.io/jquery-simple-datetimepicker/jquery.simple-dtpicker.js"></script>
<script>
$(function(){
    $('.datetimepicker').appendDtpicker({
		'locale':'ko',
		'minuteInterval':10,
		'autodateOnStart':false,
		'minTime' : '09:00'
	});
});
</script>

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
				
				<input type="hidden" id="mb_hp_chk" value="0">
				<input type="hidden" id="mb_id_chk" value="0">
					<div class="row">
						<div class="col-md-12 col-12">
							<div class="card-body">
								<div class="form-group">
									<table class="table table-hover text-nowrap text-sm">
									<thead>
									<tr>
										<th>회원명</th>
										<th>코드</th>
										<th>연락처</th>
										<th>등급</th>
										<th>금액</th>
										<th>결제수단</th>
										<th>승인번호</th>
										<th>결제일</th>
										<th>승인자</th>
										<th>승인</th>
									</tr>
									</thead>
									<tbody>
									</tbody>
									<?
										$cnt = 0;
										$sql2 = "select * 
												from l_pay a, g5_member b
												where 1=1 
													and a.mb_id = b.mb_id
													and a.mb_id = '{$row[mb_id]}'
													and a.lp_status = '입금'
												order by lp_datetime desc
												";
										$result2 = sql_query($sql2);
										for($i=0; $row2 = sql_fetch_array($result2); $i++){
											$cnt++;
									?>
									<tr>
										<td>
											<div class="confirmpop1" id="confirmpop1_<?=$row2[lp_id]?>">
												<form id="frm1_<?=$row2['lp_id']?>" onSubmit="return fnConfirm1Save('<?=$row2['lp_id']?>')" action="pop.payment.update.php">
												<input type="hidden" name="lp_id" value="<?=$row2['lp_id']?>">
												<input type="hidden" name="mb_id" value="<?=$row2['mb_id']?>">
												<input type="hidden" name="lp_price" value="<?=($row2[lp_price]/11*10)?>">
												<p class="confirm_tit">일반승인</p>
												<table class="confrimpop_tbl">
												<tr>
													<th>회원명/코드</th>
													<th>승인금액</th>
													<th>등급선택</th>
													<th>승인메모선택</th>
												</tr>
												<tr>
													<td><?=$row2[mb_name]?> / <?=$row2[mb_code]?></td>
													<td><?=number_format($row2[lp_price]/11*10)?></td>
													<td>
														<select id="" name="confirm_mb_type" class="form-control select2 select2-hidden-accessible">
														<?
															$preUser = fnGetTypePre();
															for($j=0; $j < count($preUser); $j++){
														?>
															<option value="<?=$preUser[$j]?>" <?if($row2[mb_type] == $preUser[$j]){echo "selected";}?>><?=$preUser[$j]?></option>
														<?	}?>
														</select>
													</td>
													<td>
														<select id="" name="confirm_mb_type_memo" class="form-control select2 select2-hidden-accessible">
															<option value="">선택안함</option>
														<?
															$preUser = fnGetTypePre();
															for($j=0; $j < count($preUser); $j++){
														?>
															<option value="<?=$preUser[$j]?>"><?=$preUser[$j]?></option>
														<?	}?>
														</select>
													</td>
												</tr>
												</table>
												<table class="confrimpop_tbl">
												<tr>
													<th>미수금</th>
													<td><input type="text" class="form-control" name="confirm_misu" id="confirm_misu_<?=$row2[lp_id]?>" placeholder="0" onkeyup="inputNumberFormat(this)"></td>
													<th>미수알림</td>
													<td><input type="text" name="confirm_misu_alarm_date" id="" class="form-control datetimepicker" placeholder=""></td>
												</tr>
												<tr>
													<th>미수금 완납</th>
													<td colspan="3" style="text-align:left;">
														<div class="icheck-primary d-inline">
															<input type="checkbox" id="misu_<?=$row2['lp_id']?>" name="confirm_misu_chk" value="1" onChange="fnChangeCheckBox('<?=$row2['lp_id']?>')">
															<label for="misu_<?=$row2['lp_id']?>">
																<?=$resultAry[$i]?>
															</label>
														</div>
													</td>
												</tr>
												</table>
												<p style="padding:10px;text-align:Center;">
													<button class="btn btn-primary">확인</button>
													<button type="button" class="btn btn-secondary" onClick="fnConfirm1Close()">취소</button>
												</p>
												</form>
											</div>
											<!-- 인계승인 팝업 -->
											<div class="confirmpopIn" id="confirmpopIn_<?=$row2[lp_id]?>">
												<form id="frmIn_<?=$row2['lp_id']?>" onSubmit="return fnConfirmInSave('<?=$row2['lp_id']?>')" action="pop.payment.in.update.php">
												<input type="hidden" name="lp_id" value="<?=$row2['lp_id']?>">
												<input type="hidden" name="mb_id" value="<?=$row2['mb_id']?>">
												<input type="hidden" name="lp_price" value="<?=($row2[lp_price]/11*10)?>">
												<p class="confirm_tit">인계승인</p>
												<table class="confrimpop_tbl">
												<tr>
													<th>회원명/코드</th>
													<th>승인금액</th>
													<th>등급선택</th>
													<th>승인메모선택</th>
												</tr>
												<tr>
													<td><?=$row2[mb_name]?> / <?=$row2[mb_code]?></td>
													<td><?=number_format($row2[lp_price]/11*10)?></td>
													<td>
														<select id="" name="confirm_mb_type" class="form-control select2 select2-hidden-accessible">
														<?
															$preUser = fnGetTypePre();
															for($j=0; $j < count($preUser); $j++){
														?>
															<option value="<?=$preUser[$j]?>" <?if($row2[mb_type] == $preUser[$j]){echo "selected";}?>><?=$preUser[$j]?></option>
														<?	}?>
														</select>
													</td>
													<td>
														<select id="" name="confirm_mb_type_memo" class="form-control select2 select2-hidden-accessible">
															<option value="">선택안함</option>
														<?
															$preUser = fnGetTypePre();
															for($j=0; $j < count($preUser); $j++){
														?>
															<option value="<?=$preUser[$j]?>"><?=$preUser[$j]?></option>
														<?	}?>
														</select>
													</td>
												</tr>
												</table>
												<?
													// 1차 인계회원 찾기
													$sql_bf = "select * from l_pay where mb_id = '{$mb_id}' order by lp_datetime limit 1";
													$row_bf = sql_fetch($sql_bf);
												?>
												<table class="confrimpop_tbl">
												<tr>
													<th><input type="hidden" name="confirm_in1" value="<?=$row_bf[emp_id]?>"><?=$member_info[$row_bf[emp_id]]?></th>
													<td><input type="text" class="form-control" name="confirm_in1_price" id="confirm_in1_price_<?=$row2[lp_id]?>" placeholder="0" onkeyup="inputNumberFormat(this)" onChange="fnCgIn('<?=number_format($row2[lp_price]/11*10)?>',this.value,'<?=$row2[lp_id]?>','1','2')"></td>
													<th>
														<select class="form-control select2 select2-hidden-accessible" name="confirm_in2">
															<?
															$teamList = getTeamList2();
															for($j=0; $j < count($teamList); $j++){
															?>
															<option value="<?=$teamList[$j]?>"><?=$teamList[$j]?></option>
															<?}?>
														</select>
													</td>
													<td><input type="text" name="confirm_in2_price" id="confirm_in2_price_<?=$row2[lp_id]?>" class="form-control" placeholder="0" onkeyup="inputNumberFormat(this)" onChange="fnCgIn('<?=number_format($row2[lp_price]/11*10)?>',this.value,'<?=$row2[lp_id]?>','2','1')"></td>
												</tr>
												</table>
												<table class="confrimpop_tbl">
												<tr>
													<th>미수금</th>
													<td><input type="text" class="form-control" name="confirm_misu" id="confirm_in_misu_<?=$row2[lp_id]?>" placeholder="0" onkeyup="inputNumberFormat(this)"></td>
													<th>미수알림</td>
													<td><input type="text" name="confirm_misu_alarm_date" id="" class="form-control datetimepicker" placeholder=""></td>
												</tr>
												<tr>
													<th>미수금 완납</th>
													<td colspan="3" style="text-align:left;">
														<div class="icheck-primary d-inline">
															<input type="checkbox" id="misu_in_<?=$row2['lp_id']?>" name="confirm_misu_chk" value="1" onChange="fnChangeCheckBoxIn('<?=$row2['lp_id']?>')">
															<label for="misu_in_<?=$row2['lp_id']?>">
																<?=$resultAry[$i]?>
															</label>
														</div>
													</td>
												</tr>
												</table>
												<p style="padding:10px;text-align:Center;">
													<button class="btn btn-primary">확인</button>
													<button type="button" class="btn btn-secondary" onClick="fnConfirm1Close()">취소</button>
												</p>
												</form>
											</div>
											<!-- 인계승인 팝업 끝 -->
											<?=$row2[mb_name]?>
										</td>
										<td><?=$row2[mb_code]?></td>
										<td><?=$row2[mb_hp]?></td>
										<td><?=$row2[mb_type]?></td>
										<td><?=number_format($row2[lp_price]/11*10)?></td>
										<td>
											<?=$row2[pay_method]?>
											<?if($row2[pay_method]=="무통장"){?>
											<br>
											<?
												$tmp = explode(" ",$row2[mu_num]);
												echo "(".$tmp[0].")";
											?>
											<?}else{?>
											<br><?=$row['card_name']?> (<?=$row2[card_sell_mm]?>개월)
											<?}?>
										</td>
										<td><?=$row2[transaction_no]?></td>
										<td><?=$row2[lp_pay_datetime]?></td>
										<td><?=$member_info[$row2[confirm_user]]?></td>
										<td>
											<?
												if($row['mb_in'] == ""){
													if(!$row2[confirm_user]){
											?>
											<button type="button" class="btn btn-block btn-primary" onClick="fnConfirm1('<?=$row2[lp_id]?>')">일반승인</button>
											<?		}else{?>
											<button type="button" class="btn btn-block btn-danger" onClick="fnConfirm1Cancel('<?=$row2[lp_id]?>')">일반승인취소</button>
											<?	
													}
												}else{
													if(!$row2[confirm_in1] && !$row2[confirm_in2]){
											?>
											<button type="button" class="btn btn-block btn-primary" onClick="fnConfirmIn('<?=$row2[lp_id]?>')">인계승인</button>
											<?	
													}else{
											?>
											<button type="button" class="btn btn-block btn-danger" onClick="fnConfirmInCancel('<?=$row2[lp_id]?>')">인계승인취소</button>
											<?
													}
												}
											?>
										</td>
									</tr>
									<?	}?>
									<?if($cnt < 1){?>
									<tr>
										<td colspan="11" style="text-align:center;">결제된 결과가 없습니다.</td>
									</tr>
									<?}?>
									</table>
								</div>
							</div>
						</div>
					</div>
					<!-- row 끝-->
				</div>
				<!-- /.card-body -->
			
		</div>
	</div>
</section>
<div class="abs_div"></div>


<!-- /.card -->
<script>
var inSW = false;

function fnChangeCheckBox(lp_id){
	if($("input:checkbox[id='misu_"+lp_id+"']:checked").val() == true){
		$("#confirm_misu_"+lp_id).val("0");
	}
}
function fnChangeCheckBoxIn(lp_id){
	if($("input:checkbox[id='misu_in_"+lp_id+"']:checked").val() == true){
		$("#confirm_in_misu_"+lp_id).val("0");
	}
}
function fnConfirm1Save(lp_id){
	if(confirm("일반승인을 승인하시겠습니까?") == true){
		return true;
	}
	return false;
}
function fnConfirm1(lp_id){
	$(".abs_div").show();
	$("#confirmpop1_"+lp_id).show();
}
function fnConfirm1Cancel(lp_id){
	if(confirm("일반승인을 취소하시겠습니까?")==true){
		location.href="pop.payment.cancel.php?lp_id="+lp_id;
	}
}

function fnConfirmInCancel(lp_id){
	if(confirm("인계승인을 취소하시겠습니까?")==true){
		location.href="pop.payment.in.cancel.php?lp_id="+lp_id;
	}
}

function fnCgIn(price, v, lp_id , i, j){
	in2SW = false;
	leftPrice = parseInt(price.replace(/,/gi,'')) - parseInt(v.replace(/,/gi,''));
	if(parseInt(leftPrice) < 0){
		alert("승인금액보다 큽니다.");
		$("#confirm_in"+i+"_price_"+lp_id).val("");
		$("#confirm_in"+i+"_price_"+lp_id).focus();
		$("#confirm_in"+j+"_price_"+lp_id).val("");
		return false;
	}
	$("#confirm_in"+j+"_price_"+lp_id).val(comma(leftPrice));
	inSW = true
	//alert(leftPrice);
}
function fnConfirm1Close(){
	$(".abs_div").hide();
	$(".confirmpop1").hide();
	$(".confirmpopIn").hide();
}
var cancleSW = true;
var cancleMu = true;
function fnPayCancel(lp_id){
	if(cancleSW == false){
		alert("잠시만 기다려주세요.");
	}
	if(confirm("해당 결제건을 취소하시겠습니까?") == true){
		cancleSW = false;
		location.href="./payment.cancel.php?lp_id="+lp_id
		/*$.ajax({
			type: "POST",
			url: "./ajax.payment.cancel.php",
			data: string, 
			cache: false,
			async: false,
			contentType : "application/x-www-form-urlencoded; charset=UTF-8",
			success: function(data) {
				alert(data);
			}
		});
		return false;*/

	}
	
}
function fnConfirmIn(lp_id){
	$(".abs_div").show();
	$("#confirmpopIn_"+lp_id).show();
}

function fnConfirmInSave(lp_id){
	if(inSW == true){
		if(confirm("인계처리 승인하시겠습니까?") == true){
			return true;
		}
	}
	return false;
}

function fnPayMu(lp_id){
	if(cancleMu == false){
		alert("잠시만 기다려주세요.");
	}
	if(confirm("해당 결제건을 입급확인 하시겠습니까?") == true){
		cancleMu = false;
		location.href="./payment.mu.pay.php?lp_id="+lp_id
		/*$.ajax({
			type: "POST",
			url: "./ajax.payment.cancel.php",
			data: string, 
			cache: false,
			async: false,
			contentType : "application/x-www-form-urlencoded; charset=UTF-8",
			success: function(data) {
				alert(data);
			}
		});
		return false;*/

	}
}
</script>