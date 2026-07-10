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
				<form name="frm" id="frm" role="form" autocomplete="off" action="pop.memo.update.php" onSubmit="return fnSubmit();">
				<input type="hidden" id="mb_id" name="mb_id" value="<?=$mb_id?>">
					<div class="row">
						<div class="col-md-4 col-4">
							<div class="card-body">
								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">회원정보</label>
										</div>
										<div class="col-9">
											<div class="row">
												<span style="font-size:20px;font-weight:900;;line-height:23px"><?=$row[mb_name]?> / </span><span style="font-size:26px;font-weight:900;;line-height:23px"><?=$row[mb_hp]?></span>
											</div>
										</div>
									</div>
								</div>

								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">메모선택</label>
										</div>
										<div class="col-9">
											<select id="recent_select" name="recent_select" class="form-control select2 select2-hidden-accessible" style="width: 100%;" data-select2-id="9" tabindex="-1" aria-hidden="true">
												<option selected="selected" data-select2-id="11" value="">메모선택</option>
												<?
													$memoList = fnGetMemoStatus();
													for($k=0; $k < count($memoList); $k++){
												?>
												<option value="<?=$memoList[$k]?>"><?=$memoList[$k]?></option>
												<?	}?>
											</select>
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">결제방법</label>
										</div>
										<div class="col-9">
											<div class="row">
												<div class="col-6">
													<button type="button" class="btn btn-block btn-primary" onClick="openBank()">무통장</button>
												</div>
												<div class="col-6">
													<button type="button" class="btn btn-block btn-primary" onClick="openCredit()">카드결제</button>
												</div>
											</div>
										</div>
									</div>
								</div>
								<!--div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">인계처리</label>
										</div>
										<div class="col-9">
											<?if($row[mb_in] == ""){?>
											<button type="button" class="btn btn-block btn-primary" onClick="fnInProc('처리', '<?=$row[mb_id]?>')">인계처리</button>
											<?}else{?>
											<button type="button" class="btn btn-block btn-danger" onClick="fnInProc('', '<?=$row[mb_id]?>')">인계취소</button>
											<?}?>
										</div>
									</div>
								</div-->

								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">카드승인</label>
										</div>
										<div class="col-9">
											<button type="button" class="btn btn-block btn-primary" onClick="openCredit2('처리', '<?=$row[mb_id]?>')">카드승인요청</button>
										</div>
									</div>
								</div>

								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">문자전송</label>
										</div>
										<div class="col-9">
											<div class="row">
												<div class="col-7">
													<select id="yak_select" name="yak_select" class="form-control select2 select2-hidden-accessible" style="width: 100%;" data-select2-id="9" tabindex="-1" aria-hidden="true">
														<?
															$mb_type_ary = fnGetTypeYak();
															for($i=0; $i < count($mb_type_ary); $i++){
														?>
														<option value="<?=$mb_type_ary[$i]?>"><?=$mb_type_ary[$i]?></option>
														<?	}?>
													</SELECT>
												</div>
												<div class="col-5">
													<button type="button" class="btn btn-block btn-primary" onClick="fnSendYak()">약관전송</button>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp"></label>
										</div>
										<div class="col-9">
											<div class="row">
												<div class="col-6">
													<button type="button" class="btn btn-block btn-primary" onClick="fnPwReset()">아이디전송<br>(무료)</button>
												</div>
												<div class="col-6">
													<button type="button" class="btn btn-block btn-success" onClick="fnPwReset2()">아이디전송<br>(유료)</button>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">상담내용</label>
										</div>
										<div class="col-9">
											<textarea class="form-control recent_memo" id="recent_memo" name="recent_memo" rows="3" placeholder=""></textarea>
										</div>
									</div>
								</div>
								<!--div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">미수금</label>
										</div>
										<div class="col-9">
											<input type="number" class="form-control" id="recent_misu" name="recent_misu" placeholder="">
										</div>
									</div>
								</div-->
								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">현금영수증</label>
										</div>
										<div class="col-9">
											<input type="text" class="form-control" id="mb_hyunkm" name="mb_hyunkm" placeholder="" value="<?=$row['mb_hyunkm']?>" onChange="fnChgHy(this.value)">
										</div>
									</div>
								</div>
								<script>
								function fnChgHy(v){
									$.ajax({
										type: "POST",
										url: "./ajax.pop.memo.hk.php",
										data: {mb_id : "<?=$mb_id?>", v : v}, 
										cache: false,
										async: false,
										contentType : "application/x-www-form-urlencoded; charset=UTF-8",
										success: function(data) {
											alert("현금영수증 저장됨");
										}
									});
									return false;
								}
								</script>
								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">알림</label>
										</div>
										<div class="col-9">
											<div class="row">
												<div class="col-5">
													<select id="alarm_select" name="alarm_select" class="form-control select2 select2-hidden-accessible" style="width: 100%;" data-select2-id="9" tabindex="-1" aria-hidden="true">
														<option selected="selected" value="">선택</option>
														<option value="유력">유력</option>
														<option value="단순">단순</option>
														<option value="미수">미수</option>
														<option value="부재">부재</option>
													</SELECT>
												</div>
												<div class="col-7">
													<input type="text" name="alarm_date" id="alarm_date" class="form-control datetimepicker" placeholder="">
												</div>
											</div>
										</div>
									</div>
								</div>
								<button type="submit" class="btn btn-primary">등록</button>
							</div>
						</div>
						<div class="col-md-8 col-8">
							<div class="card-body">
								<div class="form-group">
									<table class="table table-hover text-nowrap text-sm">
									<thead>
									<tr>
										<th>일자</th>
										<th>담당</th>
										<th>결제금</th>
										<th>미수금</th>
										<th>상태</th>
										<th style="width:30%">내용</th>
										<th>알람시간</th>
										<?if($member[mb_level] >= 10){?>
										<th>삭제</th>
										<?}?>
									</tr>
									</thead>
									<tbody>
									</tbody>
									<?
										
										$sql_common = " from l_memo a, g5_member b ";
										$sql_search = " where 1=1 and a.mb_id = b.mb_id and a.mb_id = '{$mb_id}' ";
										$sql_order = " order by lm_datetime desc ";

										$sql2 = " select count(distinct lm_id) as cnt {$sql_common} {$sql_search} {$sql_order} ";

										$row2 = sql_fetch($sql2);
										$total_count = $row2['cnt'];


										$rows = 10;
										$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
										if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
										$from_record = ($page - 1) * $rows; // 시작 열을 구함'


										$limit = " limit {$from_record}, {$rows} ";

										$sql2 = "select a.*, b.mb_type {$sql_common} {$sql_search} {$sql_order} {$limit}";

										$result2 = sql_query($sql2);
										for($i = 0; $row2 = sql_fetch_array($result2); $i++){
									?>
									<tr>
										<td><?=$row2[lm_datetime]?></td>
										<td><?=$member_info[$row2[from_mb_id]]?></td>
										<td><?if($row2[lm_price]){echo number_format($row2[lm_price]);}?></td>
										<td><?if($row2[lm_misu]){echo number_format($row2[lm_misu]);}?></td>
										<td><?=$row2[lm_memo_type]?></td>
										<td style="text-align:left"><?=$row2[lm_memo]?></td>
										<td>
											<?if($row2[lm_alarm_view] == "0"){?>
											<?=str_replace("0000-00-00 00:00:00","",$row2[lm_alarm_date])?>
											<?}?>
										</td>
										<?if($member[mb_level] >= 10){?>
										<td>
											<button type="button" class="btn btn-danger" onClick="fnProcDel('l_memo','lm_id','<?=$row2[lm_id]?>')">삭제</button>
										</td>
										<?}?>
									</tr>
									<?	}
										if($total_count < 1){
									?>
									<tr>
										<td colspan="10" style="text-align:center;">등록된 메모가 없습니다.</td>
									</tr>
									<?	}?>
									</table>
									<?php 
										$qstr .= "&mb_id=".base64_encode($mb_id);
										echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page=');
									?>
								</div>
							</div>
						</div>
					</div>
					<!-- row 끝-->
				</div>
				<!-- /.card-body -->

				
			</form>
		</div>
	</div>
</section>

<div class="abs_div"></div>

<div class="layer_pop01">
	<div class="col-md-12">
		<div class="card card-default">
			<div class="card-header">
				<h3 class="card-title">
					<i class="fas fa-money-check"></i>
					무통장
				</h3>
			</div>
			<!-- /.card-header -->
			<div class="card-body">
				<form id="frm_mu" name="frm_mu">
				<input type="hidden" name="mb_id" value="<?=$row[mb_id]?>">
				<input type="hidden" name="mb_name" value="<?=$row[mb_name]?>">
				<input type="hidden" name="mu_emp_id" value="<?=$member[mb_id]?>">
				<div class="callout callout-info">
					<h5>이름</h5>
					<p><?=$row[mb_name]?></p>
				</div>
				<div class="callout callout-info">
					<h5>연락처</h5>
					<p><input type="text" id="mu_mb_hp" name="mu_mb_hp" class="form-control" placeholder="<?=$row[mb_hp]?>" value="<?=$row[mb_hp]?>"></p>
				</div>
				<div class="callout callout-info">
					<h5>결제계좌</h5>
					<select class="form-control select2 select2-hidden-accessible" id="mu_num" name="mu_num" style="width: 100%;" data-select2-id="9" tabindex="-1" aria-hidden="true">
						<?
							$mu = explode("\r\n", $config['cf_mu_num']);
							for($i=0; $i < count($mu); $i++){
						?>
						<option data-select2-id="11" value="<?=$mu[$i]?>"><?=$mu[$i]?></option>
						<?	}?>
					</select>
				</div>
				<div class="callout callout-info">
					<h5>입금자명</h5>
					<p><input type="text" class="form-control" placeholder="" id="mu_mb_name" name="mu_mb_name" value="<?=$row[mb_name]?>"></p>
				</div>
				<div class="callout callout-info">
					<h5>상품선택</h5>
					<?
						$mb_type_ary = fnGetTypePre();
						for($i=0; $i < count($mb_type_ary); $i++){
					?>
					
						<div class="icheck-primary d-inline col-6">
							<input type="radio" id="radioPrimary<?=$i?>"  id="mu_mb_type" name="mu_mb_type" <?if($i == 0){echo "checked";}?> value="<?=$mb_type_ary[$i]?>" onChange="fnGetPrice('mu_mb_price',this.value)">
							<label for="radioPrimary<?=$i?>">
								<?=$mb_type_ary[$i]?>
							</label>
						</div>
					
					<?	}?>
				</div>
				<div class="callout callout-info">
					<h5>문자전송</h5>
					<?
						$mb_sms_ary = array("발송", "미발송");
						for($i=0; $i < count($mb_sms_ary); $i++){
					?>
					
						<div class="icheck-primary d-inline col-6">
							<input type="radio" id="radioSms<?=$i?>"  id="mu_sms" name="mu_sms" <?if($i == 0){echo "checked";}?> value="<?=$mb_sms_ary[$i]?>">
							<label for="radioSms<?=$i?>">
								<?=$mb_sms_ary[$i]?>
							</label>
						</div>
					
					<?	}?>
				</div>
				<div class="callout callout-info">
					<h5>금액</h5>
					<p><input type="text" class="form-control" name="mu_mb_price" id="mu_mb_price" placeholder="0" onkeyup="inputNumberFormat(this)"></p>
				</div>
				<div class="row">
					<div class="col-8"></div>
					<div class="col-4" style="text-align:right">
						<button type="button" class="btn btn-primary" onClick="fnMuSave()">결제요청</button>
						<button type="button" class="btn btn-secondary" onClick="closeBank()">닫기</button>
					</div>
				</div>
				</form>
			</div>
			<!-- /.card-body -->
		</div>
		<!-- /.card -->
	</div>
</div>


<div class="layer_pop02">
	<div class="col-md-12">
		<div class="card card-default">
			<div class="card-header">
				<h3 class="card-title">
					<i class="fas fa-money-check"></i>
					신용카드
				</h3>
			</div>
			<!-- /.card-header -->
			<div class="card-body">
				<form id="frm_cre" name="frm_cre">
				<input type="hidden" name="mb_id" id="cre_mb_id" value="<?=$row['mb_id']?>">
				<input type="hidden" name="mb_name" id="cre_mb_name" value="<?=$row['mb_name']?>">
				<input type="hidden" name="cre_emp_id" id="cre_emp_id" value="<?=$member['mb_id']?>">
				<!--input type="hidden" name="pay_company" value="웰컴페이먼츠"-->
				<div class="callout callout-info">
					<h5>결제수단</h5>
					<p>
						<label><input type="radio" name="pay_company" value="웰컴페이먼츠" checked> 웰컴페이먼츠</label>
						<label><input type="radio" name="pay_company" value="페이업" > 페이업</label>
					</p>
				</div>
				<div class="callout callout-info">
					<h5>이름</h5>
					<p><?=$row[mb_name]?></p>
				</div>
				<div class="callout callout-info">
					<div class="row">
						<div class="col-6">
							<h5>카드번호</h5>
							<div class="row">
								<div class="col-3">
									<input type="text" id="card_no_noenc1" name="card_no_noenc1" class="form-control auto_key" placeholder="" value="" maxlength="4">
								</div>
								<div class="col-3">
									<input type="text" id="card_no_noenc2" name="card_no_noenc2" class="form-control auto_key" placeholder="" value="" maxlength="4">
								</div>
								<div class="col-3">
									<input type="text" id="card_no_noenc3" name="card_no_noenc3" class="form-control auto_key" placeholder="" value="" maxlength="4">
								</div>
								<div class="col-3">
									<input type="text" id="card_no_noenc4" name="card_no_noenc4" class="form-control auto_key" placeholder="" value="" maxlength="4">
								</div>
							</div>
						</div>
						<div class="col-6">
							<h5>할부</h5>
							<select class="form-control select2 select2-hidden-accessible" id="card_sell_mm" name="card_sell_mm" style="width: 100%;" data-select2-id="9" tabindex="-1" aria-hidden="true">
								<?for($i=0; $i<=10; $i++){?>
								<?if($i==0){?>
								<option value="<?=str_pad($i, 2, "0", STR_PAD_LEFT)?>">일시불</option>
								<?}else{?>
								<option value="<?=str_pad($i, 2, "0", STR_PAD_LEFT)?>"><?=$i?>개월</option>
								<?}?>
								<?}?>
							</select>

						</div>
					</div>
				</div>
				<div class="callout callout-info on_mir" id="on_mir1">
					<div class="row">
						<div class="col-6 on_mir">
							<h5>생년월일</h5>
							<p><input type="text" class="form-control" id="card_holder_ymd_noenc" name="card_holder_ymd_noenc" value="" maxlength="6" placeholder="YYMMDD"></p>
						</div>
					</div>
				</div>
				<!--div class="callout callout-info">
					<h5>생년월일</h5>
					<p><input type="text" class="form-control" placeholder="" id="card_holder_ymd_noenc" name="card_holder_ymd_noenc" value="" maxlength="6"></p>
				</div-->
				<div class="callout callout-info">
					<div class="row">
						<div class="col-6 on_mir" id="on_mir2">
							<h5>비밀번호 앞2자리</h5>
							<div class="row">
								<div class="col-3">
									<p><input type="text" class="form-control" placeholder="" id="card_pw_noenc" name="card_pw_noenc" value="" maxlength="2"></p>
								</div>
								<div class="col-9">
									**
								</div>
							</div>
						</div>
						<!---div class="col-6">
							<h5>비밀번호 앞2자리</h5>
							<div class="row">
								<div class="col-3">
									<p><input type="text" class="form-control" placeholder="" id="card_pw_noenc" name="card_pw_noenc" value="" maxlength="2"></p>
								</div>
								<div class="col-9">
									**
								</div>
							</div>
						</div-->
						<div class="col-6">

							<h5>카드유효기간(MM / YY)</h5>
							<div class="row">
								<div class="col-3">
									<p><input type="text" class="form-control auto_key2" placeholder="" id="card_expiry_mm" name="card_expiry_mm" value="" maxlength="2"></p>
								</div>
								<div class="col-1">
									/
								</div>
								<div class="col-3">
									<p><input type="text" class="form-control auto_key3" placeholder="" id="card_expiry_yy" name="card_expiry_yy" value="" maxlength="2"></p>
								</div>
								<div class="col-5">
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="callout callout-info">
					<h5>상품선택</h5>
					<?
						$mb_type_ary = fnGetTypePre();
						for($i=0; $i < count($mb_type_ary); $i++){
					?>
					
						<div class="icheck-primary d-inline col-6">
							<input type="radio" id="radioPrimary_cre<?=$i?>"  id="cre_mb_type" name="cre_mb_type" <?if($i == 0){echo "checked";}?> value="<?=$mb_type_ary[$i]?>" onChange="fnGetPrice('cre_mb_price',this.value)">
							<label for="radioPrimary_cre<?=$i?>">
								<?=$mb_type_ary[$i]?>
							</label>
						</div>
					
					<?	}?>
				</div>
				<div class="callout callout-info">
					<h5>금액</h5>
					<p><input type="text" class="form-control" name="prodPrice" id="cre_mb_price" placeholder="0" onkeyup="inputNumberFormat(this)"></p>
				</div>
				<div class="row">
					<div class="col-8"></div>
					<div class="col-4" style="text-align:right">
						<button type="button" class="btn btn-primary" onClick="fnCreditSave()">결제요청</button>
						<button type="button" class="btn btn-secondary" onClick="closeCredit()">닫기</button>
					</div>
				</div>
				</form>
			</div>
			<!-- /.card-body -->
		</div>
		<!-- /.card -->
	</div>
</div>

<div class="layer_pop02_2">
	<div class="col-md-12">
		<div class="card card-default">
			<div class="card-header">
				<h3 class="card-title">
					<i class="fas fa-money-check"></i>
					수기결제
				</h3>
			</div>
			<!-- /.card-header -->
			<div class="card-body">
				<form id="frm_cre2" name="frm_cre2">
				<input type="hidden" name="mb_id" id="cre_mb_id" value="<?=$row['mb_id']?>">
				<input type="hidden" name="mb_name" id="cre_mb_name" value="<?=$row['mb_name']?>">
				<input type="hidden" name="cre_emp_id" id="cre_emp_id" value="<?=$member['mb_id']?>">
				<!--input type="hidden" name="pay_company" value="수기결제"-->
				<div class="callout callout-info">
					<h5>결제수단</h5>
					<p>
						<!--label><input type="radio" name="pay_company" value="오앤유(수기)" checked> 오앤유</label>
						<label><input type="radio" name="pay_company" value="엠터치(수기)" > 엠터치</label>
						<label><input type="radio" name="pay_company" value="쇼페이(수기)" > 쇼페이</label-->
						<label><input type="radio" name="pay_company" value="오후(수기)" checked> 오후</label>
						<label><input type="radio" name="pay_company" value="케이비(수기)"> 케이비</label>
						<label><input type="radio" name="pay_company" value="캠핑라인(수기)"> 캠핑라인</label>
						<label><input type="radio" name="pay_company" value="웨이업(수기)"> 웨이업</label>
						
						<!--label><input type="radio" name="pay_company" value="원넷(수기)"> 원넷</label>
						<label><input type="radio" name="pay_company" value="참좋은(수기)"> 참좋은</label-->
						<!--label><input type="radio" name="pay_company" value="온미르(수기)" > 온미르</label>
						<label><input type="radio" name="pay_company" value="다모아(수기)" > 다모아</label>
						<label><input type="radio" name="pay_company" value="코리아(수기)" > 코리아</label>
						<label><input type="radio" name="pay_company" value="페이업(수기)"> 페이업</label>
						<label><input type="radio" name="pay_company" value="세이프(수기)"  > 세이프</label>
						<label><input type="radio" name="pay_company" value="루멘(수기)"  > 루멘</label>
						<label><input type="radio" name="pay_company" value="페이츠(수기)" > 페이츠</label>
						<label><input type="radio" name="pay_company" value="웰페이(수기)"> 웰페이</label-->
						
						<!--label><input type="radio" name="pay_company" value="참좋은(수기)" checked> 참좋은</label-->
					</p>
				</div>
				<div class="callout callout-info">
					<h5>이름</h5>
					<p><?=$row['mb_name']?></p>
				</div>
				<div class="callout callout-info">
					<div class="row">
						<div class="col-6">
							<h5>카드사</h5>
							<div class="row">
								<select class="form-control select2 select2-hidden-accessible" id="card_name" name="card_name" style="width: 100%;" data-select2-id="9" tabindex="-1" aria-hidden="true">	
									<option value="삼성">삼성</option>
									<option value="KB">KB</option>
									<option value="신한">신한</option>
									<option value="현대">현대</option>
									<option value="비씨">비씨</option>
									<option value="농협">농협</option>
									<option value="롯데">롯데</option>
									<option value="하나">하나</option>
									<option value="씨티">씨티</option>
									<option value="우리">우리</option>
									<option value="카카오">카카오</option>
									<option value="기타">기타</option>
								</select>
							</div>
						</div>
						<div class="col-6">
							<h5>할부</h5>
							<select class="form-control select2 select2-hidden-accessible" id="card_sell_mm" name="card_sell_mm" style="width: 100%;" data-select2-id="9" tabindex="-1" aria-hidden="true">
								<?for($i=0; $i<=12; $i++){?>
								<?if($i==0){?>
								<option value="<?=str_pad($i, 2, "0", STR_PAD_LEFT)?>">일시불</option>
								<?}else{?>
								<option value="<?=str_pad($i, 2, "0", STR_PAD_LEFT)?>"><?=$i?>개월</option>
								<?}?>
								<?}?>
							</select>

						</div>
					</div>
				</div>
				<!--div class="callout callout-info">
					<h5>생년월일</h5>
					<p><input type="text" class="form-control" placeholder="" id="card_holder_ymd_noenc" name="card_holder_ymd_noenc" value="" maxlength="6"></p>
				</div-->
				<div class="callout callout-info">
					<h5>상품선택</h5>
					<?
						$mb_type_ary = fnGetTypePre();
						for($i=0; $i < count($mb_type_ary); $i++){
					?>
					
						<div class="icheck-primary d-inline col-6">
							<input type="radio" id="radioPrimary_cre2<?=$i?>"  id="cre_mb_type" name="cre_mb_type" <?if($i == 0){echo "checked";}?> value="<?=$mb_type_ary[$i]?>" onChange="fnGetPrice('cre_mb_price2',this.value)">
							<label for="radioPrimary_cre2<?=$i?>">
								<?=$mb_type_ary[$i]?>
							</label>
						</div>
					
					<?	}?>
				</div>
				<div class="callout callout-info">
					<h5>금액</h5>
					<p><input type="text" class="form-control" name="prodPrice" id="cre_mb_price2" placeholder="0" onkeyup="inputNumberFormat(this)"></p>
				</div>
				<div class="row">
					<div class="col-8"></div>
					<div class="col-4" style="text-align:right">
						<button type="button" class="btn btn-primary" onClick="fnCreditSave('su')">결제요청</button>
						<button type="button" class="btn btn-secondary" onClick="closeCredit2()">닫기</button>
					</div>
				</div>
				</form>
			</div>
			<!-- /.card-body -->
		</div>
		<!-- /.card -->
	</div>
</div>


<!-- /.card -->
<script>
$(function(){
	fnGetPrice('mu_mb_price', $("input:radio[name='mu_mb_type']:checked").val());		
	fnGetPrice('cre_mb_price', $("input:radio[name='cre_mb_type']:checked").val());		
	fnGetPrice('cre_mb_price2', $("input:radio[name='cre_mb_type']:checked").val());		
	$(".on_mir").hide();
	$("input[name='pay_company']").change(function(){

		if($("input[name='pay_company']:checked").val()== "페이업"){
			$(".on_mir").show();
			$("#card_sell_mm").empty();
			$("#card_sell_mm").append("<option value='00'>일시불</option>");
			$("#card_sell_mm").append("<option value='01'>1개월</option>");
			$("#card_sell_mm").append("<option value='02'>2개월</option>");
			$("#card_sell_mm").append("<option value='03'>3개월</option>");
			$("#card_sell_mm").append("<option value='04'>4개월</option>");
			$("#card_sell_mm").append("<option value='05'>5개월</option>");
			$("#card_sell_mm").append("<option value='06'>6개월</option>");
			$("#card_sell_mm").append("<option value='07'>7개월</option>");
			$("#card_sell_mm").append("<option value='08'>8개월</option>");
			$("#card_sell_mm").append("<option value='09'>9개월</option>");
			$("#card_sell_mm").append("<option value='10'>10개월</option>");
			$("#card_sell_mm").append("<option value='11'>11개월</option>");
			$("#card_sell_mm").append("<option value='12'>12개월</option>");
		}else{
			$(".on_mir").hide();
			$("#card_sell_mm").empty();
			$("#card_sell_mm").append("<option value='00'>일시불</option>");
			$("#card_sell_mm").append("<option value='01'>1개월</option>");
			$("#card_sell_mm").append("<option value='02'>2개월</option>");
			$("#card_sell_mm").append("<option value='03'>3개월</option>");
			$("#card_sell_mm").append("<option value='04'>4개월</option>");
			$("#card_sell_mm").append("<option value='05'>5개월</option>");
			$("#card_sell_mm").append("<option value='06'>6개월</option>");
			$("#card_sell_mm").append("<option value='07'>7개월</option>");
			$("#card_sell_mm").append("<option value='08'>8개월</option>");
			$("#card_sell_mm").append("<option value='09'>9개월</option>");
			$("#card_sell_mm").append("<option value='10'>10개월</option>");
		}
	});

});

var ajax_mu = true;
function fnMuSave(){
	
	if($("#mu_mb_hp").val() == ""){
		alert("연락처를 입력하세요.");
		$("#mu_mb_hp").focus();
		return false;
	}

	if($("#mu_mb_name").val() == ""){
		alert("입금자명을 입력하세요.");
		$("#mu_mb_name").focus();
		return false;
	}

	if($("#cre_mb_price").val() < 1){
		alert("결제금액을 입력하세요.");
		$("#mu_mb_price").focus();
		return false;
	}

	if(ajax_mu == false){
		alert("처리중입니다. 잠시만 기다려주세요.");
		return false;
	}

	if(confirm("무통장 신청을 신청하시겠습니까?")==true){
		var string = $("form[name=frm_mu]").serialize();

		ajax_mu = false;

		$.ajax({
			type: "POST",
			url: "ajax.mu.save.php",
			data: string, 
			cache: false,
			async: false,
			contentType : "application/x-www-form-urlencoded; charset=UTF-8",
			success: function(data) {
				alert("정상적으로 처리되었습니다.");
				ajax_mu = true;
				location.reload();
			}
		});
		return false;

	}
}

var CreaitAjax = true;
function fnCreditSave(v){
	if(CreaitAjax == false){
		alert("처리중입니다. 잠시만 기다려주세요.");
		return false;
	}
	if(v != "su"){
		if($("#card_no_noenc1").val().length < 4 || $("#card_no_noenc2").val().length < 4 || $("#card_no_noenc3").val().length < 4 || $("#card_no_noenc4").val().length < 3){
			alert("카드번호를 올바르게 입력하세요.");
			return false;
		}
	}
	
	/*if($("#card_holder_ymd_noenc").val().length < 6){
		alert("생년월일을 올바르게 입력하세요.");
		$("#card_holder_ymd_noenc").focus();
		return false;
	}
	if($("#card_pw_noenc").val().length < 2){
		alert("비밀번호를 올바르게 입력하세요.");
		$("#card_pw_noenc").focus();
		return false;
	}*/
	if(v != "su"){
		if($("#card_expiry_mm").val().length < 2){
			alert("유효기간을 올바르게 입력하세요.");
			$("#card_expiry_mm").focus();
			return false;
		}
		if($("#card_expiry_yy").val().length < 2){
			alert("유효기간을 올바르게 입력하세요.");
			$("#card_expiry_yy").focus();
			return false;
		}
	}

	if($("#mu_mb_price").val() < 1){
		alert("결제금액을 입력하세요.");
		$("#mu_mb_price").focus();
		return false;
	}

	if(confirm("결제를 진행하시겠습니까?")== true){
		CreaitAjax = false;
		if(v == "su"){
			var string = $("form[name=frm_cre2]").serialize();
		}else{
			var string = $("form[name=frm_cre]").serialize();
		}
		
		
		$.ajax({
			type: "POST",
			url: "../payment/payment.php",
			data: string,
			//dataType : "json",
			cache: false,
			async: false,
			contentType : "application/x-www-form-urlencoded; charset=UTF-8",
			success: function(data) {
				console.log(data);
				if(data == "0000"){
					alert("정상적으로 처리되었습니다.");
					location.reload();
				}else{
					alert(data);
					CreaitAjax = true;
					return false;
				}
			}
		});
	}
}

function fnGetPrice(col, type){
	$.ajax({
		type: "POST",
		url: "ajax.getPrice.php",
		data: {type : type}, 
		cache: false,
		async: false,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {
			$("#"+col).val(data);
		}
	});
	return false;
}
function openBank(){
	$(".layer_pop01").show();
	$(".abs_div").show();
}

function closeBank(){
	$(".layer_pop01").hide();
	$(".abs_div").hide();
}

function openCredit(){
	$(".layer_pop02").show();
	$(".abs_div").show();
}

function closeCredit(){
	$(".layer_pop02").hide();
	$(".abs_div").hide();
}

function openCredit2(){
	$(".layer_pop02_2").show();
	$(".abs_div").show();
}

function closeCredit2(){
	$(".layer_pop02_2").hide();
	$(".abs_div").hide();
}


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
	if($("#recent_select").val() == ""){
		alert("메모를 선택하세요.");
		$("#recent_select").focus();
		return false;
	}

	if($("#recent_memo").val().replace(/ /gi,'') == ""){
		alert("상담내용을 입력하세요.");
		$("#recent_memo").focus();
		return false;
	}

	if($("#alarm_select").val() != ""){
		if($("#alarm_date").val() == ""){
			alert("알람 날짜를 지정해주세요.");
			$("#alarm_date").focus();
			return false;
		}
	}
	return true;
}
var isAjax = true;
function fnPwReset(){
	if(isAjax == false){
		alert("처리중입니다. 잠시만 기다려주세요.");
		return false;
	}
	isAjax = false;
	if(confirm("아이디를 전송하시겠습니까?") == true){
		$.ajax({
			type: "POST",
			url: "ajax.sms.id.send.php",
			data: {mb_id : "<?=$mb_id?>"},
			cache: false,
			async: false,
			contentType : "application/x-www-form-urlencoded; charset=UTF-8",
			success: function(data) {
				alert("정상적으로 처리되었습니다.");
				isAjax = true;
			}
		});
	}
}
var isAjax2 = true;
function fnPwReset2(){
	if(isAjax2 == false){
		alert("처리중입니다. 잠시만 기다려주세요.");
		return false;
	}
	isAjax2 = false;
	if(confirm("아이디를 전송하시겠습니까?") == true){
		$.ajax({
			type: "POST",
			url: "ajax.sms.id.send2.php",
			data: {mb_id : "<?=$mb_id?>"},
			cache: false,
			async: false,
			contentType : "application/x-www-form-urlencoded; charset=UTF-8",
			success: function(data) {
				alert("정상적으로 처리되었습니다.");
				isAjax2 = true;
			}
		});
	}
}

function inputPhoneNumber(obj) {

    var number = obj.value.replace(/[^0-9]/g, "");
    var phone = "";



    if(number.length <= 4) {
        return number;
    } else if(number.length <= 8) {
        phone += number.substr(0, 4);
        phone += "-";
        phone += number.substr(4);
    } else if(number.length <= 12) {
        phone = number.substr(0, 4);
        phone += "-";
        phone += number.substr(4, 4);
        phone += "-";
        phone += number.substr(8, 4);
        phone += "-";
        phone += number.substr(12);

    } else {
        phone = number.substr(0, 4);
        phone += "-";
        phone += number.substr(4, 4);
        phone += "-";
		phone += number.substr(8, 4);
        phone += "-";
        phone += number.substr(12);
    }
    obj.value = phone;
}

function fnSendYak(){
	if(confirm("약관을 전송하시겠습니까?")==true){
		$.ajax({
			type: "POST",
			url: "ajax.proc.view.php",
			data: {mb_id : "<?=$mb_id?>", ly_type : $("#yak_select").val()}, 
			cache: false,
			async: false,
			contentType : "application/x-www-form-urlencoded; charset=UTF-8",
			success: function(data) {
				alert("정상적으로 약관이 발송되었습니다.");
			}
		});
		return false;
	}
}

function fnInProc(type, mb_id){
	if(confirm("인계"+type+"를 진행하시겠습니까?") == true){
		location.href="./proc.mbin.php?type="+type+"&mb_id="+mb_id;
	}
}

$(function() {
    $(".auto_key").keyup (function () {
		var charLimit = $(this).attr("maxlength");
        if (this.value.length >= charLimit) {
            $(this).parent().next().children('.auto_key').focus();
            return false;
        }
    });
	$(".auto_key2").keyup (function () {
		var charLimit = $(this).attr("maxlength");
        if (this.value.length >= charLimit) {
            $('.auto_key3').focus();
            return false;
        }
    });
});
</script>