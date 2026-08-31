<?php
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.sub.php");

	$mb_id2 = isset($_GET['mb_id']) ? trim((string) $_GET['mb_id']) : '';
	$mb_id = $mb_id2 !== '' ? base64_decode($mb_id2, true) : '';
	$alarm_lm_id = isset($_GET['alarm_lm_id']) ? (int) $_GET['alarm_lm_id'] : 0;

        $refresh_parent = isset($_GET['refresh_parent'])
                && (string) $_GET['refresh_parent'] === '1';


        $member_token = lottoMemberTokenCreate();

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
		$allowed_staff_ids = lottoGetAccessibleStaffIds(
    $login_mb_id,
    $login_level
);
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
	$start_date_value = ($start_date !== '' && $start_date !== '0000-00-00') ? $start_date : '';
	$end_date_value = ($end_date !== '' && $end_date !== '0000-00-00') ? $end_date : '';
?>

<link rel="stylesheet" href="//mugifly.github.io/jquery-simple-datetimepicker/jquery.simple-dtpicker.css">
<script src="//mugifly.github.io/jquery-simple-datetimepicker/jquery.simple-dtpicker.js"></script>
<script>
$(function(){
<?php if ($refresh_parent) { ?>
        if (window.opener && !window.opener.closed) {
                window.opener.location.reload();
        }

        if (window.history && window.history.replaceState) {
                var cleanUrl = new URL(window.location.href);
                cleanUrl.searchParams.delete('refresh_parent');
                window.history.replaceState(
                        {},
                        document.title,
                        cleanUrl.pathname + cleanUrl.search + cleanUrl.hash
                );
        }
<?php } ?>

	$('.datetimepicker').appendDtpicker({
		'locale': 'ko',
		'minuteInterval': 10,
		'autodateOnStart': false,
		'minTime': '09:00'
	});
});
</script>

<section class="content member-detail-page">
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
				<div class="row align-items-end">
					<div class="col-md-3 col-sm-6 mb-2">
						<strong>등급</strong>
						<div><?=htmlspecialchars((string) ($row['mb_type'] ?? ''), ENT_QUOTES)?></div>
					</div>
					<div class="col-md-3 col-sm-6 mb-2">
						<strong>담당자</strong>
						<div><?=htmlspecialchars($staff_name, ENT_QUOTES)?></div>
					</div>
					<div class="col-md-6 col-sm-12 mb-2">
						<strong>유료기간</strong>
						<form method="post" action="pop.member.period.update.php" class="mt-1" autocomplete="off" onsubmit="return validatePaidPeriod(this);">
							<input type="hidden" name="mb_id" value="<?=htmlspecialchars((string) $row['mb_id'], ENT_QUOTES)?>">
							<div class="d-flex align-items-center">
								<input type="date" name="start_date" class="form-control form-control-sm mr-1" value="<?=htmlspecialchars($start_date_value, ENT_QUOTES)?>" aria-label="유료기간 시작일">
								<span class="mr-1">~</span>
								<input type="date" name="end_date" class="form-control form-control-sm mr-1" value="<?=htmlspecialchars($end_date_value, ENT_QUOTES)?>" aria-label="유료기간 종료일">
								<button type="submit" class="btn btn-primary btn-sm text-nowrap">기간 저장</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>


		<div class="card card-success card-outline">
			<div class="card-header">
				<h3 class="card-title">회원 정보수정</h3>
			</div>

			<form method="post"
				  action="pop.member.profile.update.php"
				  autocomplete="off">
				<input type="hidden"
					   name="mb_id"
					   value="<?=htmlspecialchars((string) $row['mb_id'], ENT_QUOTES)?>">

				<div class="card-body">
					<div class="row">
						<div class="col-md-3 mb-3">
							<label for="profile_mb_name">이름</label>
							<input type="text"
								   class="form-control"
								   id="profile_mb_name"
								   name="mb_name"
								   value="<?=htmlspecialchars((string) $row['mb_name'], ENT_QUOTES)?>"
								   required>
						</div>

						<div class="col-md-3 mb-3">
							<label for="profile_mb_hp">휴대폰</label>
							<input type="text"
								   class="form-control"
								   id="profile_mb_hp"
								   name="mb_hp"
								   value="<?=htmlspecialchars((string) $row['mb_hp'], ENT_QUOTES)?>"
								   required>
						</div>

						<div class="col-md-3 mb-3">
							<label for="profile_mb_type">회원등급</label>
							<select class="form-control"
									id="profile_mb_type"
									name="mb_type">
								<?php
								$member_type_options = fnGetType();
								foreach ($member_type_options as $member_type_option) {
									$member_type_option = (string) $member_type_option;
								?>
								<option
									value="<?=htmlspecialchars($member_type_option, ENT_QUOTES)?>"
									<?=$member_type_option === (string) ($row['mb_type'] ?? '') ? 'selected' : ''?>
								>
									<?=htmlspecialchars($member_type_option, ENT_QUOTES)?>
								</option>
								<?php } ?>
							</select>
						</div>

						<div class="col-md-3 mb-3">
							<label for="profile_mb_password">비밀번호 변경</label>
							<input type="password"
								   class="form-control"
								   id="profile_mb_password"
								   name="mb_password"
								   autocomplete="new-password"
								   placeholder="변경할 때만 입력">
						</div>
					</div>

<?php
$distribution_day_columns = array(
    'mon' => 'num_mon',
    'tue' => 'num_tue',
    'wed' => 'num_wed',
    'thur' => 'num_thur',
    'fri' => 'num_fri',
);

$distribution_day = 'thur';
$distribution_qty = 20;
$distribution_configured = false;

foreach ($distribution_day_columns as $day_key => $column_name) {
    $day_qty = isset($row[$column_name])
        ? (int) $row[$column_name]
        : 0;

    if ($day_qty > 0) {
        $distribution_day = $day_key;
        $distribution_qty = $day_qty;
        $distribution_configured = true;
        break;
    }
}
?>

					<div class="row">
						<div class="col-md-4 mb-3">
							<label>아이디</label>
							<input type="text"
								   class="form-control"
								   value="<?=htmlspecialchars((string) $row['mb_id'], ENT_QUOTES)?>"
								   readonly>
						</div>

						<div class="col-md-4 mb-3 distribution-setting-column">
							<label for="profile_distribution_day">로또 배분 요일</label>
							<select class="form-control"
									id="profile_distribution_day"
									name="distribution_day">
								<option value="mon" <?=$distribution_day === 'mon' ? 'selected' : ''?>>월요일</option>
								<option value="tue" <?=$distribution_day === 'tue' ? 'selected' : ''?>>화요일</option>
								<option value="wed" <?=$distribution_day === 'wed' ? 'selected' : ''?>>수요일</option>
								<option value="thur" <?=$distribution_day === 'thur' ? 'selected' : ''?>>목요일</option>
								<option value="fri" <?=$distribution_day === 'fri' ? 'selected' : ''?>>금요일</option>
							</select>
						</div>

						<div class="col-md-4 mb-3 distribution-setting-column">
							<label for="profile_distribution_qty">로또 배분 수량</label>
							<input type="number"
								   min="1"
								   class="form-control"
								   id="profile_distribution_qty"
								   name="distribution_qty"
								   value="<?=(int) $distribution_qty?>"
								   required>
							<?php if (!$distribution_configured) { ?>
							<small class="form-text text-muted">
								미설정 회원은 목요일 20개가 기본입니다.
							</small>
							<?php } ?>
						</div>
					</div>

					<div class="row distribution-setting-column">
						<div class="col-md-12 mb-3">
							<div class="form-check">
								<input type="checkbox"
									   class="form-check-input"
									   id="profile_distribution_apply_now"
									   name="distribution_apply_now"
									   value="1">
								<label class="form-check-label"
									   for="profile_distribution_apply_now">
									<strong>바로적용</strong>
									- 현재 회차의 아직 발송되지 않은 번호부터 새 요일/수량으로 적용
								</label>
							</div>
							<small class="form-text text-muted">
								이미 발송이 시작된 현재 회차 문자는 중복발송 방지를 위해 바로적용되지 않습니다.
							</small>
						</div>
					</div>

					<div class="row" id="free_distribution_setting_row">
						<div class="col-md-6 mb-3">
							<label for="profile_free_num_date">무료회원 조합 종료일</label>
							<input type="date"
								   class="form-control"
								   id="profile_free_num_date"
								   name="free_num_date"
								   value="<?=htmlspecialchars((string) ($row['free_num_date'] ?? ''), ENT_QUOTES)?>">
						</div>

						<div class="col-md-6 mb-3">
							<label for="profile_free_num_qty">무료회원 조합 수량</label>
							<input type="number"
								   min="0"
								   class="form-control"
								   id="profile_free_num_qty"
								   name="free_num_qty"
								   value="<?=(int) ($row['free_num_qty'] ?? 0)?>">
						</div>
					</div>
				</div>

				<div class="card-footer text-right">
					<button type="submit" class="btn btn-success">
						회원정보 저장
					</button>
				</div>
			</form>
		</div>

		<div class="card card-warning card-outline">
			<div class="card-header">
				<h3 class="card-title">회원관리</h3>
			</div>

			<div class="card-body">
				<div class="row">
					<div class="col-md-6 mb-2">
						<?php if (!empty($row['hold_datetime']) || (int) ($row['left_day'] ?? 0) > 0) { ?>
						<form method="post"
							  action="pop.member.status.update.php"
							  onsubmit="return confirm('일시정지를 해제하시겠습니까?');">
							<input type="hidden" name="mb_id"
								   value="<?=htmlspecialchars((string) $row['mb_id'], ENT_QUOTES)?>">
							<input type="hidden" name="action" value="start">

							<input type="hidden" name="token"
                                                               value="<?=htmlspecialchars($member_token, ENT_QUOTES)?>">

                                                    <button type="submit" class="btn btn-primary btn-block">
								일시정지 해제
							</button>
                                                    <div class="mt-2"
                                                             style="font-size:17px; line-height:1.6; color:#333;">
                                                            <strong>일시정지 시작:</strong>
                                                            <?=htmlspecialchars((string) $row['hold_datetime'], ENT_QUOTES)?><br>
                                                            <strong>남은 이용기간:</strong>
                                                            <?=(int) ($row['left_day'] ?? 0)?>일
                                                    </div>
						</form>
						<?php } else { ?>
						<form method="post"
							  action="pop.member.status.update.php"
							  onsubmit="return confirm('이 회원의 이용기간을 일시정지하시겠습니까?');">
							<input type="hidden" name="mb_id"
								   value="<?=htmlspecialchars((string) $row['mb_id'], ENT_QUOTES)?>">
							<input type="hidden" name="action" value="hold">
                                                    <input type="hidden" name="token"
                                                               value="<?=htmlspecialchars($member_token, ENT_QUOTES)?>">

							<button type="submit" class="btn btn-warning btn-block">
								일시정지
							</button>
						</form>
						<?php } ?>
					</div>

					<div class="col-md-6 mb-2">
						<?php if (trim((string) ($row['mb_leave_date'] ?? '')) === '') { ?>
						<form method="post"
							  action="pop.member.status.update.php"
							  onsubmit="return confirm('정말 이 회원을 탈퇴 처리하시겠습니까?\n상담·결제·배분·문자 이력은 삭제하지 않습니다.');">
							<input type="hidden" name="mb_id"
								   value="<?=htmlspecialchars((string) $row['mb_id'], ENT_QUOTES)?>">
							<input type="hidden" name="action" value="leave">
                                                    <input type="hidden" name="token"
                                                               value="<?=htmlspecialchars($member_token, ENT_QUOTES)?>">

							<button type="submit" class="btn btn-danger btn-block">
								회원 탈퇴
							</button>
						</form>
						<?php } else { ?>
						<div class="btn btn-secondary btn-block"
                                                         style="font-size:17px; font-weight:700;">
                                                    탈퇴회원
                                                    (<?=htmlspecialchars((string) $row['mb_leave_date'], ENT_QUOTES)?>)
                                            </div>

                                            <?php if ($login_level >= LOTTO_ROLE_ADMIN) { ?>
                                            <form method="post"
                                                      action="pop.member.status.update.php"
                                                      class="mt-2"
                                                      onsubmit="return confirm('이 회원의 탈퇴를 취소하시겠습니까?');">
                                                    <input type="hidden"
                                                               name="mb_id"
                                                               value="<?=htmlspecialchars((string) $row['mb_id'], ENT_QUOTES)?>">
                                                    <input type="hidden" name="action" value="restore">
                                                    <input type="hidden"
                                                               name="token"
                                                               value="<?=htmlspecialchars($member_token, ENT_QUOTES)?>">

                                                    <button type="submit"
                                                                    class="btn btn-success btn-block">
                                                            탈퇴 취소
                                                    </button>
                                            </form>
                                            <?php } ?>
						<?php } ?>
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
						<input type="hidden" name="alarm_lm_id" value="<?=$alarm_lm_id?>">
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
function validatePaidPeriod(form) {
	var startDate = form.start_date.value;
	var endDate = form.end_date.value;

	if ((startDate && !endDate) || (!startDate && endDate)) {
		alert('유료기간 시작일과 종료일을 모두 선택해주세요.');
		return false;
	}

	if (startDate && endDate && endDate < startDate) {
		alert('유료기간 종료일은 시작일보다 빠를 수 없습니다.');
		return false;
	}

	return true;
}

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


<script>
$(function () {
    function toggleDistributionSettings() {
        var memberType = $.trim(
            $("#profile_mb_type").val() || ""
        );

        if (memberType === "무료회원") {
            $(".distribution-setting-column").hide();
            $("#free_distribution_setting_row").show();
            return;
        }

        if (memberType === "직원" || memberType === "") {
            $(".distribution-setting-column").hide();
            $("#free_distribution_setting_row").hide();
            return;
        }

        $(".distribution-setting-column").show();
        $("#free_distribution_setting_row").hide();
    }

    $("#profile_mb_type").on(
        "change",
        toggleDistributionSettings
    );

    toggleDistributionSettings();
});
</script>
