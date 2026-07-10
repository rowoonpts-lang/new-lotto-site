<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.php");
	
	$spamList = fnGetSpan();
	$sql_common = " from g5_member a, g5_member_etc b ";
	$sql_search = " where 1=1 and a.mb_id = b.mb_id and a.mb_id != 'admin' and mb_level < 5 ";
	$sql_order = " order by mb_datetime desc ";

	if($sch_select){
		if($sch_select == "a.mb_code"){
			$sql_search .= " and {$sch_select} = '{$sch_text}' ";
		}else{
			$sql_search .= " and {$sch_select} like '%{$sch_text}%' ";
		}
	}else{
		$sql_search .= " and (a.mb_code like '%{$sch_text}%' or a.mb_name like '%{$sch_text}%' or a.mb_hp like '%{$sch_text}%' or a.mb_id like '%{$sch_text}%') ";
	}

	if($sch_mb_type){
		if($sch_mb_type != "종료등급"){
			if($sch_mb_type == "일시정지"){
				$sql_search .= " and left_day > 0 ";
			}else{
				$sql_search .= " and mb_type = '{$sch_mb_type}' and left_day < 1 ";				
			}
		}else if($sch_mb_type == "종료등급"){
			$sql_search .= " and free_pre_type != '' ";
		}
	}

	if($sch_mb_db){
		$sql_search .= " and mb_db = '{$sch_mb_db}' ";
	}

	if($start_date){
		$sql_search .= " and substr(mb_datetime,1,10) >= substr('{$start_date}',1,10) ";
	}
	if($end_date){
		$sql_search .= " and substr(mb_datetime,1,10) <= substr('{$end_date}',1,10) ";
	}
	if($sch_mb_status){
		$sql_search .= " and recent_select = '{$sch_mb_status}' ";
	}

	

	$sql = " select count(distinct a.mb_id) as cnt {$sql_common} {$sql_search} {$sql_order} ";

	$row = sql_fetch($sql);
	$total_count = $row['cnt'];


	$rows = 30;
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
				<div class="col-md-1">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="sch_select" aria-hidden="true" autocomplete="off">
						<option selected="selected" value="">전체</option>
						<option value="a.mb_code" <?if($sch_select == "a.mb_code"){echo "selected";}?>>회원코드</option>
						<option value="a.mb_name" <?if($sch_select == "a.mb_name"){echo "selected";}?>>회원명</option>
						<option value="a.mb_hp" <?if($sch_select == "a.mb_hp"){echo "selected";}?>>연락처</option>
						<option value="a.mb_id" <?if($sch_select == "a.mb_id"){echo "selected";}?>>아이디</option>
					</select>
				</div>
				<div class="col-md-2">
					<input type="text" class="form-control" name="sch_text" value="<?=$sch_text?>" placeholder="Enter ...">
				</div>
				<div class="col-md-2">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="sch_mb_type" aria-hidden="true">
						<option selected="selected" value="">등급전체</option>
						<option value="일시정지" <?if($sch_mb_type == "일시정지"){echo "selected";}?>>일시정지</option>
						<?
						$mb_type_ary = fnGetType();
						for($i=0; $i < count($mb_type_ary); $i++){
						?>
						<option value="<?=$mb_type_ary[$i]?>" <?if($sch_mb_type == $mb_type_ary[$i]){echo "selected";}?>><?=$mb_type_ary[$i]?></option>
						<?
						}
						?>
						<option value="종료등급" <?if($sch_mb_type == "종료등급"){echo "selected";}?>>종료등급</option>
					</select>
				</div>
				<div class="col-md-1">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="sch_mb_db" aria-hidden="true">
						<option selected="selected" value="">DB경로</option>
						<?
						$sql_db = "select distinct mb_db from g5_member_etc where 1=1 and mb_db not in ('','기타','통화중') order by mb_db asc";
						$result_db = sql_query($sql_db);
						for($i=0; $row_db = sql_fetch_array($result_db); $i++){
						?>
						<option value="<?=$row_db['mb_db']?>" <?if($sch_mb_db == $row_db['mb_db']){echo "selected";}?>><?=$row_db['mb_db']?></option>
						<?
						}
						?>
					</select>
				</div>
				<div class="col-md-1">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" name="sch_mb_status" aria-hidden="true">
						<option selected="selected" value="">DB상태</option>
						<?
							$memoList = fnGetMemoStatus();
							for($k=0; $k < count($memoList); $k++){
						?>
						<option value="<?=$memoList[$k]?>" <?if($sch_mb_status == $memoList[$k]){echo "selected";}?>><?=$memoList[$k]?></option>
						<?	}?>
					</select>
				</div>
				<div class="col-md-5">
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
							<button class="btn btn-block btn-success" type="button" onClick="fnExcel()">엑셀다운</button>
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
				<div class="col-2">
					<button type="button" class="btn btn-block btn-danger" onClick="fnMemberChkDel()">선택삭제</button>
				</div>
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
					<th>종료등급</th>
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
				<?for($i=0; $row = sql_fetch_array($result); $i++){?>
				<tr>
					<td><input type="checkbox" name="chk[]" value="<?=$row['mb_id']?>"></td>
					<td><?=$total_count-($page-1)*$rows-$i?></td>
					<td><?=$row['mb_code']?></td>
					<td>
						<?=$row['mb_name']?><br>
						<?=$row['mb_hp']?>
						<?if (in_array($row['mb_hp'], $spamList)) {?>
						<br>
						<span style="color:red">[080스팸]</span>
						<?}?>
					</td>
					<td><?=$row['mb_id']?></td>
					<td>
						<?
							if($row['left_day'] > 0){
								echo "일시정지";
							}else{
								echo $row['mb_type'];
							}
						?>
					</td>
					<td><?=$row['free_pre_type']?></td>
					<td>
						<?
							if($row['left_day'] < 1){
								if(intval((strtotime($row[end_date]) - strtotime(date("Y-m-d"))) / 86400) > 0){
									echo intval((strtotime($row[end_date]) - strtotime(date("Y-m-d"))) / 86400);
								}else{
									echo "0";
								}
						}else{
							echo $row['left_day'];							
						}
						?>일
					</td>
					<td>
						<?
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
						남은조합 : <?=($tot_num-$row[use_num])?><br>
						<?=$tot_text?>
					</td>
					<td><?=$row[mb_datetime]?><br><?=$row[mb_today_login]?></td>
					<td>
						<?
							if(!$row[mb_yak]){
								echo "N";
							}else{
								echo "<span style='color:blue'>".$row['mb_yak']."</span>";
							}
						?>
					</td>
					<td><?=str_replace("homepage","home",$row[mb_db])?></td>
					<td>
						<?if($row[recent_select]){?>
							<?=$row[recent_select]?>
						<?}?>
					</td>
					<td><button type="button" class="btn btn-block btn-primary" onclick="fnMemmberMemo('<?=base64_encode($row[mb_id])?>')">상세상담</button></td>
					<td><button type="button" class="btn btn-block btn-danger" onClick="fnMemberDel('<?=base64_encode($row[mb_id])?>')">삭제</button></td>
				</tr>
				<?}?>
				<?if($total_count < 1){?>
				<tr>
					<td colspan="13">내역이 없습니다.</td>
				</tr>
				<?}?>
				</tbody>
				</table>
				</form>
				<?php 
					$qstr .= "&sch_select={$sch_select}&sch_text={$sch_text}&sch_mb_type={$sch_mb_type}&start_date={$start_date}&end_date={$end_date}&sch_mb_status={$sch_mb_status}&sch_mb_db={$sch_mb_db}";
					echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); 
				?>
			</div>
			<!-- /.card-body -->
		</div>
		<!-- /.card -->
	</div>
	
</div>

<script>
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

function fnExcel(){
	location.href="./member.all.excel.php?1=1<?=$qstr?>";
}


function fnMemberDel(mb_id){
	if(confirm("회원을 삭제하시겠습니까?")==true){
		location.href="./member.del.php?mb_id="+mb_id;
	}
}
</script>
<?
	include_once(G5_LADMIN_PATH."/tail.php");
?>