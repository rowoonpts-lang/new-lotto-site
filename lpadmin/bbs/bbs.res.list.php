<?php
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.php");

	$sch_select = '';
	$sch_mb_type = '';
	$sch_text = isset($_GET['sch_text']) ? trim((string) $_GET['sch_text']) : '';
	$start_date = isset($_GET['start_date']) ? trim((string) $_GET['start_date']) : '';
	$end_date = isset($_GET['end_date']) ? trim((string) $_GET['end_date']) : '';
	$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

	$start_date_sql = sql_real_escape_string($start_date);
	$end_date_sql = sql_real_escape_string($end_date);

	$rows = 50;
	$total_count = 0;
	$total_page = 0;
	$result = false;

	$table_result = sql_query("SHOW TABLES LIKE 'l_res'", false);
	$table_exists = $table_result && sql_num_rows($table_result) > 0;

	if ($table_exists) {
		$sql_common = " from l_res a ";
		$sql_search = " where a.lr_hp != '' and a.lr_type != '결제시도' ";
		$sql_order = " order by a.lr_datetime desc ";

		if ($start_date !== '') {
			$sql_search .= " and substr(a.lr_datetime,1,10)
				>= substr('{$start_date_sql}',1,10) ";
		}

		if ($end_date !== '') {
			$sql_search .= " and substr(a.lr_datetime,1,10)
				<= substr('{$end_date_sql}',1,10) ";
		}

		$sql = "select count(distinct a.lr_id) as cnt
				{$sql_common} {$sql_search}";

		$row = sql_fetch($sql, false);
		$total_count = isset($row['cnt']) ? (int) $row['cnt'] : 0;
		$total_page = (int) ceil($total_count / $rows);
		$from_record = ($page - 1) * $rows;
		$limit = " limit {$from_record}, {$rows} ";

		$sql = "select a.*,
					(select count(lr_id)
					   from l_res
					  where lr_hp = a.lr_hp
						and lr_datetime < a.lr_datetime) + 1 as cnt
				{$sql_common} {$sql_search} {$sql_order} {$limit}";

		$result = sql_query($sql, false);
	}

	$qstr = http_build_query(array(
		'sch_text' => $sch_text,
		'start_date' => $start_date,
		'end_date' => $end_date,
	));
?>

<div class="card card-default">
	<div class="card-body">
		<div class="col-12">
		<form id="" name="" autocomplete="off">
			<div class="row">
				<!--div class="col-md-2">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="sch_select" aria-hidden="true" autocomplete="off">
						<option selected="selected" value="">전체</option>
						<option value="a.mb_code" <?php if($sch_select == "a.mb_code"){echo "selected";}?>>회원코드</option>
						<option value="a.mb_name" <?php if($sch_select == "a.mb_name"){echo "selected";}?>>회원명</option>
						<option value="a.mb_hp" <?php if($sch_select == "a.mb_hp"){echo "selected";}?>>연락처</option>
						<option value="a.mb_id" <?php if($sch_select == "a.mb_id"){echo "selected";}?>>아이디</option>
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
					<th>연락처</th>
					<th>이름</th>
					<th>신청등급</th>
					<th>신청일</th>
					<th>중복횟수</th>
					<th>IP</th>
					<th>처리</th>
					<th>삭제</th>
				</tr>
				</thead>
				<tbody>
				<?php for($i=0; $result && ($row = sql_fetch_array($result)); $i++){?>
				<tr>
					<td><?=$total_count-($page-1)*$rows-$i?></td>
					<td><?=$row['lr_hp']?></td>
					<td><?=$row['lr_name']?></td>
					<td><?=$row['lr_type']?></td>
					<td><?=$row['lr_datetime']?></td>
					<td><?php echo $row['cnt'];?></td>
					<td><?=$row['ip']?></td>
					<td>
						<?php if($row['del_yn'] == "0"){?>
						<button type="button" class="btn btn-block btn-danger" onClick="fnDelData('<?=$row['lr_id']?>')">처리</button>
						<?php }else{?>
						처리완료
						<?php }?>
					</td>
					<td>
						<button type="button" class="btn btn-danger" onClick="fnProcDel('l_res','lr_id','<?=$row['lr_id']?>')">삭제</button>
					</td>
				</tr>
				<?php }?>
				<?php if($total_count < 1){?>
				<tr>
					<td colspan="10">내역이 없습니다.</td>
				</tr>
				<?php }?>
				</tbody>
				</table>
				<?php
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
	location.href="./bbs.res.list.excel.php?1=1<?=$qstr?>";
}

function fnDelData(lr_id){
	if(confirm("삭제하시겠습니까?") == true){
		location.href="./bbs.res.list.del.php?lr_id="+lr_id
	}
	/*var url = "./pop.new_ad.php?idx="+idx;
	var name = "new_member";
	var option = "width = 600, height = 600, top = 100, left = 200, location = no"
	window.open(url, name, option);*/
}


</script>
<?php
	include_once(G5_LADMIN_PATH."/tail.php");
?>