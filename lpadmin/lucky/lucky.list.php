<?php
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.php");

	$allowed_sch_select = array(
		'b.mb_code',
		'b.mb_name',
		'a.mb_hp',
		'a.mb_id',
	);

	$current_turn = getTurn();
	$turn = isset($_GET['turn']) ? max(0, (int) $_GET['turn']) : $current_turn;

	$sch_select = isset($_GET['sch_select'])
		&& in_array($_GET['sch_select'], $allowed_sch_select, true)
		? $_GET['sch_select']
		: '';

	$sch_text = isset($_GET['sch_text']) ? trim((string) $_GET['sch_text']) : '';
	$sch_mb_type = isset($_GET['sch_mb_type']) ? trim((string) $_GET['sch_mb_type']) : '';
	$lucky_result = isset($_GET['lucky_result']) ? trim((string) $_GET['lucky_result']) : '';
	$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

	$sch_text_sql = sql_real_escape_string($sch_text);
	$sch_mb_type_sql = sql_real_escape_string($sch_mb_type);
	$lucky_result_sql = sql_real_escape_string($lucky_result);

	$rows = 50;
	$total_count = 0;
	$total_page = 0;
	$result = false;

	$aryMu = array(
		'무료' => array('1등' => 0, '2등' => 0, '3등' => 0, '4등' => 0, '5등' => 0),
		'유료' => array('1등' => 0, '2등' => 0, '3등' => 0, '4등' => 0, '5등' => 0),
	);

	$table = $turn > 0 ? 'l_turn_'.$turn : '';
	$table_exists = false;

	if ($table !== '') {
		$table_sql = sql_real_escape_string($table);
		$table_result = sql_query("SHOW TABLES LIKE '{$table_sql}'", false);
		$table_exists = $table_result && sql_num_rows($table_result) > 0;
	}

	if ($table_exists) {
		$sql = "select mb_type, result
				  from `{$table}`
				 where result not in ('', '낙첨')";

		$summary_result = sql_query($sql, false);

		while ($summary_result && ($summary_row = sql_fetch_array($summary_result))) {
			$group = $summary_row['mb_type'] === '무료회원' ? '무료' : '유료';
			$result_name = $summary_row['result'];

			if (isset($aryMu[$group][$result_name])) {
				$aryMu[$group][$result_name]++;
			}
		}

		$sql_common = " from `{$table}` a, g5_member b ";
		$sql_search = " where a.mb_id = b.mb_id
			and a.result not in ('', '낙첨') ";
		$sql_order = " order by b.mb_code desc ";

		if ($sch_text !== '') {
			if ($sch_select !== '') {
				$sql_search .= " and {$sch_select}
					like '%{$sch_text_sql}%' ";
			} else {
				$sql_search .= " and (
					b.mb_code like '%{$sch_text_sql}%'
					or b.mb_name like '%{$sch_text_sql}%'
					or a.mb_hp like '%{$sch_text_sql}%'
					or a.mb_id like '%{$sch_text_sql}%'
				) ";
			}
		}

		if ($sch_mb_type !== '') {
			$sql_search .= " and a.mb_type = '{$sch_mb_type_sql}' ";
		}

		if ($lucky_result !== '' && $lucky_result !== '전체') {
			$sql_search .= " and a.result = '{$lucky_result_sql}' ";
		}

		$sql = "select count(distinct a.lt_id) as cnt
				{$sql_common} {$sql_search}";

		$row = sql_fetch($sql, false);
		$total_count = isset($row['cnt']) ? (int) $row['cnt'] : 0;
		$total_page = (int) ceil($total_count / $rows);
		$from_record = ($page - 1) * $rows;
		$limit = " limit {$from_record}, {$rows} ";

		$pay_result = sql_query("SHOW TABLES LIKE 'l_pay'", false);
		$pay_exists = $pay_result && sql_num_rows($pay_result) > 0;

		$pay_column = $pay_exists
			? "(select lp_pay_datetime
				  from l_pay
				 where mb_id = a.mb_id
				   and lp_status = '입금'
				 order by lp_pay_datetime desc
				 limit 1) as lp_pay_datetime"
			: "NULL as lp_pay_datetime";

		$sql = "select a.*, b.mb_code, b.mb_name,
					a.mb_type as lucky_type,
					{$pay_column}
				{$sql_common} {$sql_search}
				{$sql_order} {$limit}";

		$result = sql_query($sql, false);
	}

	$qstr = http_build_query(array(
		'sch_select' => $sch_select,
		'sch_text' => $sch_text,
		'sch_mb_type' => $sch_mb_type,
		'lucky_result' => $lucky_result,
		'turn' => $turn,
	));
?>

<div class="card card-default">
	<div class="card-body">
		<div class="col-12">
		<form id="" name="" autocomplete="off">
			<div class="row">

				<div class="col-md-1">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="turn" aria-hidden="true">
						<?php
							for($i=$current_turn; $i > 0 && $i >= (int)($config['cf_1'] ?? 0); $i--){
						?>
						<option value="<?=$i?>" <?php if($turn == $i){echo "selected";}?>><?=$i?></option>
						<?php 	}?>
					</select>
				</div>
				<div class="col-md-2">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="sch_select" aria-hidden="true">
						<option selected="selected" value="">전체</option>
						<option value="b.mb_code" <?php if($sch_select == "b.mb_code"){echo "selected";}?>>회원코드</option>
						<option value="b.mb_name" <?php if($sch_select == "b.mb_name"){echo "selected";}?>>회원명</option>
						<option value="a.mb_hp" <?php if($sch_select == "a.mb_hp"){echo "selected";}?>>연락처</option>
						<option value="a.mb_id" <?php if($sch_select == "a.mb_id"){echo "selected";}?>>아이디</option>
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
						<?php
						$mb_type_ary = fnGetType();
						for($i=0; $i < count($mb_type_ary); $i++){
						?>
						<option value="<?=$mb_type_ary[$i]?>" <?php if($sch_mb_type == $mb_type_ary[$i]){echo "selected";}?>><?=$mb_type_ary[$i]?></option>
						<?php
						}
						?>
					</select>
				</div>
				<div class="col-md-4" style="padding-top:7px">
					<?php
						$resultAry = array('전체','낙첨','1등','2등','3등','4등','5등');
						for($i=0; $i < count($resultAry); $i++){
					?>
					<div class="icheck-primary d-inline">

						<input type="radio" id="radioPrimary<?=$i?>" name="lucky_result" <?php if(($i==0 && !$lucky_result) || $resultAry[$i] == $lucky_result){?>checked=""<?php }?> value="<?=$resultAry[$i]?>">
						<label for="radioPrimary<?=$i?>">
							<?=$resultAry[$i]?>
						</label>

					</div>
					<?php 	}?>
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
				<!--h3 class="card-title"><?=$page?>/<?=$total_page?></h3-->
				<h3 class="card-title">
					<p>무료번호 (1등 : <?=number_format($aryMu['무료']['1등'])?> / 2등 : <?=number_format($aryMu['무료']['2등'])?> / 3등 : <?=number_format($aryMu['무료']['3등'])?> / 4등 : <?=number_format($aryMu['무료']['4등'])?> / 5등 : <?=number_format($aryMu['무료']['5등'])?>)</p>
					<p>유료번호 (1등 : <?=number_format($aryMu['유료']['1등'])?> / 2등 : <?=number_format($aryMu['유료']['2등'])?> / 3등 : <?=number_format($aryMu['유료']['3등'])?> / 4등 : <?=number_format($aryMu['유료']['4등'])?> / 5등 : <?=number_format($aryMu['유료']['5등'])?>)</p>
				</h3>
			</div>
			<!-- /.card-header -->
			<div class="card-body table-responsive p-0">
				<table class="table table-hover text-nowrap">
				<thead>
				<tr>
					<th>NO</th>
					<th>회차</th>
					<th>회원코드</th>
					<th>이름</th>
					<th>등급</th>
					<th>번호</th>
					<th>당첨결과</th>
					<th>결제일자</th>
					<th>번호발송시간</th>
					<th>수동발송여부</th>
				</tr>
				</thead>
				<tbody>
				<?php
					for($i=0; $result && ($row = sql_fetch_array($result)); $i++){
						$ball_text = "";
						$ball_text = $row['num1'].",".$row['num2'].",".$row['num3'].",".$row['num4'].",".$row['num5'].",".$row['num6'];
				?>
				<tr>
					<td><?=$total_count-($page-1)*$rows-$i?></td>
					<td><?=$row['turn']?></td>
					<td><?=$row['mb_code']?></td>
					<td>
						<?=$row['mb_name']?><br>
						<?=$row['mb_hp']?>
					</td>

					<td><?=$row['lucky_type']?></td>
					<td>
						<?=getBall($ball_text)?>
					</td>
					<td>
						<?php if($row['result']){echo $row['result'];}else{echo "-";}?>
					</td>
					<td><?=$row['lp_pay_datetime']?></td>
					<td><?=$row['lt_datetime']?></td>
					<td><?=$row['direct_yn']?></td>
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
	location.href="./lucky.list.excel.php?1=1<?=$qstr?>";
}
</script>

<?php
	include_once(G5_LADMIN_PATH."/tail.php");
?>