<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.php");
	

	$sql_common = " from l_pay a LEFT JOIN g5_member d ON (a.confirm_user = d.mb_id), g5_member b, g5_member_etc c ";
	$sql_search = " where 1=1 and a.mb_id = c.mb_id and a.mb_id = b.mb_id and lp_status = '입금' ";
	$sql_order = " order by lp_pay_datetime desc ";

	if($sch_select){
		$sql_search .= " and {$sch_select} like '%{$sch_text}%' ";
	}else{
		$sql_search .= " and (b.mb_code like '%{$sch_text}%' or b.mb_name like '%{$sch_text}%' or a.mb_hp like '%{$sch_text}%' or a.mb_id like '%{$sch_text}%') ";
	}

	if($sch_mb_type){
		$sql_search .= " and b.mb_type = '{$sch_mb_type}' ";
	}

	if($start_date){
		$sql_search .= " and substr(lp_pay_datetime,1,10) >= substr('{$start_date}',1,10) ";
	}
	if($end_date){
		$sql_search .= " and substr(lp_pay_datetime,1,10) <= substr('{$end_date}',1,10) ";
	}

	if($mb_team){
		$sql_search .= " and d.mb_team = '{$mb_team}' ";
	}
	if($pay_method){
		if($pay_method == "무통장" || $pay_method == "신용카드"){
			$sql_search .= " and a.pay_method = '{$pay_method}' ";
		}else{
			$sql_search .= " and a.pay_company = '{$pay_method}' ";
		}
	}


	$sql = " select count(distinct a.lp_id) as cnt {$sql_common} {$sql_search} {$sql_order} ";

	$row = sql_fetch($sql);
	$total_count = $row['cnt'];


	$rows = 30;
	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함'

	$sql = " select sum(lp_price) sum {$sql_common} {$sql_search} and lp_status = '입금' {$sql_order} ";
	$row = sql_fetch($sql);
	$tot_amt = $row[sum];


	$limit = " limit {$from_record}, {$rows} ";

	$sql = "select a.*,b.*,c.*, d.mb_team as emp_mb_team {$sql_common} {$sql_search} {$sql_order} {$limit}";

	$result = sql_query($sql);
	//echo $sql;

?>

<div class="card card-default">
	<div class="card-body">
		<div class="col-12">
		<form id="" name="" autocomplete="off">
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
				
				<div class="col-md-1">
					<div class="row">
						<div class="col-md-12">
							<input type="text" class="form-control" name="sch_text" value="<?=$sch_text?>" placeholder="Enter ...">
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
				<div class="col-md-1">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="mb_team" aria-hidden="true" autocomplete="off">
						<option selected="selected" value="">팀전체</option>
						<?
							$teamList = getTeamList();
							for($i=0; $i < count($teamList); $i++){
						?>
						<option value="<?=$teamList[$i]?>" <?if($mb_team == $teamList[$i]){echo "selected";}?>><?=$teamList[$i]?></option>
						<?}?>
					</select>
				</div>
				<div class="col-md-1">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="pay_method" aria-hidden="true" autocomplete="off">
						<option selected="selected" value="">결제방식</option>
						<option value="무통장" <?if($pay_method == "무통장"){echo "selected";}?>>무통장</option>
						<option value="신용카드" <?if($pay_method == "신용카드"){echo "selected";}?>>신용카드</option>
						<!--option value="웰컴페이먼츠" <?if($pay_method == "웰컴페이먼츠"){echo "selected";}?>>웰컴</option>
						<option value="페이업" <?if($pay_method == "페이업"){echo "selected";}?>>페이업</option>
						<option value="참좋은(수기)" <?if($pay_method == "참좋은(수기)"){echo "selected";}?>>참좋은(수기)</option>
						<option value="온미르(수기)" <?if($pay_method == "온미르(수기)"){echo "selected";}?>>온미르(수기)</option>
						<option value="루멘(수기)" <?if($pay_method == "루멘(수기)"){echo "selected";}?>>루멘(수기)</option>
						<option value="페이츠(수기)" <?if($pay_method == "페이츠(수기)"){echo "selected";}?>>페이츠(수기)</option>
						<option value="세이프(수기)" <?if($pay_method == "세이프(수기)"){echo "selected";}?>>세이프(수기)</option>						
						<option value="페이업(수기)" <?if($pay_method == "페이업(수기)"){echo "selected";}?>>페이업(수기)</option>
						<option value="다모아(수기)" <?if($pay_method == "다모아(수기)"){echo "selected";}?>>다모아(수기)</option>
						<option value="코리아(수기)" <?if($pay_method == "코리아(수기)"){echo "selected";}?>>코리아(수기)</option>
						<option value="원넷(수기)" <?if($pay_method == "원넷(수기)"){echo "selected";}?>>원넷(수기)</option>
						<option value="웰페이(수기)" <?if($pay_method == "웰페이(수기)"){echo "selected";}?>>웰페이(수기)</option-->
						<option value="케이비(수기)" <?if($pay_method == "케이비(수기)"){echo "selected";}?>>케이비(수기)</option>
						<option value="캠핑라인(수기)" <?if($pay_method == "캠핑라인(수기)"){echo "selected";}?>>캠핑라인(수기)</option>
						<option value="웨이업(수기)" <?if($pay_method == "웨이업(수기)"){echo "selected";}?>>웨이업(수기)</option>
						<option value="오후(수기)" <?if($pay_method == "오후(수기)"){echo "selected";}?>>오후(수기)</option>
						<!--option value="오앤유(수기)" <?if($pay_method == "오앤유(수기)"){echo "selected";}?>>오앤유(수기)</option>
						<option value="쇼페이(수기)" <?if($pay_method == "쇼페이(수기)"){echo "selected";}?>>쇼페이(수기)</option>
						<option value="엠터치(수기)" <?if($pay_method == "엠터치(수기)"){echo "selected";}?>>엠터치(수기)</option-->
						
						
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
				<div class="col-md-1">
					<button class="btn btn-block btn-danger">검색</button>
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
			<?if($start_date || $end_date){?>
			<div class="card-header">
				<h3 class="card-title" style="font-size:19px;font-weight:600">총 매출액 : <?=number_format($tot_amt/11*10)?></h3>
			</div>
			<?}?>
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
					<th>결제상태</th>
					<th>PG사</th>
					<th>결제일</th>
					<th>DB경로</th>
					<th>담당자</th>
					<th>취소</th>
					<th>삭제</th>
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
								if($row['pay_company'] == "수기결제" || $row['pay_company'] == "페이업(수기)" || $row['pay_company'] == "코리아(수기)" || $row['pay_company'] == "세이프(수기)" || $row['pay_company'] == "다모아(수기)" || $row['pay_company'] == "루멘(수기)" || $row['pay_company'] == "온미르(수기)" || $row['pay_company'] == "온페이츠(수기)" || $row['pay_company'] == "참좋은(수기)" || $row['pay_company'] == "쇼페이(수기)" || $row['pay_company'] == "웰페이(수기)" || $row['pay_company'] == "케이비(수기)" || $row['pay_company'] == "캠핑라인(수기)" || $row['pay_company'] == "원넷(수기)" || $row['pay_company'] == "웨이업(수기)" || $row['pay_company'] == "오후(수기)"){
									echo "<br>(수기)".$row['card_name']." (".$row['card_sell_mm']."개월)";
								}else{
									echo "<br>".$row['card_name']." (".$row['card_sell_mm']."개월)";
								}
							}
						?>
					</td>
					<td><?=number_format($row['lp_price']/11*10)?></td>
					<td>
						<?
							if($row['lp_status'] == "입금"){
								echo "완료";
							}else if($row['lp_status'] == "주문"){
								echo "대기";
							}else{
								echo $row['lp_status'];
							}
						?>
					</td>
					<td><?
							if($row['pay_company'] == "수기결제" || $row['pay_company'] == "페이업(수기)" || $row['pay_company'] == "코리아(수기)" || $row['pay_company'] == "세이프(수기)" || $row['pay_company'] == "다모아(수기)" || $row['pay_company'] == "루멘(수기)" || $row['pay_company'] == "온미르(수기)" || $row['pay_company'] == "참좋은(수기)" || $row['pay_company'] == "페이츠(수기)" || $row['pay_company'] == "쇼페이(수기)" || $row['pay_company'] == "웰페이(수기)" || $row['pay_company'] == "케이비(수기)" || $row['pay_company'] == "캠핑라인(수기)" || $row['pay_company'] == "원넷(수기)" || $row['pay_company'] == "웨이업(수기)" || $row['pay_company'] == "오후(수기)"){
								echo $row['pay_company'];
							}else{
								if($row['pay_method']== "신용카드" && $row['pay_company'] == "웰컴페이먼츠"){
									echo "웰컴";
								}
								if($row['pay_method']== "신용카드" && $row['pay_company'] == "페이업"){
									echo "페이업";
								}
								if($row['pay_method']== "신용카드" && $row['pay_company'] == "페이링"){
									echo "페이업";
								}
							}
						?></td>
					<td><?=$row['lp_pay_datetime']?></td>
					<td><?=$row['mb_db']?></td>
					<td>
						<?=$member_info[$row['confirm_user']]?>
					</td>
					<td>
						<?if($row[lp_status] != "취소"){?>
						<button type="button" class="btn btn-danger" onClick="fnPayCancel('<?=$row[lp_id]?>')">취소</button>
						<?}?>
						
						<?if($row[lp_status] == "취소"){?>
							<?=$row[lp_cancel_datetime]?>
						<?}?>
					</td>
					<td>
						<button type="button" class="btn btn-danger" onClick="fnProcDel('l_pay','lp_id','<?=$row[lp_id]?>')">삭제</button>
					</td>
				</tr>
				<?}?>
				<?if($total_count < 1){?>
				<tr>
					<td colspan="14">내역이 없습니다.</td>
				</tr>
				<?}?>
				</tbody>
				</table>
				<?php 
					$qstr .= "&sch_select={$sch_select}&sch_text={$sch_text}&sch_mb_type={$sch_mb_type}&start_date={$start_date}&end_date={$end_date}&pay_method={$pay_method}";
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