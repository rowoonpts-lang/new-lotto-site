<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.php");
?>



<div class="row">
	<div class="col-12">
		<div class="card">
			<?include_once(G5_LADMIN_PATH."/member/inc.calendar.php");?>
		</div>
		<!-- /.card -->

		<?if($rowDate && $mb_id){?>
		<div class="card">
			<div class="card-body table-responsive p-0">
				<h3 class="card-title" style="padding:10px;font-weight:600;text-align:center;width:100%"><?=$rowDate?> <?=$member_info[$mb_id]?></h3>
				<table class="table table-striped mb-none">
				<thead>
				<tr>
					<th class="center">상담일자</th>
					<th class="center">회원이름</th>
					<th class="center">아이디</th>
					<th class="center">회원코드</th>
					<th class="center">알림종류</th>
					<th class="center" width="500px;">내용</th>
					<th class="center">알림시간</th>
					<th class="center">담당자</th>
					<th class="center">확인처리</th>
				</tr>
				</thead>
				<tbody>
				<?
					$add_query2 = "";

					$where = " and st_tp = '1' and lm_alarm_date != '0000-00-00 00:00:00' and a.mb_id = b.mb_id and lm_alarm_view = '0' ";
					$order = " order by lm_alarm_date ASC";
					//$where .= " and from_mb_id = '$mb_id' AND lm_alarm_type = '유력' ";
					if($rowDate){
						$where .= " and substr(lm_alarm_date,1,10) = '{$rowDate}' ";
					}

					if($mb_id){
						$add_query2 = " and from_mb_id = '{$mb_id}' ".$where;
					}
										

					$where .= " and from_mb_id = '$mb_id' AND lm_alarm_type = '유력' ";
					$sql = "select * 
							from l_memo a, g5_member b
							where 1=1
								{$where}
								{$add_query2}
								{$order}
							";
					$result = sql_query($sql);

					$cnt = 0;
					//echo $sql;

					if(date("Y-m-d") == $rowDate){
						$rowDate = "";
					}
					$cnt=0;;
					for($i=0; $row = sql_fetch_array($result); $i++){
						$cnt++;
				?>
				<?include("./inc.alarmList.php");?>
				<?}	

					$sql = "select * 
							from l_memo a, g5_member b
							where 1=1
								{$add_query2} AND lm_alarm_type != '유력'
								{$order}
							";
					$result = sql_query($sql);

					//echo $sql;

					if(date("Y-m-d") == $rowDate){
						$rowDate = "";
					}
					
					for($i=0; $row = sql_fetch_array($result); $i++){
						$cnt++;
				?>
				<?include("./inc.alarmList.php");?>
				<?}?>
				<?if($cnt < 1){?>
				<tr>
					<td colspan="11" style="text-align:center;">
						알람이 없습니다.
					</td>
				</tr>
				<?}?>
				</tbody>
				</table>
			
			</div>
		</div>
		<?}?>
	</div>
</div>
<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-body table-responsive p-0">
				<h3 class="card-title" style="padding:10px;font-weight:600;text-align:center;width:100%">당일 알람</h3>
				<table class="table table-hover text-nowrap">
				<thead>
				<tr>
					<th class="center">상담일자</th>
					<th class="center">회원이름</th>
					<th class="center">아이디</th>
					<th class="center">회원코드</th>
					<th class="center">알림종류</th>
					<th class="center" width="500px;">내용</th>
					<th class="center">알림시간</th>
					<th class="center">담당자</th>
					<th class="center">확인처리</th>
				</tr>
				</thead>
				<tbody>
				<?
					$add_query2 = "";
					if($mb_id){
						$add_query2 = " and from_mb_id = '{$mb_id}' ";
					}

					$where = " and st_tp = '1' and lm_alarm_view = '0' and lm_alarm_date != '0000-00-00 00:00:00' and a.mb_id = b.mb_id";
					$order = " order by lm_alarm_date asc";

					// 관리자도 다보이게 할때
					/*if($member[mb_level] < 10){
						$where .= " and mm_wr_mb_id = '$member[mb_id]' ";
					}*/
					if($is_admin && !$mb_id){
						//$where .= " and from_mb_id = '$member[mb_id]' ";
					}else{
						$where .= " and from_mb_id = '$member[mb_id]' ";
					}

					if($etc_1){
						$where .= " and etc_1 = '{$etc_1}' ";
					}
					
					/*if($rowDate){
						$where .= " and substr(lm_alarm_date,1,10) = '{$rowDate}' ";
					}else{
						$where .= " and substr(lm_alarm_date,1,10) = substr(now(),1,10) ";
					}*/
					$where .= " and substr(lm_alarm_date,1,10) = substr(now(),1,10) ";

					$sql = "select * 
							from l_memo a, g5_member b
							where 1=1
								{$where}
								{$add_query2}
								{$order}
								{$limit}
							";
					$result = sql_query($sql);

					$cnt = 0;
					//echo $sql;

					if(date("Y-m-d") == $rowDate){
						$rowDate = "";
					}
					
					for($i=0; $row = sql_fetch_array($result); $i++){
						$cnt++;
				?>
				<?include("./inc.alarmList.php");?>
				<?}?>
				<?if($cnt < 1){?>
				<tr>
					<td colspan="11" style="text-align:center;">
						알람이 없습니다.
					</td>
				</tr>
				<?}?>
				</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-body table-responsive p-0">
				<h3 class="card-title" style="padding:10px;font-weight:600;text-align:center;width:100%">지난 알람</h3>
				<table class="table table-striped mb-none">
				<thead>
				<tr>
					<th class="center">상담일자</th>
					<th class="center">회원이름</th>
					<th class="center">아이디</th>
					<th class="center">회원코드</th>
					<th class="center">알림종류</th>
					<th class="center" width="500px;">내용</th>
					<th class="center">알림시간</th>
					<th class="center">담당자</th>
					<th class="center">확인처리</th>
				</tr>
				</thead>
				<tbody>
				<?
					$add_query2 = "";
					if($mb_id){
						$add_query2 = " and from_mb_id = '{$mb_id}' ";
					}

					//$where = " and st_tp = '1' ";
					$where = " and lm_alarm_date != '0000-00-00 00:00:00' and a.mb_id = b.mb_id ";
					$order = " order by lm_alarm_date desc";
					/*관리자도 다보이게*/
					/*if($member[mb_level] < 10){
						$where .= " and mm_wr_mb_id = '$member[mb_id]' ";
					}*/
					if($is_admin){
						if($mb_id){
							$where .= " and from_mb_id = '$mb_id' ";
						}
					}else{
						$where .= " and from_mb_id = '$member[mb_id]' ";
					}

					if($rowDate){
						//$where .= " and substr(mm_alarmtime,1,10) = '{$rowDate}' ";
						$where .= " and (lm_alarm_date <= now() or lm_alarm_view) ";
					}else{
						$where .= " and (lm_alarm_date <= now() or lm_alarm_view) ";
					}



					$sql_common = " from l_memo  a, g5_member b  "; 
					$sql = " select count(distinct lm_id) as cnt {$sql_common} where 1=1  {$where} {$add_query2} {$order} "; 
					$row = sql_fetch($sql); 
					$total_count = $row['cnt']; 
					

					$rows = 15; 
					$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산 
					if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지) 
					$from_record = ($page - 1) * $rows; // 시작 열을 구함' 
					
					$limit = " limit {$from_record}, {$rows} "; 

					$sql = "select * 
							{$sql_common}
							where 1=1
								{$where}
								{$add_query2}
								{$order}
								{$limit}
							";
					$result = sql_query($sql);
					$cnt = 0;
					//echo $sql;

					if(date("Y-m-d") == $rowDate){
						$rowDate = "";
					}

					for($i=0; $row = sql_fetch_array($result); $i++){
						$cnt++;
				?>
				<?if($row[etc_1] == "유력"){
					$colordata = "#e3ffea";
				}
				else{
					$colordata = "";
				}?>
				<?include("./inc.alarmList.php");?>
				<?}?>
				<?if($cnt < 1){?>
				<tr>
					<td colspan="11" style="text-align:center;">
						알람이 없습니다.
					</td>
				</tr>
				<?}?>
				</tbody>
				</table>

				<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?> 
			</div>
		</div>
	</div>
</div>

<script>
function fnCheckAlarm(lm_id, mb_id){
	$.ajax({
		type: "POST",
		url: "./ajax.checkAlarm.php",
		data: {lm_id : lm_id}, 
		cache: false,
		async: false,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {
			//alert("정상적으로 처리되었습니다.");
			fnMemmberMemo(mb_id);
			location.reload();
		}
	});
}

</script>
<?
	include_once(G5_LADMIN_PATH."/tail.php");
?>