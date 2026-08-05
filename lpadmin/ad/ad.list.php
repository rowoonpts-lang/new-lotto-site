<?php
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.php");

	$allowed_sch_select = array('lu_id', 'lu_name', 'lu_code', 'lu_type');

	$sch_select = isset($_GET['sch_select'])
		&& in_array($_GET['sch_select'], $allowed_sch_select, true)
		? $_GET['sch_select']
		: '';

	$sch_text = isset($_GET['sch_text']) ? trim((string) $_GET['sch_text']) : '';
	$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
	$sch_text_sql = sql_real_escape_string($sch_text);

	$rows = 10;
	$total_count = 0;
	$total_page = 0;
	$result = false;

	$table_result = sql_query("SHOW TABLES LIKE 'l_ad_user'", false);
	$table_exists = $table_result && sql_num_rows($table_result) > 0;

	if ($table_exists) {
		$sql_common = " from l_ad_user ";
		$sql_search = " where del_yn = '0' ";
		$sql_order = " order by idx desc ";

		if ($sch_text !== '') {
			if ($sch_select !== '') {
				$sql_search .= " and {$sch_select}
					like '%{$sch_text_sql}%' ";
			} else {
				$sql_search .= " and (
					lu_id like '%{$sch_text_sql}%'
					or lu_name like '%{$sch_text_sql}%'
					or lu_code like '%{$sch_text_sql}%'
					or lu_type like '%{$sch_text_sql}%'
				) ";
			}
		}

		$sql = "select count(distinct idx) as cnt
				{$sql_common} {$sql_search}";

		$row = sql_fetch($sql, false);
		$total_count = isset($row['cnt']) ? (int) $row['cnt'] : 0;
		$total_page = (int) ceil($total_count / $rows);
		$from_record = ($page - 1) * $rows;
		$limit = " limit {$from_record}, {$rows} ";

		$sql = "select *
				{$sql_common} {$sql_search} {$sql_order} {$limit}";

		$result = sql_query($sql, false);
	}

	$qstr = http_build_query(array(
		'sch_select' => $sch_select,
		'sch_text' => $sch_text,
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
						<div class="col-md-6">
							<input type="text" class="form-control" name="sch_text" value="<?=$sch_text?>" placeholder="Enter ...">
						</div>
						<div class="col-md-3">
							<button class="btn btn-block btn-danger">검색</button>
						</div>
						<div class="col-md-3">
							<button type="button" class="btn btn-block btn-primary" onClick="fnAddMemmber()">매체추가</button>
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
					<th>매체사</th>
					<th>매체사 코드</th>
					<th>광고 코드</th>
					<th>광고 아이디</th>
					<th>광고 패스워드</th>
					<th>생성일</th>
					<th>관리자 접속 여부</th>
					<th>수정</th>
					<th>삭제</th>
					<th>연동문서 다운</th>
				</tr>
				</thead>
				<tbody>
				<?php for($i=0; $result && ($row = sql_fetch_array($result)); $i++){?>
				<tr>
					<td><?=$total_count-($page-1)*$rows-$i?></td>
					<td><?=$row['lu_name']?></td>
					<td><?=$row['lu_type']?></td>
					<td><?=$row['lu_code']?></td>
					<td><?=$row['lu_id']?></td>
					<td><?=$row['lu_pw']?></td>
					<td><?=$row['lu_datetime']?></td>
					<td>
						<?php if($row['st_tp'] == "1"){?>
						접속가능
						<?php }else{?>
						접속중지
						<?php }?>
					</td>
					<td><button type="button" class="btn btn-block btn-primary" onclick="fnModifyMemmber('<?=$row['idx']?>')">수정</button></td>
					<td><button type="button" class="btn btn-block btn-danger" onclick="fnDelMemmber('<?=$row['idx']?>')">삭제</button></td>
					<td><a href="<?=G5_URL?>/ad/doc.v1.php?idx=<?=Encrypt($row['idx'], 'able','able')?>" target="_blank" class="btn btn-block btn-success" >링크열기</a></td>
					<!--td><button type="button" class="btn btn-block btn-danger" onClick="fnMemmberInfo('<?=base64_encode($row[mb_id])?>')">정보수정</button></td-->
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
function fnAddMemmber(){
	var url = "./pop.new_ad.php";
	var name = "new_member";
	var option = "width = 600, height = 600, top = 100, left = 200, location = no"
	window.open(url, name, option);
}

function fnModifyMemmber(idx){
	var url = "./pop.new_ad.php?idx="+idx;
	var name = "new_member";
	var option = "width = 600, height = 600, top = 100, left = 200, location = no"
	window.open(url, name, option);
}

function fnDelMemmber(idx){
	if(confirm("삭제하시겠습니까?") == true){
		location.href="./pop.new_ad.del.php?idx="+idx
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