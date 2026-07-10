<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.sub.php");

	$mb_id2 = $mb_id;
	$mb_id = base64_decode($mb_id);

	$sql= "select * from g5_member a, g5_member_etc b where 1=1 and a.mb_id = b.mb_id and a.mb_id = '{$mb_id}'";
	$row = sql_fetch($sql);

	if(!$turn){
		$turn = getTurn()-1;
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
				<?include_once("./member.head.php");?>
				<!-- /.card-header -->
				<!-- form start -->
				<div class="row">				
					<div class="col-2" style="padding:10px 20px 0px">
						<form id="frm">
							<input type="hidden" name="mb_id" value="<?=base64_encode($mb_id)?>">
							<select id="turn" name="turn" class="form-control select2 select2-hidden-accessible" style="width: 100%;" aria-hidden="true" onChange="$('#frm').submit();">
								<?
									for($i=getTurn(); $i >= $config[cf_1]; $i--){
								?>
								<option value="<?=$i?>" <?if($turn == $i){echo "selected";}?>><?=$i?> 회차</option>
								<?	}?>
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
									<?
										$sql_common = " from l_turn_{$turn} a, g5_member b, g5_member_etc c ";
										$sql_search = " where 1=1 and a.mb_id = b.mb_id and b.mb_id = c.mb_id and a.mb_id = '{$row[mb_id]}' ";
										$sql_order = " order by a.lt_id desc ";

										$sql2 = " select count(distinct lt_id) as cnt {$sql_common} {$sql_search} {$sql_order} ";

										$row2 = sql_fetch($sql2);
										$total_count = $row2['cnt'];


										$rows = 10;
										$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
										if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
										$from_record = ($page - 1) * $rows; // 시작 열을 구함'


										$limit = " limit {$from_record}, {$rows} ";

										$sql2 = "select a.mb_type as mb_type2, a.*, b.*, c.* {$sql_common} {$sql_search} {$sql_order} {$limit}";

										$result2 = sql_query($sql2);
										for($i=0; $row2 = sql_fetch_array($result2); $i++){
											$ball_text = "";
											$ball_text = $row2[num1].",".$row2[num2].",".$row2[num3].",".$row2[num4].",".$row2[num5].",".$row2[num6];
									?>
									<tr>
										<td><?=$total_count-($page-1)*$rows-$i?></td>
										<td><?=$row2[turn]?></td>
										<td><?=$row2[mb_type2]?></td>
										<td>
											<?=getBallColor($ball_text,$luckAry)?>
											
										</td>
										<td><?=$row2[lt_datetime]?></td>										
										<td><?=$row2[result]?></td>
									</tr>
									<?	}?>
									<?if($total_count < 1){?>
									<tr>
										<td colspan="10" style="text-align:center;">발송된 문자가 없습니다.</td>
									</tr>
									<?}?>
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