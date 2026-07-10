<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.php");
	$sql_common = " from g5_member a ";
	$sql_search = " where 1=1 and a.mb_id != 'admin' and mb_level >= 5";
	$sql_order = " order by mb_datetime desc ";
	
	if($sch_text){
		if($sch_select){
			$sql_search .= " and {$sch_select} like '%{$sch_text}%' ";
		}else{
			$sql_search .= " and (a.mb_code like '%{$sch_text}%' or a.mb_name like '%{$sch_text}%' or a.mb_hp like '%{$sch_text}%' or a.mb_id like '%{$sch_text}%') ";
		}	
	}else{

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
				<div class="col-md-2">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="sch_select" aria-hidden="true" autocomplete="off">
						<option selected="selected" value="">전체</option>
						<option value="a.mb_code" <?if($sch_select == "a.mb_code"){echo "selected";}?>>회원코드</option>
						<option value="a.mb_name" <?if($sch_select == "a.mb_name"){echo "selected";}?>>회원명</option>
						<option value="a.mb_hp" <?if($sch_select == "a.mb_hp"){echo "selected";}?>>연락처</option>
						<option value="a.mb_id" <?if($sch_select == "a.mb_id"){echo "selected";}?>>아이디</option>
					</select>
				</div>
				<div class="col-md-3">
					<div class="row">
						<div class="col-md-6">
							<input type="text" class="form-control" name="sch_text" value="<?=$sch_text?>" placeholder="Enter ...">
						</div>
						<div class="col-md-3">
							<button class="btn btn-block btn-danger">검색</button>
						</div>
						<div class="col-md-3">
							<button type="button" class="btn btn-block btn-primary" onClick="fnAddMemmber('')">직원등록</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		</form>
		<div class="col-12">
			<div class="row">
				<div class="col-md-12">
				
				<input type="text" class="form-control" name="cf_ip" id="cf_ip" value="<?=$config['cf_ip']?>" onChange="fnSaveIp(this.value)">
				<p>허용아이피를 | 단위로 입력</p>
				</div>
			</div>
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
					<th>이름</th>
					<th>연락처</th>
					<th>아이디</th>
					<th>패스워드</th>
					<th>팀</th>
					<th>권한</th>
					<th>접근페이지</th>
					<th>수정</th>
					<th>삭제</th>
				</tr>
				</thead>
				<tbody>
				<?for($i=0; $row = sql_fetch_array($result); $i++){?>
				<tr>
					<td><?=$total_count-($page-1)*$rows-$i?></td>
					<td><?=$row['mb_name']?></td>
					<td><?=$row['mb_hp']?></td>
					<td><?=$row['mb_id']?></td>
					<td><?=$row['emp_pw']?></td>
					<td><?=$row['mb_team']?></td>
					<td><?=getLevelText($row['mb_level'])?></td>
					<td><?=getLevelPage($row['mb_level'])?></td>
					<td><button type="button" class="btn btn-block btn-primary" onclick="fnAddMemmber('<?=base64_encode($row[mb_id])?>')">수정</button></td>
					<td><button type="button" class="btn btn-block btn-danger" onclick="fnMemberDel('<?=base64_encode($row[mb_id])?>')">삭제</button></td>
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
function fnSaveIp(v){
	$.ajax({
		type: "POST",
		url: "./ajax.saveIp.php",
		data: {v : v}, 
		cache: false,
		async: false,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {

		}
	});
	return false;

}

function fnAddMemmber(mb_id){
	var url = "./pop.emp.new_member.php?mb_id="+mb_id;
	var name = "new_member";
	var option = "width = 600, height = 800, top = 100, left = 200, location = no"
	window.open(url, name, option);
}

function fnMemmberInfo(mb_id){
	var url = "./pop.member_info.php?mb_id="+mb_id;
	var name = "member_info";
	var option = "width = 1200, height = 700, top = 100, left = 200, location = no"
	window.open(url, name, option);
}

function fnMemberDel(mb_id){
	if(confirm("회원을 삭제하시겠습니까?")==true){
		location.href="<?=G5_LADMIN_URL?>/member/member.del.php?mb_id="+mb_id;
	}
}

</script>
<?
	include_once(G5_LADMIN_PATH."/tail.php");
?>