<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.php");
	$sql_common = " from l_ad_list_in a";
	$sql_search = " where 1=1  ";
	$sql_order = " order by a.ll_datetime desc ";
	
	if($start_date){
		$sql_search .= " and a.idx >= {$start_date} ";
	}
	if($end_date){
		$sql_search .= " and a.idx <= {$end_date} ";
	}
	

	$sql = " select count(distinct a.idx) as cnt {$sql_common} {$sql_search} {$sql_order} ";


	$row = sql_fetch($sql);
	$total_count = $row['cnt'];


	$rows = 50;
	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함'


	$limit = " limit {$from_record}, {$rows} ";

	$sql = "select a.*, (select count(idx) from l_ad_list_in where 1=1 and tel = a.tel and ll_datetime < a.ll_datetime)+1 cnt {$sql_common} {$sql_search} {$sql_order} {$limit}";

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
				<div class="col-md-3">
					<div class="row">
						<div class="col-md-9">
							<input type="text" class="form-control" name="sch_text" value="<?=$sch_text?>" placeholder="Enter ...">
						</div>
						<div class="col-md-3">
							<button class="btn btn-block btn-danger">검색</button>
						</div>
					</div>
				</div>
				<div class="col-md-3">
					<div class="row">
						<div class="col-md-6">
							<div class="input-group">
								<input type="text" class="form-control float-right" name="start_date" value="<?=$start_date?>" placeholder="시작번호">
							</div>
						</div>
						<div class="col-md-6">
							<div class="input-group">
								<input type="text" class="form-control float-right" name="end_date" value="<?=$end_date?>" placeholder="끝번호">
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
					<th>랜딩종류</th>
					<th>연락처</th>
					<th>이름</th>
					<th>신청일</th>
					<th>중복횟수</th>
					<th>IP</th>
					<th>처리</th>
					<th>삭제</th>
				</tr>
				</thead>
				<tbody>
				<?for($i=0; $row = sql_fetch_array($result); $i++){?>
				<tr>
					<td><?=$row['idx']?></td>
					<td><?=$row['lu_type']?></td>
					<td><?=$row['tel']?></td>
					<td><?=$row['name']?></td>
					<td><?=$row['ll_datetime']?></td>
					<td><?=$row['cnt'];?></td>
					<td><?=$row['ip']?></td>
					<td>
						<?if($row['del_yn'] == "0"){?>
						<button type="button" class="btn btn-block btn-danger" onClick="fnDelData('<?=$row['idx']?>')">처리</button>
						<?}else{?>
						처리완료
						<?}?>
					</td>
					<td>
						<button type="button" class="btn btn-danger" onClick="fnProcDel('l_ad_list_in','idx','<?=$row[idx]?>')">삭제</button>
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
	location.href="./bbs.res.db.excel.php?1=1<?=$qstr?>";
}

function fnDelData(idx){
	if(confirm("처리하시겠습니까?") == true){
		location.href="./bbs.res.db.del.php?idx="+idx
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