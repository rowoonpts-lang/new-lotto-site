<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.php");
	$sql_common = " from l_ad_list a, l_ad_user b ";
	$sql_search = " where 1=1 and a.lu_type = b.lu_type and a.lu_code = b.lu_code and a.tel not in ('01063411646','01063943588 ','01064491829','01047118874','01094430200','01030211877')";
	$sql_order = " order by a.ll_datetime desc ";

	if($sch_text){
		$sql_search .= " and tel like '%{$sch_text}%' ";
	}
	
	if($start_num){
		$sql_search .= " and a.idx >= {$start_num} ";
	}
	if($end_num){
		$sql_search .= " and a.idx <= {$end_num} ";
	}
	if($start_date){
		$sql_search .= " and substr(a.ll_datetime,1,10) >= '{$start_date}' ";
	}
	if($end_date){
		$sql_search .= " and substr(a.ll_datetime,1,10) <= '{$end_date}' ";
	}
	
	if($sch_lu_code){
		$sql_search .= " and a.lu_code = '{$sch_lu_code}' ";
	}



	$sql = " select count(distinct a.idx) as cnt {$sql_common} {$sql_search} {$sql_order} ";

	$row = sql_fetch($sql);
	$total_count = $row['cnt'];


	$rows = 50;
	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함'


	$limit = " limit {$from_record}, {$rows} ";

	$sql = "select a.*, b.lu_name, (select count(idx) from l_ad_list where 1=1 and tel = a.tel and ll_datetime < a.ll_datetime)+1 cnt, (select ll_datetime from l_ad_list where 1=1 and tel = a.tel and ll_datetime < a.ll_datetime order by ll_datetime desc limit 1) lll_datetime {$sql_common} {$sql_search} {$sql_order} {$limit}";

	$result = sql_query($sql);

?>

<div class="card card-default">
	<div class="card-body">
		<div class="col-12">
		<form id="" name="" autocomplete="off">
			<div class="row">
				<!--div class="col-md-2">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="sch_select" aria-hidden="true" autocomplete="off">
						<option selected="selected" value="">전체</option>
						<option value="a.mb_code" <?if($sch_select == "a.mb_code"){echo "selected";}?>>회원코드</option>
						<option value="a.mb_name" <?if($sch_select == "a.mb_name"){echo "selected";}?>>회원명</option>
						<option value="a.mb_hp" <?if($sch_select == "a.mb_hp"){echo "selected";}?>>연락처</option>
						<option value="a.mb_id" <?if($sch_select == "a.mb_id"){echo "selected";}?>>아이디</option>
					</select>
				</div-->
				<div class="col-md-8">
					<div class="row">
						<div class="col-md-2">
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">
										<i class="far fa-calendar-alt"></i>
									</span>
								</div>
								<input type="text" class="form-control float-right dateinput" name="start_date" value="<?=$start_date?>">
							</div>
						</div>
						<div class="col-md-2">
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">
										<i class="far fa-calendar-alt"></i>
									</span>
								</div>
								<input type="text" class="form-control float-right dateinput" name="end_date" value="<?=$end_date?>">
							</div>
						</div>
						<div class="col-md-2">
							<input type="text" class="form-control" name="sch_text" value="<?=$sch_text?>" placeholder="Enter ...">
						</div>
						<div class="col-md-3">
							<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="sch_lu_code" aria-hidden="true" autocomplete="off">
								<option selected="selected" value="">전체</option>
								<?
									$sql3 = "select * from l_ad_user where 1=1 and st_tp = '1' and del_yn = '0' order by lu_name asc";
									$result3 = sql_query($sql3);
									for($k=0; $row3 = sql_fetch_array($result3); $k++){
								?>
									<option <?if($sch_lu_code == $row3['lu_code']){?>selected="selected"<?}?> value="<?=$row3['lu_code']?>"><?=$row3['lu_name']?>[<?=$row3['lu_code']?>]</option>
								<?
									}
								?>
							</select>
						</div>
						<div class="col-md-3">
							<button class="btn btn-block btn-danger">검색</button>
						</div>
					</div>
				</div>
				<div class="col-md-2">
					<div class="row">
						<div class="col-md-6">
							<div class="input-group">
								<input type="text" class="form-control float-right" name="start_num" value="<?=$start_num?>" placeholder="시작번호">
							</div>
						</div>
						<div class="col-md-6">
							<div class="input-group">
								<input type="text" class="form-control float-right" name="end_num" value="<?=$end_num?>" placeholder="끝번호">
							</div>
						</div>
					</div>
				</div>
				<div class="col-md-1">
					<button class="btn btn-block btn-success" type="button" onClick="fnExcel()">엑셀다운</button>
				</div>
			</div>
		</div>
		</form>
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
					<th>매체사</th>
					<th>광고코드</th>
					<th>연락처</th>
					<th>이름</th>
					<th>정보제공동의</th>
					<th>신청일</th>
					<th>중복횟수</th>
					<th>최근중복일</th>
					<th>IP</th>
					<th>처리</th>
					<th>가입여부</th>
					<th>삭제</th>
				</tr>
				</thead>
				<tbody>
				<?for($i=0; $row = sql_fetch_array($result); $i++){?>
				<tr>
					<td><?=$row['idx']?></td>
					<td><?=$row['lu_name']?></td>
					<td><?=$row['lu_code']?></td>
					<td><?=$row['tel']?></td>
					<td><?=$row['name']?></td>
					<td><?=$row['etc1']?></td>
					<td><?=$row['ll_datetime']?></td>
					<td><?=$row['cnt'];?></td>
					<td><span style="color:red"><?=$row['lll_datetime'];?></span></td>
					<td><?=$row['ip']?></td>
					<td>
						<?if($row['del_yn'] == "0"){?>
						<button type="button" class="btn btn-block btn-danger" onClick="fnDelData('<?=$row['idx']?>')">처리</button>
						<?}else{?>
						처리완료
						<?}?>
					</td>
					<td><?=fnGetMemberYN($row['tel'])?></td>
					<td>
						<button type="button" class="btn btn-danger" onClick="fnProcDel('l_ad_list','idx','<?=$row['idx']?>')">삭제</button>
					</td>
				</tr>
				<?}?>
				<?if($total_count < 1){?>
				<tr>
					<td colspan="10">내역이 없습니다.</td>
				</tr>
				<?}?>
				</tbody>
				</table>
				<?php 
					$qstr .= "&sch_select={$sch_select}&sch_text={$sch_text}&sch_mb_type={$sch_mb_type}&start_date={$start_date}&end_date={$end_date}&start_num={$start_num}&end_num={$end_num}&sch_lu_code={$sch_lu_code}";
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
	location.href="./ad.data.list.excel.php?1=1<?=$qstr?>";
}

function fnDelData(idx){
	if(confirm("처리하시겠습니까?") == true){
		location.href="./ad.data.list.del.php?idx="+idx
	}
	/*var url = "./pop.new_ad.php?idx="+idx;
	var name = "new_member";
	var option = "width = 600, height = 600, top = 100, left = 200, location = no"
	window.open(url, name, option);*/
}


</script>
<?
	include_once(G5_LADMIN_PATH."/tail.php");
?>