<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.php");
	

	/*$sql_common = " from msg_cust_log a left join g5_member b on (replace(a.phone_no,'-','') = replace(b.mb_hp,'-','')) ";
	$sql_search = " where 1=1  ";
	$sql_order = " order by send_time desc ";
	

	$sql = " select count(distinct idx) as cnt {$sql_common} {$sql_search} {$sql_order} ";

	$row = sql_fetch($sql);
	$total_count = $row['cnt'];


	$rows = 30;
	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함'


	$limit = " limit {$from_record}, {$rows} ";

	$sql = "select * {$sql_common} {$sql_search} {$sql_order} {$limit}";
	$result = sql_query($sql);*/

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
				<!--div class="col-md-2">
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
				</div-->
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
			</div>
		</form>
		</div>
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
					<th>회원명/연락처(등급)</th>
					<th>회신번호</th>
					<th style="width:30%">내용</th>
					<th>전송일시</th>
					<th>전송타입</th>
					<th>처리여부</th>
					<th>재전송</th>
				</tr>
				</thead>
				<tbody>
				<?for($i=0; $row = sql_fetch_array($result); $i++){?>
				<tr>
					<td><?=$total_count-($page-1)*$rows-$i?></td>
					<td>
						<?=$row['mb_code']?>
					</td>
					<td><?=$row['mb_name']?><br><?=$row['mb_hp']?><br><?=$row['mb_type']?></td>
					<td><?=$row['phone_no']?></td>
					<td style="text-align:left"><?=nl2br($row['message'])?></td>
					<td><?=$row['send_time']?></td>
					<td><?=$row['msg_type']?></td>
					<td>
						<?
							$rt = getSmsResult($row[table_name], $row[msg_id]);
							if($rt == "0"){
								echo "성공";
							}else if($rt == ""){
								echo "전송중";
							}else{
								echo "실패";
							}
						?>
					</td>
					<td><button type="button" class="btn btn-block btn-primary" onclick="fnSmsReSend('<?=$row[idx]?>')">재전송</button></td>
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
function fnSmsReSend(idx){
	if(confirm("재전송 하시겠습니까?") == true){
		$.ajax({
			type: "POST",
			url: "<?=G5_LADMIN_URL?>/member/ajax.smsReSend.php",
			data: {idx : idx}, 
			cache: false,
			async: false,
			contentType : "application/x-www-form-urlencoded; charset=UTF-8",
			success: function(data) {
				alert("정상적으로 처리되었습니다.");
				location.reload();
			}
		});
		return false;

	}
}
</script>
<?
	include_once(G5_LADMIN_PATH."/tail.php");
?>