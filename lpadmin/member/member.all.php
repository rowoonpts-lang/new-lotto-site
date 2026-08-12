<?php
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.php");

	$allowed_sch_select = array(
		'a.mb_code',
		'a.mb_name',
		'a.mb_hp',
		'a.mb_id'
	);

	$sch_select = isset($_GET['sch_select']) && in_array($_GET['sch_select'], $allowed_sch_select, true)
		? $_GET['sch_select']
		: '';
	$sch_text = isset($_GET['sch_text']) ? trim((string) $_GET['sch_text']) : '';
	$sch_mb_type = isset($_GET['sch_mb_type']) ? trim((string) $_GET['sch_mb_type']) : '';
	$sch_mb_db = isset($_GET['sch_mb_db']) ? trim((string) $_GET['sch_mb_db']) : '';
	$sch_mb_status = isset($_GET['sch_mb_status']) ? trim((string) $_GET['sch_mb_status']) : '';
	$sch_staff_mb_id = isset($_GET['sch_staff_mb_id']) ? trim((string) $_GET['sch_staff_mb_id']) : '';
	$start_date = isset($_GET['start_date']) ? trim((string) $_GET['start_date']) : '';
	$end_date = isset($_GET['end_date']) ? trim((string) $_GET['end_date']) : '';
	$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
	$qstr = isset($qstr) ? (string) $qstr : '';

	$sch_text_sql = sql_real_escape_string($sch_text);
	$sch_mb_type_sql = sql_real_escape_string($sch_mb_type);
	$sch_mb_db_sql = sql_real_escape_string($sch_mb_db);
	$sch_mb_status_sql = sql_real_escape_string($sch_mb_status);
	$sch_staff_mb_id_sql = sql_real_escape_string($sch_staff_mb_id);
	$start_date_sql = sql_real_escape_string($start_date);
	$end_date_sql = sql_real_escape_string($end_date);
	
	$spamList = fnGetSpan();

	$login_mb_id = isset($member['mb_id'])
		? trim((string) $member['mb_id'])
		: '';

	$login_level = isset($member['mb_level'])
		? (int) $member['mb_level']
		: 0;

	$can_view_all = lottoCanViewAllMembers($login_level);

	$sql_common = "
		from g5_member a
		inner join g5_member_etc b
			on b.mb_id = a.mb_id
		left join l_member_assignment c
			on c.mb_id = a.mb_id
		left join g5_member d
			on d.mb_id = c.staff_mb_id
	";

	$sql_search = "
		where 1=1
		  and a.mb_id != 'admin'
		  and a.mb_level < 5
	";

	if (!$can_view_all) {
		$staff_ids = array($login_mb_id);

		if (
			$login_level === LOTTO_ROLE_STAFF2
			|| $login_level === LOTTO_ROLE_TEAM_LEADER
		) {
			$child_staff_ids =
				lottoGetDirectChildStaffIds($login_mb_id);

			foreach ($child_staff_ids as $child_staff_id) {
				$staff_ids[] = $child_staff_id;
			}
		}

		$staff_ids = array_values(
			array_unique(
				array_filter($staff_ids)
			)
		);

		$staff_ids_sql = array();

		foreach ($staff_ids as $staff_id) {
			$staff_ids_sql[] =
				"'" . sql_real_escape_string($staff_id) . "'";
		}

		if (count($staff_ids_sql) > 0) {
			$sql_search .= "
				and c.staff_mb_id in (
					" . implode(',', $staff_ids_sql) . "
				)
			";
		} else {
			$sql_search .= " and 1 = 0 ";
		}
	}

	$sql_order = " order by a.mb_datetime desc ";

	if($sch_select){
		if($sch_select == "a.mb_code"){
			$sql_search .= " and {$sch_select} = '{$sch_text_sql}' ";
		}else{
			$sql_search .= " and {$sch_select} like '%{$sch_text_sql}%' ";
		}
	}else{
		$sql_search .= " and (a.mb_code like '%{$sch_text_sql}%' or a.mb_name like '%{$sch_text_sql}%' or a.mb_hp like '%{$sch_text_sql}%' or a.mb_id like '%{$sch_text_sql}%') ";
	}

	if($sch_mb_type){
		if($sch_mb_type == "일시정지"){
			$sql_search .= " and b.left_day > 0 ";
		}else{
			$sql_search .= " and a.mb_type = '{$sch_mb_type_sql}' and b.left_day < 1 ";
		}
	}

	if($sch_staff_mb_id){
		$sql_search .= " and c.staff_mb_id = '{$sch_staff_mb_id_sql}' ";
	}

	if($sch_mb_db){
		$sql_search .= " and b.mb_db = '{$sch_mb_db_sql}' ";
	}

	if($start_date){
		$sql_search .= " and substr(a.mb_datetime,1,10) >= substr('{$start_date_sql}',1,10) ";
	}
	if($end_date){
		$sql_search .= " and substr(a.mb_datetime,1,10) <= substr('{$end_date_sql}',1,10) ";
	}
	if($sch_mb_status){
		$sql_search .= " and b.recent_select = '{$sch_mb_status_sql}' ";
	}

	

	$sql = " select count(distinct a.mb_id) as cnt {$sql_common} {$sql_search} {$sql_order} ";

	$row = sql_fetch($sql);
	$total_count = isset($row['cnt']) ? (int) $row['cnt'] : 0;


	$rows = 30;
	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함'


	$limit = " limit {$from_record}, {$rows} ";

	$sql = "select
			a.*,
			b.*,
			c.staff_mb_id,
			d.mb_name as staff_name
		{$sql_common}
		{$sql_search}
		{$sql_order}
		{$limit}";
	$result = sql_query($sql);

	$assignment_staff_rows = array();

	if ($can_view_all) {
		$assignment_staff_result = sql_query(
			"select mb_id, mb_name, mb_level
			   from g5_member
			  where mb_level in (
				".LOTTO_ROLE_STAFF1.",
				".LOTTO_ROLE_STAFF2.",
				".LOTTO_ROLE_TEAM_LEADER."
			  )
			  order by mb_level desc, mb_name asc, mb_id asc",
			false
		);

		while ($assignment_staff_row = sql_fetch_array($assignment_staff_result)) {
			$assignment_staff_rows[] = $assignment_staff_row;
		}
	}

?>

<div class="card card-default">
	<div class="card-body">
		<div class="col-12">
		<form id="" name="" autocomplete="off">
			<div class="row">
				<div class="col-md-1">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="sch_select" aria-hidden="true" autocomplete="off">
						<option selected="selected" value="">전체</option>
						<option value="a.mb_code" <?php if($sch_select == "a.mb_code"){echo "selected";}?>>회원코드</option>
						<option value="a.mb_name" <?php if($sch_select == "a.mb_name"){echo "selected";}?>>회원명</option>
						<option value="a.mb_hp" <?php if($sch_select == "a.mb_hp"){echo "selected";}?>>연락처</option>
						<option value="a.mb_id" <?php if($sch_select == "a.mb_id"){echo "selected";}?>>아이디</option>
					</select>
				</div>
				<div class="col-md-2">
					<input type="text" class="form-control" name="sch_text" value="<?=$sch_text?>" placeholder="Enter ...">
				</div>
				<div class="col-md-2">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="sch_mb_type" aria-hidden="true">
						<option selected="selected" value="">등급전체</option>
						<option value="일시정지" <?php if($sch_mb_type == "일시정지"){echo "selected";}?>>일시정지</option>
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
				<div class="col-md-2">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="sch_staff_mb_id" aria-hidden="true">
						<option value="">담당자전체</option>
						<?php
						$staff_filter_ids = array();

						if ($can_view_all) {
							$staff_filter_result = sql_query(
								"select mb_id, mb_name, mb_level
								   from g5_member
								  where mb_level in (
									".LOTTO_ROLE_STAFF1.",
									".LOTTO_ROLE_STAFF2.",
									".LOTTO_ROLE_TEAM_LEADER."
								  )
								  order by mb_level desc, mb_name asc, mb_id asc",
								false
							);

							while ($staff_filter_row = sql_fetch_array($staff_filter_result)) {
								$staff_filter_ids[] = (string) $staff_filter_row['mb_id'];
						?>
						<option value="<?=htmlspecialchars((string) $staff_filter_row['mb_id'], ENT_QUOTES)?>" <?php if($sch_staff_mb_id === (string) $staff_filter_row['mb_id']){echo "selected";}?>>
							<?=htmlspecialchars((string) $staff_filter_row['mb_name'], ENT_QUOTES)?>
						</option>
						<?php
							}
						} else {
							foreach ($staff_ids as $staff_filter_id) {
								$staff_filter_id_sql = sql_real_escape_string($staff_filter_id);
								$staff_filter_row = sql_fetch(
									"select mb_id, mb_name, mb_level
									   from g5_member
									  where mb_id = '{$staff_filter_id_sql}'
									    and mb_level >= ".LOTTO_ROLE_STAFF1."
									  limit 1",
									false
								);

								if (empty($staff_filter_row['mb_id'])) {
									continue;
								}
						?>
						<option value="<?=htmlspecialchars((string) $staff_filter_row['mb_id'], ENT_QUOTES)?>" <?php if($sch_staff_mb_id === (string) $staff_filter_row['mb_id']){echo "selected";}?>>
							<?=htmlspecialchars((string) $staff_filter_row['mb_name'], ENT_QUOTES)?>
						</option>
						<?php
							}
						}
						?>
					</select>
				</div>
				<div class="col-md-1">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="sch_mb_db" aria-hidden="true">
						<option selected="selected" value="">DB경로</option>
						<?php
						$sql_db = "select distinct mb_db from g5_member_etc where 1=1 and mb_db not in ('','기타','통화중') order by mb_db asc";
						$result_db = sql_query($sql_db);
						for($i=0; $row_db = sql_fetch_array($result_db); $i++){
						?>
						<option value="<?=$row_db['mb_db']?>" <?php if($sch_mb_db == $row_db['mb_db']){echo "selected";}?>><?=$row_db['mb_db']?></option>
						<?php
						}
						?>
					</select>
				</div>
				<div class="col-md-1">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="sch_mb_status" aria-hidden="true">
						<option selected="selected" value="">DB상태</option>
						<?php
							$memoList = fnGetMemoStatus();
							for($k=0; $k < count($memoList); $k++){
						?>
						<option value="<?=$memoList[$k]?>" <?php if($sch_mb_status == $memoList[$k]){echo "selected";}?>><?=$memoList[$k]?></option>
						<?php 	}?>
					</select>
				</div>
				<div class="col-md-3">
					<div class="row">
						<div class="col-md-4">
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">
										<i class="far fa-calendar-alt"></i>
									</span>
								</div>
								<input type="text" class="form-control float-right dateinput" name="start_date" value="<?=$start_date?>">
							</div>
						</div>
						<div class="col-md-4">
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">
										<i class="far fa-calendar-alt"></i>
									</span>
								</div>
								<input type="text" class="form-control float-right dateinput" name="end_date" value="<?=$end_date?>">
							</div>
						</div>
						<div class="col-md-2">
							<button class="btn btn-block btn-danger">검색</button>
						</div>
						<div class="col-md-2">
							<button class="btn btn-block btn-primary" type="button" onClick="fnAddMember()">회원등록</button>
<?php if ((int) $member['mb_level'] >= LOTTO_ROLE_ADMIN) { ?>
<button class="btn btn-block btn-success" type="button" onClick="fnExcel()">엑셀다운</button>
<?php } ?>
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
			<div class="row">
				<div class="col-10">
				</div>
				<?php if ((int) $member['mb_level'] >= LOTTO_ROLE_ADMIN) { ?>
				<div class="col-2">
					<button type="button" class="btn btn-block btn-danger" onClick="fnMemberChkDel()">선택삭제</button>
				</div>
				<?php } ?>
			</div>
			<div class="card-header">
				<h3 class="card-title"></h3>
			</div>
			<!-- /.card-header -->
			<div class="card-body table-responsive p-0">
				<form name="frm_member" id="frm_member">
				<table class="table table-hover text-nowrap">
				<thead>
				<tr>
					<th><input type="checkbox" id="checkall"></th>
					<th>NO</th>
					<th>회원코드</th>
					<th>회원명/연락처</th>
					<th>아이디</th>
					<th>등급</th>
					<th>담당자</th>
					<th>남은기간</th>
					<th>요일/조합</th>
					<th>가입일/최근접속일</th>
					<th>약관동의</th>
					<th>디비경로</th>
					<th>상태</th>
					<th>상세상담</th>
					<th>탈퇴/삭제</th>
					<!--th>정보변경</th-->
				</tr>
				</thead>
				<tbody>
				<?php for($i=0; $row = sql_fetch_array($result); $i++){?>
				<tr>
					<td><input type="checkbox" name="chk[]" value="<?=$row['mb_id']?>"></td>
					<td><?=$total_count-($page-1)*$rows-$i?></td>
					<td><?=$row['mb_code']?></td>
					<td>
						<?=$row['mb_name']?><br>
						<?=$row['mb_hp']?>
						<?php if (in_array($row['mb_hp'], $spamList)) {?>
						<br>
						<span style="color:red">[080스팸]</span>
						<?php }?>
					</td>
					<td><?=$row['mb_id']?></td>
					<td>
						<?php
							if($row['left_day'] > 0){
								echo "일시정지";
							}else{
								echo $row['mb_type'];
							}
						?>
					</td>
					<td>
						<?php
						$current_staff_mb_id = isset($row['staff_mb_id'])
							? trim((string) $row['staff_mb_id'])
							: '';

						$staff_name = isset($row['staff_name'])
							? trim((string) $row['staff_name'])
							: '';
						?>

						<?php if ($can_view_all) { ?>
						<select
							class="form-control form-control-sm member-staff-select"
							data-mb-id="<?=htmlspecialchars((string) $row['mb_id'], ENT_QUOTES)?>"
							data-original-value="<?=htmlspecialchars($current_staff_mb_id, ENT_QUOTES)?>"
						>
							<option value="">미배정</option>
							<?php foreach ($assignment_staff_rows as $assignment_staff_row) { ?>
							<option
								value="<?=htmlspecialchars((string) $assignment_staff_row['mb_id'], ENT_QUOTES)?>"
								<?php if ($current_staff_mb_id === (string) $assignment_staff_row['mb_id']) { echo 'selected'; } ?>
							>
								<?=htmlspecialchars((string) $assignment_staff_row['mb_name'], ENT_QUOTES)?>
							</option>
							<?php } ?>
						</select>
						<?php } else { ?>
						<?=($staff_name !== ''
							? htmlspecialchars($staff_name, ENT_QUOTES)
							: '-')?>
						<?php } ?>
					</td>
					<td>
						<?php
							if($row['left_day'] < 1){
								if(intval((strtotime($row['end_date']) - strtotime(date("Y-m-d"))) / 86400) > 0){
									echo intval((strtotime($row['end_date']) - strtotime(date("Y-m-d"))) / 86400);
								}else{
									echo "0";
								}
						}else{
							echo $row['left_day'];							
						}
						?>일
					</td>
					<td>
						<?php
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
						남은조합 : <?=($tot_num-$row['use_num'])?><br>
						<?=$tot_text?>
					</td>
					<td><?=$row['mb_datetime']?><br><?=$row['mb_today_login']?></td>
					<td>
						<?php
							if(!$row['mb_yak']){
								echo "N";
							}else{
								echo "<span style='color:blue'>".$row['mb_yak']."</span>";
							}
						?>
					</td>
					<td><?=str_replace("homepage","home",$row['mb_db'])?></td>
					<td>
						<?php if($row['recent_select']){?>
							<?=$row['recent_select']?>
						<?php }?>
					</td>
					<td><button type="button" class="btn btn-block btn-primary" onclick="fnMemmberMemo('<?=base64_encode($row['mb_id'])?>')">상세상담</button></td>
					<td>
						<?php if ((int) $member['mb_level'] >= LOTTO_ROLE_ADMIN) { ?>
						<button type="button" class="btn btn-block btn-danger" onClick="fnMemberDel('<?=base64_encode($row['mb_id'])?>')">삭제</button>
						<?php } ?>
					</td>
				</tr>
				<?php }?>
				<?php if($total_count < 1){?>
				<tr>
					<td colspan="13">내역이 없습니다.</td>
				</tr>
				<?php }?>
				</tbody>
				</table>
				</form>
				<?php
					$qstr .= "&sch_select=".urlencode($sch_select)
				."&sch_text=".urlencode($sch_text)
				."&sch_mb_type=".urlencode($sch_mb_type)
				."&sch_staff_mb_id=".urlencode($sch_staff_mb_id)
				."&start_date=".urlencode($start_date)
				."&end_date=".urlencode($end_date)
				."&sch_mb_status=".urlencode($sch_mb_status)
				."&sch_mb_db=".urlencode($sch_mb_db);
					echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); 
				?>
			</div>
			<!-- /.card-body -->
		</div>
		<!-- /.card -->
	</div>
	
</div>

<script>
$(document).on('change', '.member-staff-select', function(){
	var $select = $(this);
	var mbId = $select.data('mb-id');
	var staffMbId = $select.val();
	var originalValue = $select.attr('data-original-value');

	$select.prop('disabled', true);

	$.ajax({
		type: 'POST',
		url: './ajax.member.assignment.update.php',
		data: {
			mb_id: mbId,
			staff_mb_id: staffMbId
		},
		dataType: 'json',
		success: function(response) {
			if (!response || response.success !== true) {
				alert(
					response && response.message
						? response.message
						: '담당자 변경에 실패했습니다.'
				);

				$select.val(originalValue);
				return;
			}

			$select.attr(
				'data-original-value',
				response.staff_mb_id || ''
			);
		},
		error: function() {
			alert('담당자 변경 중 오류가 발생했습니다.');
			$select.val(originalValue);
		},
		complete: function() {
			$select.prop('disabled', false);
		}
	});
});

function fnMemberChkDel(){
	if(confirm("선택하신 회원을 삭제하시겠습니까?")==true){
		var string = $("form[name=frm_member]").serialize();

		$.ajax({
			type: "POST",
			url: "./member.alldel.php",
			data: string, 
			cache: false,
			async: false,
			contentType : "application/x-www-form-urlencoded; charset=UTF-8",
			success: function(data) {
				location.reload();
			}
		});
		return false;
	}

}

$(document).ready(function(){
    //최상단 체크박스 클릭
    $("#checkall").click(function(){
        //클릭되었으면
        if($("#checkall").prop("checked")){
            //input태그의 name이 chk인 태그들을 찾아서 checked옵션을 true로 정의
            $("input[name='chk[]']").prop("checked",true);
            //클릭이 안되있으면
        }else{
            //input태그의 name이 chk인 태그들을 찾아서 checked옵션을 false로 정의
            $("input[name='chk[]']").prop("checked",false);
        }
    })
})

function fnAddMember(){
	var url = "./pop.new_member.php";
	var name = "new_member";
	var option = "width=500,height=650,top=100,left=200,location=no";

	window.open(url, name, option);
}

function fnExcel(){
	location.href="./member.all.excel.php?1=1<?=$qstr?>";
}


function fnMemberDel(mb_id){
	if(confirm("회원을 삭제하시겠습니까?")==true){
		location.href="./member.del.php?mb_id="+mb_id;
	}
}
</script>
<?php
	include_once(G5_LADMIN_PATH."/tail.php");
?>