<?php
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.sub.php");

	$mb_id2 = isset($_GET['mb_id']) ? trim((string) $_GET['mb_id']) : '';
	$mb_id = $mb_id2 !== '' ? base64_decode($mb_id2, true) : '';

	if ($mb_id === false || $mb_id === '') {
		echo '<script>alert("회원정보가 올바르지 않습니다.");window.close();</script>';
		exit;
	}

	$mb_id_sql = sql_real_escape_string($mb_id);

	$sql = "select a.*, b.*
			from g5_member a
			left join g5_member_etc b on b.mb_id = a.mb_id
			where a.mb_id = '{$mb_id_sql}'
			limit 1";
	$row = sql_fetch($sql);

	if (empty($row['mb_id'])) {
		echo '<script>alert("회원정보를 찾을 수 없습니다.");window.close();</script>';
		exit;
	}

	$assignment = sql_fetch(
		"select c.staff_mb_id, d.mb_name as staff_name
		   from l_member_assignment c
		   left join g5_member d on d.mb_id = c.staff_mb_id
		  where c.mb_id = '{$mb_id_sql}'
		  limit 1",
		false
	);

	$login_mb_id = isset($member['mb_id']) ? trim((string) $member['mb_id']) : '';
	$login_level = isset($member['mb_level']) ? (int) $member['mb_level'] : 0;
	$can_view_all = lottoCanViewAllMembers($login_level);

	if (!$can_view_all) {
		$allowed_staff_ids = array($login_mb_id);

		if ($login_level === LOTTO_ROLE_STAFF2 || $login_level === LOTTO_ROLE_TEAM_LEADER) {
			$child_staff_ids = lottoGetDirectChildStaffIds($login_mb_id);
			foreach ($child_staff_ids as $child_staff_id) {
				$allowed_staff_ids[] = (string) $child_staff_id;
			}
		}

		$allowed_staff_ids = array_values(array_unique(array_filter($allowed_staff_ids)));
		$assigned_staff_mb_id = isset($assignment['staff_mb_id']) ? trim((string) $assignment['staff_mb_id']) : '';

		if ($assigned_staff_mb_id === '' || !in_array($assigned_staff_mb_id, $allowed_staff_ids, true)) {
			echo '<script>alert("접근 권한이 없는 회원입니다.");window.close();</script>';
			exit;
		}
	}

	$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
	$rows = 10;

	$sql_common = " from l_memo a, g5_member b ";
	$sql_search = " where a.mb_id = b.mb_id and a.mb_id = '{$mb_id_sql}' ";
	$sql_order = " order by a.lm_datetime desc ";

	$count_row = sql_fetch("select count(distinct a.lm_id) as cnt {$sql_common} {$sql_search}");
	$total_count = isset($count_row['cnt']) ? (int) $count_row['cnt'] : 0;
	$total_page = $total_count > 0 ? (int) ceil($total_count / $rows) : 1;
	if ($page > $total_page) {
		$page = $total_page;
	}
	$from_record = ($page - 1) * $rows;

	$history_result = sql_query(
		"select a.*, b.mb_type
		   {$sql_common}
		   {$sql_search}
		   {$sql_order}
		   limit {$from_record}, {$rows}"
	);

	$staff_name = isset($assignment['staff_name']) && trim((string) $assignment['staff_name']) !== ''
		? trim((string) $assignment['staff_name'])
		: '-';

	$start_date = isset($row['start_date']) ? (string) $row['start_date'] : '';
	$end_date = isset($row['end_date']) ? (string) $row['end_date'] : '';
	$start_date_text = ($start_date !== '' && $start_date !== '0000-00-00') ? $start_date : '-';
	$end_date_text = ($end_date !== '' && $end_date !== '0000-00-00') ? $end_date : '-';
?>

<link rel="stylesheet" href="//mugifly.github.io/jquery-simple-datetimepicker/jquery.simple-dtpicker.css">
<script src="//mugifly.github.io/jquery-simple-datetimepicker/jquery.simple-dtpicker.js"></script>
<script>
$(function(){
	$('.datetimepicker').appendDtpicker({
		'locale': 'ko',
		'minuteInterval': 10,
		'autodateOnStart': false,
		'minTime': '09:00'
	});
});
</script>

<section class="content">
	<div class="container-fluid">
		<div class="card card-primary card-outline">
			<div class="card-body pb-2">
				<?php include_once("./member.head.php"); ?>
			</div>
		</div>

		<div class="card card-info card-outline">
			<div class="card-header">
				<h3 class="card-title">회원 기본정보</h3>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-3 col-sm-6 mb-2">
						<strong>회원명</strong>
						<div><?=htmlspecialchars((string) $row['mb_name'], ENT_QUOTES)?></div>
					</div>
					<div class="col-md-3 col-sm-6 mb-2">
						<strong>휴대폰</strong>
						<div><?=htmlspecialchars((string) $row['mb_hp'], ENT_QUOTES)?></div>
					</div>
					<div class="col-md-3 col-sm-6 mb-2">
						<strong>아이디</strong>
						<div><?=htmlspecialchars((string) $row['mb_id'], ENT_QUOTES)?></div>
					</div>
					<div class="col-md-3 col-sm-6 mb-2">
						<strong>회원코드</strong>
						<div><?=htmlspecialchars((string) ($row['mb_code'] ?? ''), ENT_QUOTES)?></div>
					</div>
				</div>
				<hr>
				<div class="row">
					<div class="col-md-3 col-sm-6 mb-2">
						<strong>등급</strong>
						<div><?=htmlspecialchars((string) ($row['mb_type'] ?? ''), ENT_QUOTES)?></div>
					</div>
					<div class="col-md-3 col-sm-6 mb-2">
						<strong>담당자</strong>
						<div><?=htmlspecialchars($staff_name, ENT_QUOTES)?></div>
					</div>
					<div class="col-md-3 col-sm-6 mb-2">
						<strong>이용기간 시작</strong>
						<div><?=htmlspecialchars($start_date_text, ENT_QUOTES)?></div>
					</div>
					<div class="col-md-3 col-sm-6 mb-2">
						<strong>이용기간 종료</strong>
						<div><?=htmlspecialchars($end_date_text, ENT_QUOTES)?></div>
					</div>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-lg-5">
				<div class="card card-primary">
					<div class="card-header">
						<h3 class="card-title">상담 등록</h3>
					</div>
					<form name="frm_member_memo" id="frm_member_memo" method="post" action="pop.member.memo.update.php" autocomplete="off">
						<input type="hidden" name="mb_id" value="<?=htmlspecialchars((string) $row['mb_id'], ENT_QUOTES)?>">
						<div class="card-body">
							<div class="form-group">
								<label for="recent_select">메모선택</label>
								<select id="recent_select" name="recent_select" class="form-control">
									<option value="">메모선택</option>
									<?php
									$memo_list = fnGetMemoStatus();
									for ($i = 0; $i < count($memo_list); $i++) {
									?>
									<option value="<?=htmlspecialchars((string) $memo_list[$i], ENT_QUOTES)?>"><?=htmlspecialchars((string) $memo_list[$i], ENT_QUOTES)?></option>
									<?php } ?>
								</select>
							</div>

							<div class="form-group">
								<label for="recent_memo">상담내용</label>
								<textarea class="form-control" id="recent_memo" name="recent_memo" rows="6"></textarea>
							</div>

							<div class="form-group">
								<label for="mb_hyunkm">현금영수증</label>
								<input type="text" class="form-control" id="mb_hyunkm" value="<?=htmlspecialchars((string) ($row['mb_hyunkm'] ?? ''), ENT_QUOTES)?>" onChange="fnChgHy(this.value)">
							</div>

							<div class="form-group mb-0">
								<label>알림</label>
								<div class="row">
									<div class="col-md-5 mb-2 mb-md-0">
										<select id="alarm_select" name="alarm_select" class="form-control">
											<option value="">선택</option>
											<option value="유력">유력</option>
											<option value="단순">단순</option>
											<option value="미수">미수</option>
											<option value="부재">부재</option>
										</select>
									</div>
									<div class="col-md-7">
										<input type="text" name="alarm_date" id="alarm_date" class="form-control datetimepicker" placeholder="알림 일시">
									</div>
								</div>
							</div>
						</div>
						<div class="card-footer text-right">
							<button type="submit" class="btn btn-primary">상담 저장</button>
						</div>
					</form>
				</div>
			</div>

			<div class="col-lg-7">
				<div class="card card-secondary">
					<div class="card-header">
						<h3 class="card-title">상담이력</h3>
					</div>
					<div class="card-body table-responsive p-0">
						<table class="table table-hover text-nowrap text-sm mb-0">
							<thead>
							<tr>
								<th>일자</th>
								<th>담당</th>
								<th>상태</th>
								<th style="width:45%">내용</th>
								<th>알림시간</th>
								<?php if ($login_level >= LOTTO_ROLE_ADMIN) { ?>
								<th>삭제</th>
								<?php } ?>
							</tr>
							</thead>
							<tbody>
							<?php
							$history_count = 0;
							while ($history = sql_fetch_array($history_result)) {
								$history_count++;
								$from_mb_id = isset($history['from_mb_id']) ? (string) $history['from_mb_id'] : '';
								$writer_name = isset($member_info[$from_mb_id]) ? (string) $member_info[$from_mb_id] : $from_mb_id;
							?>
							<tr>
								<td><?=htmlspecialchars((string) $history['lm_datetime'], ENT_QUOTES)?></td>
								<td><?=htmlspecialchars($writer_name, ENT_QUOTES)?></td>
								<td><?=htmlspecialchars((string) $history['lm_memo_type'], ENT_QUOTES)?></td>
								<td class="text-wrap"><?=nl2br(htmlspecialchars((string) $history['lm_memo'], ENT_QUOTES))?></td>
								<td>
									<?php if ((string) $history['lm_alarm_view'] === '0') { ?>
									<?=htmlspecialchars(str_replace('0000-00-00 00:00:00', '', (string) $history['lm_alarm_date']), ENT_QUOTES)?>
									<?php } ?>
								</td>
								<?php if ($login_level >= LOTTO_ROLE_ADMIN) { ?>
								<td>
									<button type="button" class="btn btn-sm btn-danger" onClick="fnProcDel('l_memo', 'lm_id', '<?=htmlspecialchars((string) $history['lm_id'], ENT_QUOTES)?>')">삭제</button>
								</td>
								<?php } ?>
							</tr>
							<?php } ?>
							<?php if ($history_count < 1) { ?>
							<tr>
								<td colspan="6" class="text-center">등록된 상담내역이 없습니다.</td>
							</tr>
							<?php } ?>
							</tbody>
						</table>
					</div>
					<?php if ($total_count > $rows) { ?>
					<div class="card-footer">
						<?php
						$qstr_member = 'mb_id='.urlencode($mb_id2);
						echo get_paging(
							$config['cf_write_pages'],
							$page,
							$total_page,
							'?'.$qstr_member.'&amp;page='
						);
						?>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
function fnChgHy(v){
	$.ajax({
		type: 'POST',
		url: './ajax.pop.memo.hk.php',
		data: {
			mb_id: '<?=htmlspecialchars((string) $row['mb_id'], ENT_QUOTES)?>',
			v: v
		},
		cache: false,
		contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
		success: function(){
			alert('현금영수증 저장됨');
		},
		error: function(){
			alert('현금영수증 저장 중 오류가 발생했습니다.');
		}
	});
}
</script>
