<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.php");

	$sql_common = " from l_lucky_custom ";
	$sql_search = " where 1=1 ";
	$sql_order = " order by lc_datetime desc ";

	$sql = " select count(distinct lc_id) as cnt {$sql_common} {$sql_search} {$sql_order} ";

	$row = sql_fetch($sql);
	$total_count = $row['cnt'];


	$rows = 50;
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
		<form id="frm" name="frm" action="lucky.custom.save.php" method="post" autocomplete="off" onSubmit="return fnSave()">
			<div class="row">
				<div class="col-md-1">
					<input type="text" class="form-control" name="turn" placeholder="회차" required>
				</div>
				<div class="col-md-1">
					<input type="text" class="form-control" name="num1" placeholder="1번호" required>
				</div>
				<div class="col-md-1">
					<input type="text" class="form-control" name="num2" placeholder="2번호" required>
				</div>
				<div class="col-md-1">
					<input type="text" class="form-control" name="num3" placeholder="3번호" required>
				</div>
				<div class="col-md-1">
					<input type="text" class="form-control" name="num4" placeholder="4번호" required>
				</div>
				<div class="col-md-1">
					<input type="text" class="form-control" name="num5" placeholder="5번호" required>
				</div>
				<div class="col-md-1">
					<input type="text" class="form-control" name="num6" placeholder="6번호" required>
				</div>
				<div class="col-md-1">
					<input type="text" class="form-control" name="num7" placeholder="보너스번호" required>
				</div>
				<div class="col-md-1">
					<button class="btn btn-block btn-primary">저장</button>
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
				<h3 class="card-title">번호 등록 시 자동으로 당첨결과가 반영됩니다.</h3>
			</div>
			<!-- /.card-header -->
			<div class="card-body table-responsive p-0">
				<table class="table table-hover text-nowrap">
				<thead>
				<tr>
					<th>NO</th>
					<th>회차</th>
					<th>당첨번호1</th>
					<th>당첨번호2</th>
					<th>당첨번호3</th>
					<th>당첨번호4</th>
					<th>당첨번호5</th>
					<th>당첨번호6</th>
					<th>보너스번호</th>
					<th>생성일</th>
					<th>삭제</th>
				</tr>
				</thead>
				<tbody>
				<?
					$tturn = "";
					for($i=0; $row = sql_fetch_array($result); $i++){
						$ball_text = "";
						$ball_text = $row[num1].",".$row[num2].",".$row[num3].",".$row[num4].",".$row[num5].",".$row[num6];				
						$tturn = $row['turn'];
				?>
				<tr>
					<td><?=$total_count-($page-1)*$rows-$i?></td>
					<td><?=$row['turn']?></td>
					<td><?=getBall($row[num1])?></td>
					<td><?=getBall($row[num2])?></td>
					<td><?=getBall($row[num3])?></td>
					<td><?=getBall($row[num4])?></td>
					<td><?=getBall($row[num5])?></td>
					<td><?=getBall($row[num6])?></td>
					<td><?=getBall($row[num7])?></td>
					<td><?=$row[lc_datetime]?></td>
					<td><button class="btn btn-block btn-danger" type="button" onClick="fnProcDel('l_lucky_custom', 'lc_id', '<?=$row[lc_id]?>')">삭제</button></td>
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
					$qstr .= "&sch_select={$sch_select}&num1={$num1}&sch_mb_type={$sch_mb_type}&lucky_result={$lucky_result}&turn={$turn}";
					echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); 
				?>
				<!-- 배고 -->
			<!-- /.card-body -->
		</div>
		<!-- /.card -->
	</div>
</div>
<script>
function fnSave(){
	return true;
}


</script>

<?
	include_once(G5_LADMIN_PATH."/tail.php");
?>