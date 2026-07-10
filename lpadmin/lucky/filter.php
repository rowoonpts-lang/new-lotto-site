<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.php");

	$sql = "select * from l_filter where 1=1 order by f_id asc";
	$result= sql_query($sql);

	

	//$sql_af = "select * from "
	
?>



<div class="row">
	<div class="col-12">
		<div class="card">
			<!-- /.card-header -->
			<div class="card-body table-responsive p-0">
				<table class="table table-hover text-nowrap">
				<thead>
				<tr>
					<th>등급</th>
					<th>필터</th>
				</tr>
				</thead>
				<tbody>
				<?
					for($i=0; $row = sql_fetch_array($result); $i++){
						$sql2 = "
								SELECT SUM(num_mon) AS mon, SUM(num_tue) AS tue, SUM(num_wed) AS wed, SUM(num_thur) AS thur, SUM(num_fri) AS fri, SUM(num_sat) AS sat
								FROM g5_member a, g5_member_etc b 
								WHERE 1=1 
									AND a.mb_id = b.mb_id 
									AND end_date >= substr(NOW(),1,10) 
									AND a.mb_type IN ('{$row['f_type']}')
								";
						$row2 = sql_fetch($sql2);
				?>
				<tr>
					<td style="width:450px">
						<?=$row['f_type']?><br>
						<?
							$sql_cnt = "select count(lf_id) cnt from l_filter_temp where 1=1 and type = '{$row['f_type']}' and st_tp = '1'";
							$row_cnt = sql_fetch($sql_cnt);
						?>
						월 : <?=number_format($row2['mon'])?> / 화 : <?=number_format($row2['tue'])?> / 수 : <?=number_format($row2['wed'])?> / 목 : <?=number_format($row2['thur'])?> / 금 : <?=number_format($row2['fri'])?> / 토 : <?=number_format($row2['sat'])?> / 총 : <?=number_format($row2['mon']+$row2['tue']+$row2['wed']+$row2['thur']+$row2['fri']+$row2['sat'])?><br>
						사용가능갯수 : <?=number_format($row_cnt['cnt'])?>개

						<br><br>
						<?
							$sql3 = "select * from l_filter_temp where 1=1 and type = '{$row['f_type']}' and st_tp = '1' order by rand() limit 10";
							$result3 = sql_query($sql3);
							for($k=0; $row3 = sql_fetch_array($result3); $k++){
								echo $row3['num1'].",".$row3['num2'].",".$row3['num3'].",".$row3['num4'].",".$row3['num5'].",".$row3['num6']."<br>";
							}
						?>
					</td>
					<td style="text-align:left">
						<form id="frm_<?=$row['f_id']?>" name="frm_<?=$row['f_id']?>">
						<input type="hidden" name="f_id" class="form-control" value="<?=$row['f_id']?>">
						<div class="row">
							<div class="col-1">
								제외수
							</div>
							<div class="col-11">
								<input type="text" name="f_filter1" class="form-control" value="<?=$row['f_filter1']?>">
								콤마 단위로 입력
							</div>
						</div>
						<div class="row">
							<div class="col-1">
								총합
							</div>
							<div class="col-11">
								<input type="text" name="f_filter2" class="form-control" value="<?=$row['f_filter2']?>">
								콤마 단위로 입력, 허용범위 90 ~ 180(미만 혹은 초과시 시스템 부하발생 위험)
							</div>
						</div>
						<div class="row">
							<div class="col-1">
								AF필터
							</div>
							<div class="col-11">
								<input type="checkbox" name="f_filter3" class="" <?if($row['f_filter3']){ echo "checked";}?> value="1">
							</div>
						</div>
						<div class="row">
							<div class="col-1">
								홀짝조합
							</div>
							<div class="col-11">
								<!--input type="text" name="f_filter4" class="form-control" value="<?=$row['f_filter4']?>" min="0" max="6">
								홀수의 갯수만 입력하세요.<br>ex. 0~6, 숫자 오버시 치명적인 오류가 발생될 수 있습니다.<br>사용하지 않으려면 비워주세요.-->
								<ul class="ul-horizen">
									<!--li><label><input name="f_filter4[]" type="checkbox" value="" <?if($row['f_filter4'] == ""){echo "checked";}?>>사용안함</label></li-->
									<li><label><input name="f_filter4[]" type="checkbox" value="0" <?if(strpos($row['f_filter4'],"0") !== false){echo "checked";}?>>홀 0 : 짝 6</label></li>
									<li><label><input name="f_filter4[]" type="checkbox" value="1" <?if(strpos($row['f_filter4'],"1") !== false){echo "checked";}?>>홀 1 : 짝 5</label></li>
									<li><label><input name="f_filter4[]" type="checkbox" value="2" <?if(strpos($row['f_filter4'],"2") !== false){echo "checked";}?>>홀 2 : 짝 4</label></li>
									<li><label><input name="f_filter4[]" type="checkbox" value="3" <?if(strpos($row['f_filter4'],"3") !== false){echo "checked";}?>>홀 3 : 짝 3</label></li>
									<li><label><input name="f_filter4[]" type="checkbox" value="4" <?if(strpos($row['f_filter4'],"4") !== false){echo "checked";}?>>홀 4 : 짝 2</label></li>
									<li><label><input name="f_filter4[]" type="checkbox" value="5" <?if(strpos($row['f_filter4'],"5") !== false){echo "checked";}?>>홀 5 : 짝 1</label></li>
									<li><label><input name="f_filter4[]" type="checkbox" value="6" <?if(strpos($row['f_filter4'],"6") !== false){echo "checked";}?>>홀 6 : 짝 0</label></li>
								</ul>
							</div>
						</div>
						<div class="row">
							<div class="col-1">
								저고차필터
							</div>
							<div class="col-11">
								<!--input type="text" name="f_filter5" class="form-control" value="<?=$row['f_filter5']?>" min="0" max="6"-->
								<ul class="ul-horizen">
									<!--li><label><input name="f_filter5[]" type="checkbox" value="" <?if($row['f_filter5'] == ""){echo "checked";}?>>사용안함</label></li-->
									<li><label><input name="f_filter5[]" type="checkbox" value="0" <?if(strpos($row['f_filter5'],"0") !== false){echo "checked";}?>>저 0 : 고 6</label></li>
									<li><label><input name="f_filter5[]" type="checkbox" value="1" <?if(strpos($row['f_filter5'],"1") !== false){echo "checked";}?>>저 1 : 고 5</label></li>
									<li><label><input name="f_filter5[]" type="checkbox" value="2" <?if(strpos($row['f_filter5'],"2") !== false){echo "checked";}?>>저 2 : 고 4</label></li>
									<li><label><input name="f_filter5[]" type="checkbox" value="3" <?if(strpos($row['f_filter5'],"3") !== false){echo "checked";}?>>저 3 : 고 3</label></li>
									<li><label><input name="f_filter5[]" type="checkbox" value="4" <?if(strpos($row['f_filter5'],"4") !== false){echo "checked";}?>>저 4 : 고 2</label></li>
									<li><label><input name="f_filter5[]" type="checkbox" value="5" <?if(strpos($row['f_filter5'],"5") !== false){echo "checked";}?>>저 5 : 고 1</label></li>
									<li><label><input name="f_filter5[]" type="checkbox" value="6" <?if(strpos($row['f_filter5'],"6") !== false){echo "checked";}?>>저 6 : 고 0</label></li>
								</ul>
								낮은수 1~22가 나오는 갯수 입니다.
							</div>
						</div>
						<div class="row">
							<div class="col-1">
								연번갯수
							</div>
							<div class="col-11">
								<ul class="ul-horizen">
									<!--li><label><input name="f_filter6[]" type="checkbox" value="" <?if(strpos($row['f_filter6'],"0") !== ""){echo "checked";}?>>사용안함</label></li-->
									<li><label><input name="f_filter6[]" type="checkbox" value="0" <?if(strpos($row['f_filter6'],"0") !== false){echo "checked";}?>>0개</label></li>
									<li><label><input name="f_filter6[]" type="checkbox" value="1" <?if(strpos($row['f_filter6'],"1") !== false){echo "checked";}?>>1개</label></li>
									<li><label><input name="f_filter6[]" type="checkbox" value="2" <?if(strpos($row['f_filter6'],"2") !== false){echo "checked";}?>>2개</label></li>
									<li><label><input name="f_filter6[]" type="checkbox" value="3" <?if(strpos($row['f_filter6'],"3") !== false){echo "checked";}?>>3개</label></li>
									<li><label><input name="f_filter6[]" type="checkbox" value="4" <?if(strpos($row['f_filter6'],"4") !== false){echo "checked";}?>>4개</label></li>
									<li><label><input name="f_filter6[]" type="checkbox" value="5" <?if(strpos($row['f_filter6'],"5") !== false){echo "checked";}?>>5개</label></li>
									<li><label><input name="f_filter6[]" type="checkbox" value="6" <?if(strpos($row['f_filter6'],"6") !== false){echo "checked";}?>>6개</label></li>
								</ul>
							</div>
						</div>
						<div class="row">
							<div class="col-1">
								끝수합
							</div>
							<div class="col-11">
								<ul class="ul-horizen">
									<!--li><label><input name="f_filter7[]" type="checkbox" value="" <?if($row['f_filter7'] == ""){echo "checked";}?>>사용안함</label></li-->
									<li><label><input name="f_filter7[]" type="checkbox" value="0" <?if(strpos($row['f_filter7'],"0") !== false){echo "checked";}?>>0</label></li>
									<li><label><input name="f_filter7[]" type="checkbox" value="1" <?if(strpos($row['f_filter7'],"1") !== false){echo "checked";}?>>1</label></li>
									<li><label><input name="f_filter7[]" type="checkbox" value="2" <?if(strpos($row['f_filter7'],"2") !== false){echo "checked";}?>>2</label></li>
									<li><label><input name="f_filter7[]" type="checkbox" value="3" <?if(strpos($row['f_filter7'],"3") !== false){echo "checked";}?>>3</label></li>
									<li><label><input name="f_filter7[]" type="checkbox" value="4" <?if(strpos($row['f_filter7'],"4") !== false){echo "checked";}?>>4</label></li>
									<li><label><input name="f_filter7[]" type="checkbox" value="5" <?if(strpos($row['f_filter7'],"5") !== false){echo "checked";}?>>5</label></li>
									<li><label><input name="f_filter7[]" type="checkbox" value="6" <?if(strpos($row['f_filter7'],"6") !== false){echo "checked";}?>>6</label></li>
									<li><label><input name="f_filter7[]" type="checkbox" value="7" <?if(strpos($row['f_filter7'],"7") !== false){echo "checked";}?>>7</label></li>
									<li><label><input name="f_filter7[]" type="checkbox" value="8" <?if(strpos($row['f_filter7'],"8") !== false){echo "checked";}?>>8</label></li>
									<li><label><input name="f_filter7[]" type="checkbox" value="9" <?if(strpos($row['f_filter7'],"9") !== false){echo "checked";}?>>9</label></li>
								</ul>
							</div>
						</div>
						</form>
						<p style="padding:10px 0px">
							
							<button class="btn btn-block btn-success" type="button" onClick="fnSave('<?=$row['f_id']?>')">저장</button>
							<button class="btn btn-block btn-primary" type="button" onClick="fnCreate('<?=$row['f_type']?>')">생성</button>
							<?if($row_cnt['cnt'] > 0){?>
							<button class="btn btn-block btn-danger" type="button" onClick="fnDel('<?=$row['f_type']?>')">기존번호 제거</button>
							<?}?>
						</p>
					</td>
				</tr>
				<?}?>
				</tbody>
				</table>

				<?php 
					$qstr .= "&sch_select={$sch_select}&sch_text={$sch_text}&sch_mb_type={$sch_mb_type}&lucky_result={$lucky_result}&turn={$turn}";
					echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); 
				?>
			</div>
			<!-- /.card-body -->
		</div>
		<!-- /.card -->
	</div>
</div>

<div style="position:fixed;left:50%;top:50%;margin-left:-300px;margin-top:-300px;display:none;z-index:10002" id="loading_box">
<img src="<?=G5_THEME_IMG_URL?>/lotto_loading.gif">
</div>
<div class="abs_div2" style="position:fixed;background:rgba(0,0,0,.5);width:100%;height:100vh;left:0;top:0;z-index:10000;display:none"></div>
<script>


function loadingBox(){
	$("#loading_box").show();
	$(".abs_div2").show();
	$('html, body').css({'overflow': 'hidden', 'height': '100%'}); //scroll hidden 해제
}
function fnSave(f_id){
	var string = $("form[name='frm_"+f_id+"']").serialize();

	$.ajax({
		type: "POST",
		url: "./filter.update.php",
		data: string, 
		cache: false,
		async: false,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {
			if(data == "1111"){
				alert("정상적으로 저장되었습니다.");		
			}
			if(data == "0000"){
				alert("총합필더 갯수 이상");		
				return false;
			}
			if(data == "0001"){
				alert("총합필더 허용구간 이상");		
				return false;
			}
			console.log(data);
			location.reload();
		}
	});
	return false;
}

function fnCreate(f_type){
	if(confirm("필터를 입력하고 저장하셨습니까?\n기본 생성갯수는 20,000개 이며, 다소 시간이 소요됩니다.(약 1분소요)") == true){
		loadingBox();
		location.href="<?=G5_LADMIN_URL?>/lucky/test.php?f_type="+f_type;
	}
}
function fnDel(f_type){
	if(confirm("등록된 필터 번호를 모두 삭제하시겠습니까?") == true){
		location.href="<?=G5_LADMIN_URL?>/lucky/test.del.php?f_type="+f_type;
	}
}
</script>

<?
	include_once(G5_LADMIN_PATH."/tail.php");
?>