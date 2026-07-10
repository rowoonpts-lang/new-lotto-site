<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.php");
	if(!$turn){
		$turn = getTurn();
	}

	$table = "l_turn_".$turn;


	$sql = "select * from {$table} where 1=1 and result not in ('','낙첨') ";

	$result = sql_query($sql);
	$aryMu = array();
	for($i=0; $row= sql_fetch_array($result); $i++){
		if($row[mb_type] == "무료회원"){
			$aryMu['무료'][$row[result]] = $aryMu['무료'][$row[result]]+1;
		}else{
			$aryMu['유료'][$row[result]] = $aryMu['유료'][$row[result]]+1;
		}
	}
	



	$sql_common = " from {$table} a, g5_member b ";
	$sql_search = " where 1=1 and a.mb_id = b.mb_id and result not in ('','낙첨') ";
	$sql_order = " order by mb_code desc ";

	if($sch_select){
		$sql_search .= " and {$sch_select} like '%{$sch_text}%' ";
	}else{
		$sql_search .= " and (b.mb_code like '%{$sch_text}%' or b.mb_name like '%{$sch_text}%' or a.mb_hp like '%{$sch_text}%' or a.mb_id like '%{$sch_text}%') ";
	}

	if($sch_mb_type){
		$sql_search .= " and a.mb_type = '{$sch_mb_type}' ";
	}

	if($lucky_result && $lucky_result != '전체'){
		$sql_search .= " and result = '{$lucky_result}' ";
	}

	$sql = " select count(distinct a.lt_id) as cnt {$sql_common} {$sql_search} {$sql_order} ";

	$row = sql_fetch($sql);
	$total_count = $row['cnt'];


	$rows = 50;
	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함'


	$limit = " limit {$from_record}, {$rows} ";

	$sql = "select *, a.mb_type as lucky_type, (select lp_pay_datetime from l_pay where 1=1 and mb_id = a.mb_id and lp_status = '입금' order by lp_pay_datetime desc limit 1) lp_pay_datetime {$sql_common} {$sql_search} {$sql_order} {$limit}";
	$result = sql_query($sql);
?>

<div class="card card-default">
	<div class="card-body">
		<div class="col-12">
		<form id="" name="" autocomplete="off">
			<div class="row">
				
				<div class="col-md-1">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="turn" aria-hidden="true">
						<?
							for($i=getTurn(); $i >= $config[cf_1]; $i--){
						?>
						<option value="<?=$i?>" <?if($turn == $i){echo "selected";}?>><?=$i?></option>
						<?	}?>
					</select>
				</div>
				<div class="col-md-2">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="sch_select" aria-hidden="true">
						<option selected="selected" value="">전체</option>
						<option value="b.mb_code" <?if($sch_select == "b.mb_code"){echo "selected";}?>>회원코드</option>
						<option value="b.mb_name" <?if($sch_select == "b.mb_name"){echo "selected";}?>>회원명</option>
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
				<div class="col-md-4" style="padding-top:7px">
					<?
						$resultAry = array('전체','낙첨','1등','2등','3등','4등','5등');
						for($i=0; $i < count($resultAry); $i++){
					?>
					<div class="icheck-primary d-inline">
						
						<input type="radio" id="radioPrimary<?=$i?>" name="lucky_result" <?if(($i==0 && !$lucky_result) || $resultAry[$i] == $lucky_result){?>checked=""<?}?> value="<?=$resultAry[$i]?>">
						<label for="radioPrimary<?=$i?>">
							<?=$resultAry[$i]?>
						</label>
						
					</div>
					<?	}?>
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
				<?
					for($i=0; $row = sql_fetch_array($result); $i++){
						$ball_text = "";
						$ball_text = $row[num1].",".$row[num2].",".$row[num3].",".$row[num4].",".$row[num5].",".$row[num6];				
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
						<?if($row['result']){echo $row['result'];}else{echo "-";}?>
					</td>
					<td><?=$row[lp_pay_datetime]?></td>
					<td><?=$row[lt_datetime]?></td>
					<td><?=$row['direct_yn']?></td>
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
					$qstr .= "&sch_select={$sch_select}&sch_text={$sch_text}&sch_mb_type={$sch_mb_type}&lucky_result={$lucky_result}&turn={$turn}";
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

<?
	include_once(G5_LADMIN_PATH."/tail.php");
?>