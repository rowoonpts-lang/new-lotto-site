<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.php");

	$year = date("Y");
	$month = str_pad(date("m"), 2, "0", STR_PAD_LEFT);

	if($sch_year){
		$year = $sch_year;
	}
	if($sch_year){
		$month = str_pad($sch_month, 2, "0", STR_PAD_LEFT);
	}
	
	
	$sql = "select * from l_memo where 1=1 and substr(lm_datetime,1,7) = '{$year}-{$month}'";
	$result = sql_query($sql);
	$memoAry = array();
	for($i=0; $row= sql_fetch_array($result); $i++){
		$memoAry[substr($row[lm_datetime],0,10)][$row[from_mb_id]] = $memoAry[substr($row[lm_datetime],0,10)][$row[from_mb_id]]+1;
	}
?>

<div class="card card-default">
	<div class="card-body">
		<div class="col-12">
		<form id="" name="" autocomplete="off">
			<div class="row">
				<div class="col-md-1">
					<select name="sch_year" class="form-control select2 select2-hidden-accessible">
						<?for($i=2019; $i< 2030; $i++){?>
						<option value="<?=$i?>" <?if($i == $year){?>selected<?}?>><?=$i?></option>
						<?}?>
					</select>
				</div>
				<div class="col-md-1">
					<select name="sch_month" class="form-control select2 select2-hidden-accessible">
						<?for($i=1; $i<= 12; $i++){?>
						<option value="<?=str_pad($i, 2, "0", STR_PAD_LEFT)?>" <?if(str_pad($i, 2, "0", STR_PAD_LEFT) == $month){?>selected<?}?>><?=$i?></option>
						<?}?>
					</select>
				</div>
				<div class="col-md-3">
					<div class="row">
						<div class="col-md-3">
							<button class="btn btn-block btn-danger">검색</button>
						</div>
					</div>
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
					<th class="center"></th>
					<?
						
						$startDate = $year."-".$month."-01";
						$endDate = date("Y-m-d", mktime(0, 0, 0, $month+1 , 0, $year)); 
						$endDayEpx = explode("-",$endDate);
						//$start_day = date("")
						for($i=1; $i <= $endDayEpx[2]; $i++){
					?>
					<th class="center"><?=$i?></th>
					<?}?>
					<th class="center">합계</th>
				</tr>
				</thead>
				<tbody>
						<?
							$where = " and mb_level >=4 ";
							$order = " order by mb_datetime desc ";

							$sql = "select * 
									from g5_member 
									where 1=1
										{$where}
										{$order}
										{$limit}
									";
							$result = sql_query($sql);
							
							$cnt = 0;
							for($i=0; $row = sql_fetch_array($result); $i++){
						?>
						<tr>
							<td style="width:100px;"><?=$row[mb_name]?><?=$row[mb_team]?></td>
							<?
								for($j=1; $j <= $endDayEpx[2]; $j++){
								$toDayTmp = str_pad($j, 2, "0", STR_PAD_LEFT);
								$totmem[$row[mb_id]] += $memoAry[$year."-".$month."-".$toDayTmp][$row[mb_id]];
							?>
							<td>
								<?=$memoAry[$year."-".$month."-".$toDayTmp][$row[mb_id]]?>
							</td>
							<?	}?>
							<td>
								<?=$totmem[$row[mb_id]]?>
							</td>
						</tr>
						<?	}?>
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

<?
	include_once(G5_LADMIN_PATH."/tail.php");
?>