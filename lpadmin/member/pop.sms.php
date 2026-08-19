<?php
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.sub.php");

	$mb_id2 = isset($mb_id) ? $mb_id : '';
	$mb_id = base64_decode($mb_id2, true);

	if ($mb_id === false || trim((string) $mb_id) === '') {
		alert('회원 정보가 올바르지 않습니다.');
		exit;
	}

	$mb_id = trim((string) $mb_id);
	$safe_mb_id = sql_real_escape_string($mb_id);

	$row = sql_fetch(
		"select *
		   from g5_member
		  where mb_id = '{$safe_mb_id}'
		  limit 1",
		false
	);

	if (empty($row['mb_id'])) {
		alert('회원을 찾을 수 없습니다.');
		exit;
	}

	$login_mb_id = isset($member['mb_id'])
		? trim((string) $member['mb_id'])
		: '';
	$login_level = isset($member['mb_level'])
		? (int) $member['mb_level']
		: 0;

	if (!lottoCanViewMember(
		$login_mb_id,
		$login_level,
		$mb_id
	)) {
		alert('접근 권한이 없는 회원입니다.');
		exit;
	}

	$lotto_turns = array();

	$turn_result = sql_query(
		"select distinct draw_no
		   from l_member_combination
		  where mb_id = '{$safe_mb_id}'
		  order by draw_no desc",
		false
	);

	while ($turn_row = sql_fetch_array($turn_result)) {
		$draw_no = (int) $turn_row['draw_no'];

		if ($draw_no > 0) {
			$lotto_turns[] = $draw_no;
		}
	}
?>
<!-- Main content -->
<section class="content">
	<div class="container-fluid">
	<div class="row">
		<!-- left column -->
		<div class="col-md-12 col-12">
			<!-- general form elements -->
			<div class="card card-primary">
				<?php include_once("./member.head.php");?>
				<!-- /.card-header -->

				<div class="card-body pb-0">
					<div class="card card-outline card-secondary">
						<div class="card-header">
							<h3 class="card-title">로또 조합 문자</h3>
						</div>
						<div class="card-body">
							<div class="row align-items-end">
								<div class="col-md-3 col-12">
									<label for="lotto_turn">회차</label>
									<select id="lotto_turn" class="form-control">
										<?php foreach ($lotto_turns as $draw_no) { ?>
										<option value="<?=(int) $draw_no?>"><?=(int) $draw_no?> 회차</option>
										<?php } ?>
									</select>
								</div>

								<div class="col-md-3 col-12">
									<button
										type="button"
										class="btn btn-primary"
										<?php if (empty($lotto_turns)) { ?>disabled<?php } ?>
										onClick="fnLottoResend();"
									>전체 조합 재발송</button>
								</div>

								<div class="col-md-2 col-12">
									<label for="lotto_add_count">추가 조합 수</label>
									<input
										type="number"
										id="lotto_add_count"
										class="form-control"
										min="1"
										value="1"
									>
								</div>

								<div class="col-md-3 col-12">
									<button
										type="button"
										id="btn_lotto_add"
										class="btn btn-secondary"
										<?php if (empty($lotto_turns)) { ?>disabled<?php } ?>
										onClick="fnLottoAddSend();"
									>추가 조합 발송</button>
								</div>
							</div>

							<div class="text-muted mt-2">
								전체 재발송은 기존 번호를 다시 보내고,
								추가 조합 발송은 새 조합을 추가로 생성합니다.
							</div>
						</div>
					</div>
				</div>
				<!-- form start -->
				<form name="frm" id="frm" role="form" autocomplete="off" action="sms.udpate.php" onSubmit="return fnSubmit();">
				<input type="hidden" name="mb_hp" value="<?=$row['mb_hp']?>">
					<div class="row">
						<div class="col-md-4 col-4">
							<div class="card-body">
								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">회원정보</label>
										</div>
										<div class="col-9">
											<div class="row">
												<?=$row['mb_name']?> / <?=$row['mb_hp']?>
											</div>
										</div>
									</div>
								</div>

								
								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">상담내용</label>
										</div>
										<div class="col-9">
											<textarea class="form-control" rows="3" id="sms_content" name="sms_content" placeholder="" style="height:300px"></textarea>
										</div>
									</div>
								</div>
								<div class="form-group">
									<div class="row">
										<div class="col-3">
											<label for="mb_hp">광고멘트</label>
										</div>
										<div class="col-9">
											<div class="custom-control custom-checkbox">
											  <input class="custom-control-input" type="checkbox" id="customCheckbox2" id="chk" name="chk" value="1">
											  <label for="customCheckbox2" class="custom-control-label">하단에 광고멘트 삽입</label>
											</div>
										</div>
									</div>
								</div>
								<button type="submit" class="btn btn-primary">보내기</button>
							</div>
						</div>
						<div class="col-md-8 col-8">
							<div class="card-body">
								<div class="form-group">
									<table class="table table-hover text-nowrap text-sm">
									<thead>
									<tr>
										<th>NO</th>
										<th>전송타입</th>
										<th style="width:30%">발송내용</th>
										<th>발송일자</th>
										<th>처리결과</th>
										<th>재전송</th>
									</tr>
									</thead>
									<tbody>
									</tbody>
									<?php
										$sql_common = " from msg_cust_log ";
										$sql_search = " where 1=1 and phone_no = '{$row['mb_hp']}' ";
										$sql_order = " order by send_time desc ";

										$sql2 = " select count(distinct idx) as cnt {$sql_common} {$sql_search} {$sql_order} ";

										$row2 = sql_fetch($sql2);
										$total_count = $row2['cnt'];


										$rows = 10;
										$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
										if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
										$from_record = ($page - 1) * $rows; // 시작 열을 구함'


										$limit = " limit {$from_record}, {$rows} ";

										$sql2 = "select * {$sql_common} {$sql_search} {$sql_order} {$limit}";
										$result2 = sql_query($sql2);
										for($i=0; $row2 = sql_fetch_array($result2); $i++){
									?>
									<tr>
										<td><?=$total_count-($page-1)*$rows-$i?></td>
										<td><?=$row2['msg_type']?></td>
										<td style="text-align:left"><?=nl2br($row2['message'])?></td>
										<td><?=$row2['send_time']?></td>
										<td>
											<?php
												//$rt = getSmsResult($row2['table_name'], $row2['msg_id']);
												$rt = $row2['etc'];
												if($rt == "0"){
													echo "성공";
												}else if($rt == ""){
													echo "전송중";
												}else{
													echo "실패";
												}
											?>
										</td>
										<td><button type="button" class="btn btn-primary" onClick="fnSmsReSend('<?=$row2['idx']?>')">재전송</button></td>
									</tr>
									<?php 	}?>
									<?php if($total_count < 1){?>
									<tr>
										<td colspan="10" style="text-align:center;">발송된 문자가 없습니다.</td>
									</tr>
									<?php 	}?>
									</table>

									<?php
										$qstr .="&mb_id=".base64_encode($mb_id);
										echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); 
									?>
								</div>
							</div>
						</div>
					</div>
					<!-- row 끝-->
				</div>
				<!-- /.card-body -->
			</form>
		</div>
	</div>
</section>
<!-- /.card -->
<script>
function fnLottoResend(){
	var drawNo = parseInt($("#lotto_turn").val(), 10);

	if(!drawNo){
		alert("재발송할 회차를 선택해주세요.");
		return false;
	}

	window.open(
		"pop.lotto.sms.resend.php?mb_id="
		+ encodeURIComponent(<?=json_encode($mb_id)?>)
		+ "&draw_no="
		+ encodeURIComponent(drawNo),
		"lottoSmsResend",
		"width=620,height=720,scrollbars=yes,resizable=yes"
	);

	return false;
}

function fnLottoAddSend(){
	var drawNo = parseInt($("#lotto_turn").val(), 10);
	var count = parseInt($("#lotto_add_count").val(), 10);
	var $button = $("#btn_lotto_add");

	if(!drawNo){
		alert("추가발송할 회차를 선택해주세요.");
		return false;
	}

	if(!count || count < 1){
		alert("추가 조합 수량을 1개 이상 입력해주세요.");
		$("#lotto_add_count").focus();
		return false;
	}

	if(!confirm(
		drawNo
		+ "회차에 "
		+ count
		+ "개의 새 조합을 추가하시겠습니까?"
	)){
		return false;
	}

	$button.prop("disabled", true);

	$.ajax({
		url: "ajax.create.number.php",
		type: "POST",
		dataType: "json",
		data: {
			mb_id: <?=json_encode($mb_id)?>,
			draw_no: drawNo,
			cnt: count
		},
		success: function(result){
			if(!result || result.success !== true){
				alert(
					result && result.error
						? result.error
						: "추가 조합 생성에 실패했습니다."
				);
				return;
			}

			if(!result.distribution_batch){
				alert("추가 조합 배치 정보를 확인할 수 없습니다.");
				return;
			}

			window.open(
				"pop.lotto.sms.confirm.php?batch="
				+ encodeURIComponent(result.distribution_batch),
				"lottoSmsConfirm",
				"width=620,height=720,scrollbars=yes,resizable=yes"
			);
		},
		error: function(){
			alert("추가 조합 생성 중 서버 오류가 발생했습니다.");
		},
		complete: function(){
			$button.prop("disabled", false);
		}
	});

	return false;
}

function fnSubmit(){
	if($("#sms_content").val().replace(/ /gi, '') == ""){
		alert("보내실 상담내용을 입력하세요.");
		$("#sms_content").focus();
		return false;
	}

	return true;
}

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