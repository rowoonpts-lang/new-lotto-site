<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.php");
	

	$sql_common = " from l_pay_in a, g5_member b, g5_member_etc c ";
	$sql_search = " where 1=1 and a.mb_id = c.mb_id and a.mb_id = b.mb_id and lp_status = '입금' ";
	$sql_order = " order by confirm_in_datetime desc, lp_pay_datetime desc ";

	if($sch_select){
		if($sch_select == "emp_mb_id"){
			$sql_search .= " and in_mb_name like '%{$sch_text}%' ";
		}else{
			$sql_search .= " and {$sch_select} like '%{$sch_text}%' ";
		}

	}else{
		$sql_search .= " and (b.mb_code like '%{$sch_text}%' or b.mb_name like '%{$sch_text}%' or a.mb_hp like '%{$sch_text}%' or a.mb_id like '%{$sch_text}%' or in_mb_name like '%{$sch_text}%') ";
	}

	if($sch_mb_type){
		if($sch_mb_type == "S1"){
			$sql_search .= " and (in_type = '0' or in_type = '1') ";
		}else{
			$sql_search .= " and in_type = '2' ";
		}
	}

	if($start_date){
		$sql_search .= " and substr(confirm_in_datetime,1,10) >= substr('{$start_date}',1,10) ";
	}
	if($end_date){
		$sql_search .= " and substr(confirm_in_datetime,1,10) <= substr('{$end_date}',1,10) ";
	}

	

	$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";

	$row = sql_fetch($sql);
	$total_count = $row['cnt'];


	$rows = 30;
	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함'

	$sql = " select sum(in_price) sum {$sql_common} {$sql_search} and lp_status = '입금' {$sql_order} ";
	$row = sql_fetch($sql);
	$tot_amt = $row[sum];

	$limit = " limit {$from_record}, {$rows} ";

	$sql = "select * {$sql_common} {$sql_search} {$sql_order} {$limit}";
	$result = sql_query($sql);

?>

<div class="card card-default">
	<div class="card-body">
		<div class="col-12">
		<form id="" name="" autocomplete="off">
			<div class="row">
				<div class="col-md-2">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="sch_mb_type" aria-hidden="true">
						<option selected="selected" value="">팀전체</option>
						<?
						$mb_type_ary = getTeamList3();
						for($i=0; $i < count($mb_type_ary); $i++){
						?>
						<option value="<?=$mb_type_ary[$i]?>" <?if($sch_mb_type == $mb_type_ary[$i]){echo "selected";}?>><?=$mb_type_ary[$i]?></option>
						<?
						}
						?>
					</select>
				</div>
				<div class="col-md-2">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="sch_select" aria-hidden="true" autocomplete="off">
						<option selected="selected" value="">전체</option>
						<option value="emp_mb_id" <?if($sch_select == "emp_mb_id"){echo "selected";}?>>승인자</option>
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
				<div class="col-md-1">
					<button class="btn btn-block btn-success" type="button" onClick="fnExcel()">엑셀다운</button>
				</div>
			</div>
		</form>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title" style="font-size:19px;font-weight:600">총 승인 금액 : <?=number_format($tot_amt)?></h3>
			</div>
			<!-- /.card-header -->
			<div class="card-body table-responsive p-0">
				<table class="table table-hover text-nowrap">
				<thead>
				<tr>
					<th>NO</th>
					<th>회원코드</th>
					<th>회원명/연락처</th>
					<th>아이디</th>
					<th>등급</th>
					<th>결제방법</th>
					<th>결제금액</th>
					<th>결제일</th>
					<th>승인일</th>
					<th>DB경로</th>
					<th>승인자</th>
					<th>취소</th>
				</tr>
				</thead>
				<tbody>
				<?for($i=0; $row = sql_fetch_array($result); $i++){?>
				<tr>
					<td><?=$total_count-($page-1)*$rows-$i?></td>
					<td><a href="<?=G5_LADMIN_URL?>/member/member.all.php?sch_select=a.mb_code&sch_text=<?=$row['mb_code']?>"><?=$row['mb_code']?></td>
					<td>
						<?=$row['mb_name']?><br>
						<?=$row['mb_hp']?>
					</td>
					<td><a href=""  onclick="fnMemmberMemo('<?=base64_encode($row[mb_id])?>')"><?=$row['mb_id']?></a></td>
					<td><?=$row['mb_type']?></td>
					<td>
						<?=$row['pay_method']?>
						<?	if($row['pay_method'] == "무통장"){
								$mu_ary = explode(" ",$row['mu_num']);
								echo "<br>".$mu_ary[0]." (".$row['mu_mb_name'].")";
							}else{
								echo "<br>".$row['card_name']." (".$row['card_sell_mm']."개월)";
							}
						?>
					</td>
					<td><?=number_format($row['in_price'])?></td>
					<td><?=$row['lp_pay_datetime']?></td>
					<td><?=$row['confirm_in_datetime']?></td>
					<td><?=$row['mb_db']?></td>
					<td>
						<?
							if($row[in_type] == "0"){
								echo $member_info[$row['in_mb_id']];
							}
							if($row[in_type] == "1"){
								echo $member_info[$row['confirm_in1']]."/".$member_info[$row['confirm_user']];
								//echo $member_info[$row['in_mb_id']];
							}
							if($row[in_type] == "2"){
								echo $member_info[$row['confirm_user']];
								//echo $member_info[$row['confirm_in1']]."/".$member_info[$row['confirm_user']];
							}
						?>
					</td>
					<td>
						<?if($row[lp_status] != "취소"){?>
						<button type="button" class="btn btn-danger" onClick="fnPayInCancel('<?=$row[lp_id]?>')">취소</button>
						<?}?>
						
						<?if($row[lp_status] == "취소"){?>
							<?=$row[lp_cancel_datetime]?>
						<?}?>
					</td>
				</tr>
				<?}?>
				<?if($total_count < 1){?>
				<tr>
					<td colspan="11">내역이 없습니다.</td>
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
	location.href="./payment.all.excel.php?1=1<?=$qstr?>";
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


</script>
<?
	include_once(G5_LADMIN_PATH."/tail.php");
?>