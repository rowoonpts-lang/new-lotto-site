<?php
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.sub.php");

	$mb_id2 = isset($mb_id) ? $mb_id : '';
	$mb_id = base64_decode($mb_id2);
	$safe_mb_id = sql_real_escape_string($mb_id);

	$sql = "
		select *
		from g5_member a
		join g5_member_etc b on a.mb_id = b.mb_id
		where a.mb_id = '{$safe_mb_id}'
		limit 1
	";
	$row = sql_fetch($sql);

	$member_turns = array();

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
			$member_turns[] = $draw_no;
		}
	}

	$turn = isset($_GET['turn']) ? (int) $_GET['turn'] : 0;

	if ($turn < 1 && !empty($member_turns)) {
		$turn = (int) $member_turns[0];
	}

	$list = getLuckyNum($turn);
	$luckAry = array($list['drwtNo1'], $list['drwtNo2'],$list['drwtNo3'],$list['drwtNo4'],$list['drwtNo5'],$list['drwtNo6']);
	//print_r($luckAry);

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
				<!-- form start -->
				<div class="row">				
					<div class="col-2" style="padding:10px 20px 0px">
						<form id="turnForm">
							<input type="hidden" name="mb_id" value="<?=base64_encode($mb_id)?>">
							<select id="turn" name="turn" class="form-control select2 select2-hidden-accessible" style="width: 100%;" aria-hidden="true" onChange="$('#turnForm').submit();">
								<?php foreach ($member_turns as $member_turn) { ?>
								<option value="<?=$member_turn?>" <?php if ($turn === $member_turn) { echo "selected"; }?>><?=$member_turn?> 회차</option>
								<?php } ?>
							</select>
						</form>
					</div>
					<div class="col-6" style="padding:10px 20px 0px">
						1등 당첨 : <?=number_format($row[lucky1])?> / 
						2등 당첨 : <?=number_format($row[lucky2])?> /
						3등 당첨 : <?=number_format($row[lucky3])?> /
						4등 당첨 : <?=number_format($row[lucky4])?> /
						5등 당첨 : <?=number_format($row[lucky5])?> 
					</div>
				</div>

				<form name="frm" id="frm" role="form" autocomplete="off" action="member.save.php" onSubmit="return fnSubmit();">
				<input type="hidden" id="mb_hp_chk" value="0">
				<input type="hidden" id="mb_id_chk" value="0">
					<div class="row">
						<div class="col-md-12 col-12">
							<div class="card-body">
								<div class="form-group">
									<table class="table table-hover text-nowrap text-sm">
									<thead>
									<tr>
										<th>NO</th>
										<th>회차</th>
										<th>등급</th>
										<th>조합번호</th>
										<th>발송시간</th>
										<th>결과</th>								
									</tr>
									</thead>
									<tbody>
									</tbody>
									<?php
										$sql_search = "
											where mb_id = '{$safe_mb_id}'
											  and draw_no = '{$turn}'
										";

										$row2 = sql_fetch(
											"select count(*) as cnt
											   from l_member_combination
											   {$sql_search}",
											false
										);
										$total_count = (int) $row2['cnt'];

										$rows = 10;
										$total_page = $total_count > 0 ? (int) ceil($total_count / $rows) : 1;
										$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

										if ($page < 1) {
											$page = 1;
										}

										$from_record = ($page - 1) * $rows;

										$sql2 = "select
												lmc_id,
												draw_no,
												member_type,
												num1, num2, num3, num4, num5, num6,
												result_rank,
												result_checked_at,
												created_at
											from l_member_combination
											{$sql_search}
											order by lmc_id desc
											limit {$from_record}, {$rows}";

										$result2 = sql_query($sql2, false);
										for($i=0; $row2 = sql_fetch_array($result2); $i++){
											$ball_text = "";
											$ball_text = $row2['num1'].",".$row2['num2'].",".$row2['num3'].",".$row2['num4'].",".$row2['num5'].",".$row2['num6'];
									?>
									<tr>
										<td><?=$total_count-($page-1)*$rows-$i?></td>
										<td><?=(int) $row2['draw_no']?></td>
										<td><?=htmlspecialchars($row2['member_type'], ENT_QUOTES, 'UTF-8')?></td>
										<td>
											<?=getBallColor($ball_text,$luckAry)?>
											
										</td>
										<td><?=htmlspecialchars($row2['created_at'], ENT_QUOTES, 'UTF-8')?></td>
										<td><?php
											if (empty($row2['result_checked_at'])) {
												echo '추첨대기';
											} elseif ($row2['result_rank'] !== null && (int) $row2['result_rank'] >= 1 && (int) $row2['result_rank'] <= 5) {
												echo (int) $row2['result_rank'] . '등';
											} else {
												echo '낙첨';
											}
											?></td>
									</tr>
									<?php 	}?>
									<?php if($total_count < 1){?>
									<tr>
										<td colspan="10" style="text-align:center;">발송된 문자가 없습니다.</td>
									</tr>
									<?php }?>
									</table>

									<?php
										$qstr .="&mb_id=".base64_encode($mb_id)."&turn=".$turn;
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
function fnFindHP(){
	var mb_hp = $("#mb_hp").val().replace(/ /gi,'');
	mb_hp = mb_hp.replace(/-/gi,'');
	$("#mb_hp").val(mb_hp);
	if(mb_hp == ""){alert("휴대폰 번호를 입력해주세요.");return false;}
	
	$.ajax({
		type: "POST",
		url: "<?=G5_URL?>/ajax/ajax.find.mb_hp.php",
		data: {mb_hp : mb_hp}, 
		cache: false,
		async: false,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {
			if(data*1 > 0){
				alert("현재 사용중인 휴대폰 번호입니다.");
				$("#mb_hp").val('');
				$("#mb_id").val('');
				return false;
			}else{
				alert("사용이 가능한 휴대폰 번호입니다.");
				$("#mb_id").val(mb_hp);
				$("#mb_hp").attr('readonly', true);
				$("#mb_hp_chk").val("1");
				return false;
			}
		}
	});
	return false;
}

function fnFindID(){
	var mb_id = $("#mb_id").val().replace(/ /gi,'');
	$("#mb_id").val(mb_id);
	if(mb_id == ""){alert("아이디를 입력해주세요.");return false;}
	
	$.ajax({
		type: "POST",
		url: "<?=G5_URL?>/ajax/ajax.find.mb_id.php",
		data: {mb_id : mb_id}, 
		cache: false,
		async: false,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {
			if(data*1 > 0){
				alert("현재 사용중인 아이디입니다.");
				$("#mb_id").val('');
				return false;
			}else{
				alert("사용이 가능한 아이디입니다.");
				$("#mb_id").attr('readonly', true);
				$("#mb_id_chk").val("1");
				return false;
			}
		}
	});
	return false;
}

function fnSubmit(){
	if($("#mb_hp_chk").val() == "0"){
		alert("휴대폰 번호 중복검사를 진행해주세요.");
		return false;
	}
	$("#mb_name").val($("#mb_name").val().replace(/ /gi,''));
	if($("#mb_name").val() == ""){
		alert("이름을 입력해주세요");
		return false;
	}
	if($("#mb_id_chk").val() == "0"){
		alert("아이디 중복검사를 진행해주세요.");
		return false;
	}
	$("#mb_password").val($("#mb_password").val().replace(/ /gi,''));
	if($("#mb_password").val() == ""){
		alert("패스워드를 입력해주세요");
		return false;
	}
	return true;
}
</script>