<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.php");
	$spamList = fnGetSpan();
	$sql_common = " from g5_member a, g5_member_etc b ";
	$sql_search = " where 1=1 and a.mb_id = b.mb_id and a.mb_id != 'admin' and mb_level < 5 ";
	$sql_order = " order by mb_datetime desc ";
	
	if($sch_text){
		if($sch_select){
			if($sch_select == "a.mb_code"){
				$sql_search .= " and {$sch_select} = '{$sch_text}' ";
			}else{
				$sql_search .= " and {$sch_select} like '%{$sch_text}%' ";
			}
		}else{
			$sql_search .= " and (a.mb_code like '%{$sch_text}%' or a.mb_name like '%{$sch_text}%' or a.mb_hp like '%{$sch_text}%' or a.mb_id like '%{$sch_text}%') ";
		}	
	}else{
		$sql_search .= " and mb_id = '' ";
	}
	$sql = " select count(distinct a.mb_id) as cnt {$sql_common} {$sql_search} {$sql_order} ";

	$row = sql_fetch($sql);
	$total_count = $row['cnt'];


	$rows = 10;
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
		<form id="" name="" autocomplete="off">
			<div class="row">
				<div class="col-md-1">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="sch_select" aria-hidden="true" autocomplete="off">
						<option selected="selected" value="">전체</option>
						<option value="a.mb_code" <?if($sch_select == "a.mb_code"){echo "selected";}?>>회원코드</option>
						<option value="a.mb_name" <?if($sch_select == "a.mb_name"){echo "selected";}?>>회원명</option>
						<option value="a.mb_hp" <?if($sch_select == "a.mb_hp"){echo "selected";}?>>연락처</option>
						<option value="a.mb_id" <?if($sch_select == "a.mb_id"){echo "selected";}?>>아이디</option>
					</select>
				</div>
				<div class="col-md-4">
					<div class="row">
						<div class="col-md-6">
							<input type="text" class="form-control" name="sch_text" value="<?=$sch_text?>" placeholder="Enter ...">
						</div>
						<div class="col-md-3">
							<button class="btn btn-block btn-danger">검색</button>
						</div>
						<div class="col-md-3">
							<button type="button" class="btn btn-block btn-primary" onClick="fnAddMemmber()">대리가입</button>
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
					<th>NO</th>
					<th>회원코드</th>
					<th>회원명/연락처</th>
					<th>아이디</th>
					<th>등급</th>
					<th>남은기간</th>
					<th>요일/조합</th>
					<th>가입일/최근접속일</th>
					<th>약관동의</th>
					<th>디비경로</th>
					<th>상태</th>
					<th>상세상담</th>
					<!--th>정보변경</th-->
				</tr>
				</thead>
				<tbody>
				<?for($i=0; $row = sql_fetch_array($result); $i++){?>
				<tr>
					<td><?=$total_count-($page-1)*$rows-$i?></td>
					<td><?=$row['mb_code']?></td>
					<td>
						<?=$row['mb_name']?><br>
						<?=$row['mb_hp']?>
						<?if (in_array($row['mb_hp'], $spamList)) {?>
						<br>
						<span style="color:red">[080스팸]</span>
						<?}?>
					</td>
					<td><?=$row['mb_id']?></td>
					<td>
						<?
							if($row['left_day'] > 0){
								echo "일시정지";
							}else{
								echo $row['mb_type'];
							}
						?>
					</td>
					<td>
						<?
							if(intval((strtotime($row[end_date]) - strtotime(date("Y-m-d"))) / 86400) > 0){
								echo intval((strtotime($row[end_date]) - strtotime(date("Y-m-d"))) / 86400);
							}else{
								echo "0";
							}
						?>일
					</td>
					<td>
						<?
							$tot_num = 0;
							$tot_text = "";
							$tot_num = $row['num_mon']+$row['num_tue']+$row['num_wed']+$row['num_thur']+$row['num_fri']+$row['num_sat'];
							$totAry = array('num_mon','num_tue','num_wed','num_thur','num_fri','num_sat');
							$totAryKor = array('월','화','수','목','금','토');
							for($k=0; $k < count($totAry); $k++){
								if($row[$totAry[$k]] > 0){
									if($tot_text){$tot_text.= " / ";}
									$tot_text.= $totAryKor[$k]." : ".$row[$totAry[$k]];
								}
							}
							
						?>
						남은조합 : <?=($tot_num-$row[use_num])?><br>
						<?=$tot_text?>
					</td>
					<td><?=$row[mb_datetime]?><br><?=$row[mb_today_login]?></td>
					<td>
						<?
							if(!$row[mb_yak]){
								echo "N";
							}else{
								echo "<span style='color:blue'>".$row['mb_yak']."</span>";
							}
						?>
					</td>
					<td><?=str_replace("homepage","home",$row[mb_db])?></td>
					<td>
						<?if($row[recent_select]){?>
							<?=$row[recent_select]?>
						<?}?>
					</td>
					<td><button type="button" class="btn btn-block btn-primary" onclick="fnMemmberMemo('<?=base64_encode($row[mb_id])?>')">상세상담</button></td>
				</tr>
				<?}?>
				<?if($total_count < 1){?>
				<tr>
					<td colspan="13">내역이 없습니다.</td>
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
function fnAddMemmber(){
	var url = "./pop.new_member.php";
	var name = "new_member";
	var option = "width = 600, height = 600, top = 100, left = 200, location = no"
	window.open(url, name, option);
}

function fnMemmberInfo(mb_id){
	var url = "./pop.member_info.php?mb_id="+mb_id;
	var name = "member_info";
	var option = "width = 1200, height = 700, top = 100, left = 200, location = no"
	window.open(url, name, option);
}

function fnMemmberMemo(mb_id){
	var url = "./pop.memo.php?mb_id="+mb_id;
	var name = "memo";
	var option = "width = 1400, height = 700, top = 100, left = 200, location = no"
	window.open(url, name, option);
}
</script>
<?
	include_once(G5_LADMIN_PATH."/tail.php");
?>