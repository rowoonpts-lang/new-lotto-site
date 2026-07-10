<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.sub.php");

	$mb_id2 = $mb_id;
	$mb_id = base64_decode($mb_id);

	$sql= "select * from g5_member where 1=1 and mb_id = '{$mb_id}'";
	$row = sql_fetch($sql);
?>
<!-- Main content -->
<section class="content">
	<div class="container-fluid">
	<div class="row">
		<!-- left column -->
		<div class="col-md-12 col-12">
			<!-- general form elements -->
			<div class="card card-primary">
				<?include_once("./member.head.php");?>
				<!-- /.card-header -->
				<!-- form start -->
				<form name="frm" id="frm" role="form" autocomplete="off" action="sms.udpate.php" onSubmit="return fnSubmit();">
				<input type="hidden" name="mb_hp" value="<?=$row[mb_hp]?>">
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
												<?=$row[mb_name]?> / <?=$row[mb_hp]?>
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
									<?
										$sql_common = " from msg_cust_log ";
										$sql_search = " where 1=1 and phone_no = '{$row[mb_hp]}' ";
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
										<td><?=$row2[msg_type]?></td>
										<td style="text-align:left"><?=nl2br($row2[message])?></td>
										<td><?=$row2[send_time]?></td>
										<td>
											<?
												//$rt = getSmsResult($row2[table_name], $row2[msg_id]);
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
										<td><button type="button" class="btn btn-primary" onClick="fnSmsReSend('<?=$row2[idx]?>')">재전송</button></td>
									</tr>
									<?	}?>
									<?if($total_count < 1){?>
									<tr>
										<td colspan="10" style="text-align:center;">발송된 문자가 없습니다.</td>
									</tr>
									<?	}?>
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