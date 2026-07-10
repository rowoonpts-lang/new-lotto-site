<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.php");
	

	$sql_common = " from l_pay a, g5_member b ";
	$sql_search = " where 1=1 and a.mb_id = b.mb_id and pay_method = '신용카드' and pay_company in ('페이업(수기)','코리아(수기)','페이올(수기)','세이프(수기)','다모아(수기)','루멘(수기)','온미르(수기)','페이츠(수기)','참좋은(수기)','오앤유(수기)','쇼페이(수기)','엠터치(수기)','웰페이(수기)','케이비(수기)','캠핑라인(수기)','원넷(수기)','웨이업(수기)','오후(수기)','수기결제') ";
	$sql_order = " order by lp_pay_datetime desc ";

	if($sch_select){
		$sql_search .= " and {$sch_select} like '%{$sch_text}%' ";
	}else{
		$sql_search .= " and (b.mb_code like '%{$sch_text}%' or b.mb_name like '%{$sch_text}%' or a.mb_hp like '%{$sch_text}%' or a.mb_id like '%{$sch_text}%') ";
	}

	if($sch_mb_type){
		$sql_search .= " and mb_type = '{$sch_mb_type}' ";
	}

	if($start_date){
		$sql_search .= " and substr(lp_pay_datetime,1,10) >= substr('{$start_date}',1,10) ";
	}
	if($end_date){
		$sql_search .= " and substr(lp_pay_datetime,1,10) <= substr('{$end_date}',1,10) ";
	}

	

	$sql = " select count(distinct a.lp_id) as cnt {$sql_common} {$sql_search} {$sql_order} ";

	$row = sql_fetch($sql);
	$total_count = $row['cnt'];


	$rows = 30;
	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함'


	$limit = " limit {$from_record}, {$rows} ";

	$sql = "select * {$sql_common} {$sql_search} {$sql_order} {$limit}";
	$result = sql_query($sql);
?>

<div class="card card-default">
	<div class="card-body">
		<div class="col-12">
		<form id="" name=""  autocomplete="off">
			<div class="row">
				<div class="col-md-2">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="sch_select" aria-hidden="true" autocomplete="off">
						<option selected="selected" value="">전체</option>
						<option value="a.mb_code" <?if($sch_select == "a.mb_code"){echo "selected";}?>>회원코드</option>
						<option value="a.mb_name" <?if($sch_select == "a.mb_name"){echo "selected";}?>>회원명</option>
						<option value="a.mb_hp" <?if($sch_select == "a.mb_hp"){echo "selected";}?>>연락처</option>
						<option value="a.mb_id" <?if($sch_select == "a.mb_id"){echo "selected";}?>>아이디</option>
					</select>
				</div>
				<div class="col-md-2">
					<div class="row">
						<div class="col-md-8">
							<input type="text" class="form-control" name="sch_text" value="<?=$sch_text?>" placeholder="Enter ...">
						</div>
						<div class="col-md-4">
							<button class="btn btn-block btn-danger">검색</button>
						</div>
					</div>
				</div>
				<div class="col-md-2">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="sch_mb_type" aria-hidden="true">
						<option selected="selected" value="">등급전체</option>
						<?
						$mb_type_ary = fnGetType();
						for($i=0; $i < count($mb_type_ary); $i++){
						?>
						<option value="<?=$mb_type_ary[$i]?>" <?if($sch_mb_type == $mb_type_ary[$i]){echo "selected";}?>><?=$mb_type_ary[$i]?></option>
						<?
						}
						?>
					</select>
				</div>
				<div class="col-md-3">
					<div class="row">
						<div class="col-md-6">
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">
										<i class="far fa-calendar-alt"></i>
									</span>
								</div>
								<input type="text" class="form-control float-right dateinput" name="start_date" value="<?=$start_date?>">
							</div>
						</div>
						<div class="col-md-6">
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">
										<i class="far fa-calendar-alt"></i>
									</span>
								</div>
								<input type="text" class="form-control float-right dateinput" name="end_date" value="<?=$end_date?>">
							</div>
						</div>
					</div>
				</div>
				<!--div class="col-md-1">
					<button class="btn btn-block btn-success" type="button" onClick="fnExcel()">엑셀다운</button>
				</div-->
			</div>
		</form>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"></h3>
			</div>
			<!-- /.card-header -->
			<div class="card-body table-responsive p-0">
				<table class="table table-hover text-nowrap">
				<thead>
				<tr>
					<th>NO</th>
					<th>신청일</th>
					<th>회원코드</th>
					<th>회원명/연락처</th>
					<th>아이디</th>
					<th>신청등급</th>
					<th>현재등급</th>
					<th>카드사</th>
					<th>PG사</th>
					<th>개월수</th>
					<th>결제금액</th>
					<th>담당자</th>
					<th>승인확인</th>
					<th>삭제</th>
				</tr>
				</thead>
				<tbody>
				<?for($i=0; $row = sql_fetch_array($result); $i++){?>
				<tr>
					<td><?=$total_count-($page-1)*$rows-$i?></td>
					<td><?=$row['lp_datetime']?></td>
					<td><a href="<?=G5_LADMIN_URL?>/member/member.all.php?sch_select=a.mb_code&sch_text=<?=$row['mb_code']?>"><?=$row['mb_code']?></td>
					<td>
						<?=$row['mb_name']?><br>
						<?=$row['mb_hp']?>
					</td>
					<td><a href=""  onclick="fnMemmberMemo('<?=base64_encode($row[mb_id])?>')"><?=$row['mb_id']?></a></td>
					<td><?=$row['lp_type']?></td>
					<td><?=$row['mb_type']?></td>
					<td><?=$row['card_name']?></td>
					<td><?=$row['pay_company']?></td>
					<td><?=$row['card_sell_mm']?></td>
					<td><?=number_format($row['lp_price'])?></td>
					<td><?=$member_info[$row['emp_id']]?></td>
					<td>
						<?if($row[lp_status] == "주문"){?>
						<button type="button" class="btn btn-success" onClick="fnPayCredit('<?=$row[lp_id]?>')">승인</button>
						<?}?>
					</td>
					
					<!--td><?=number_format($row['lp_price']/11*10)?></td-->
					<!--td>
						<?
							if($row['lp_status'] == "입금"){
								echo "완료";
							}else{
								echo $row['lp_status'];
							}
						?>
					</td>
					<td><?=$member_info[$row['emp_id']]?></td>
					<td><?=$row['lp_datetime']?></td>
					<td><?=$row['lp_pay_datetime']?></td>
					<td>
						<?if($row[lp_status] == "주문"){?>
						<button type="button" class="btn btn-success" onClick="fnPayCredit('<?=$row[lp_id]?>')">입금확인</button>
						<?}?>
					</td-->
					<td>
						<button type="button" class="btn btn-danger" onClick="fnProcDel('l_pay','lp_id','<?=$row[lp_id]?>')">삭제</button>
					</td>
				</tr>
				<?}?>
				<?if($total_count < 1){?>
				<tr>
					<td colspan="12">내역이 없습니다.</td>
				</tr>
				<?}?>
				</tbody>
				</table>
				<?php 
					$qstr .= "&sch_select={$sch_select}&sch_text={$sch_text}&sch_mb_type={$sch_mb_type}&start_date={$start_date}&end_date={$end_date}";
					echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); 
				?>
			</div>
			<!-- /.card-body -->
		</div>
		<!-- /.card -->
	</div>
</div>

<script>
function fnExcel(){
	location.href="./payment.mu.excel.php?1=1<?=$qstr?>";
}


var cancleSW = true;
function fnPayCancel(lp_id){
	if(cancleSW == false){
		alert("잠시만 기다려주세요.");
	}
	if(confirm("해당 결제건을 취소하시겠습니까?") == true){
		cancleSW = false;
		location.href="<?=G5_LADMIN_URL?>/member/payment.cancel.php?lp_id="+lp_id

	}
	
}

var cancleMu = true;
function fnPayCredit(lp_id){
	if(cancleMu == false){
		alert("잠시만 기다려주세요.");
	}
	if(confirm("해당 결제건을 입금확인 하시겠습니까?") == true){
		cancleMu = false;
		location.href="<?=G5_LADMIN_URL?>/payment/payment.credit.pay.php?lp_id="+lp_id
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
<?
	include_once(G5_LADMIN_PATH."/tail.php");
?>