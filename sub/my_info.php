<?
	include_once("_common.php");
	if(!$is_member){
		alert("로그인 후 이용바랍니다.", G5_BBS_URL."/login.php?url=".G5_URL."/sub/my_lotto.php");
	}
	include_once(G5_PATH."/_head.php");

	$sql = "select * from g5_member_etc where 1=1 and mb_id = '{$member[mb_id]}'";

	$row = sql_fetch($sql);

	$tot_num = $row['num_mon']+$row['num_tue']+$row['num_wed']+$row['num_thur']+$row['num_fri']+$row['num_sat'];
	$week_text = "";
	if($row['num_mon']){if($week_text){$week_text.=" / ";}$week_text .= "월요일";}
	if($row['num_tue']){if($week_text){$week_text.=" / ";}$week_text .= "화요일";}
	if($row['num_wed']){if($week_text){$week_text.=" / ";}$week_text .= "수요일";}
	if($row['num_thur']){if($week_text){$week_text.=" / ";}$week_text .= "목요일";}
	if($row['num_fri']){if($week_text){$week_text.=" / ";}$week_text .= "금요일";}
	if($row['num_sat']){if($week_text){$week_text.=" / ";}$week_text .= "토요일";}

?>

<form name="info_frm" id="info_frm" method="post" action="<?=G5_URL?>/sub/my_info.update.php" onsubmit="return fnSubmit();" autocomplete="off">
	<div id="my_info" class="my">

		<div class="my_info_box">
			<p class="my_lotto_tit">회원정보</p>
			<table class="my_lotto_tb">
				<tr>
					<th>휴대폰번호</th>
					<td><?=add_hyphen($member[mb_hp])?></td>
				</tr>
				<tr>
					<th>가입상품</th>
					<td><span class="color_red"><?=$member[mb_type]?></span></td>
				</tr>
				<tr>
					<th>수신조합</th>
					<td><?=$tot_num?>개 조합 <?if($week_text){?>(<?=$week_text?>)<?}?></td>
				</tr>
				<tr>
					<th>서비스 종료일</th>
					<td>
						<?if($row[end_date] >= date('Y-m-d')){echo $row[end_date];}else{echo "없음";}?>
					</td>
				</tr>
				<!--tr>
					<th>보유캐시</th>
					<td>0원<a href="">캐시충전</a></td>
				</tr-->
			</table>
			<div class="my_lotto_numb">
				<p>1등 보장조합기 이용가능 횟수 : <span class="color_red">0</span></p>
				<p>SMS서비스 남은 횟수 : <span class="color_red">0</span></p>
			</div>
		</div>

		<div class="my_info_box">
			<table class="my_lotto_tb">
				<tr>
					<th>아이디</th>
					<td><?=$member['mb_id']?></td>
				</tr>
				<tr>
					<th>이름</th>
					<td><?=$member['mb_name']?></td>
				</tr>
				<tr>
					<th>닉네임</th>
					<td><?=$member['mb_nick']?></td>
				</tr>
				<tr>
					<th>비밀번호</th>
					<td><input type="password" name="mb_password" id="mb_password" class="my_ipt"></td>
				</tr>
				<tr>
					<th>비밀번호확인</th>
					<td><input type="password" name="mb_password_re" id="mb_password_re" class="my_ipt"></td>
				</tr>
				<!--tr>
					<th>핸드폰번호</th>
					<td>
						<select name="mb_hp1" id="mb_hp1" class="my_ipt2">
							<option value="010">010</option>
							<option value="011">011</option>
							<option value="016">016</option>
							<option value="017">017</option>
							<option value="018">018</option>
							<option value="019">019</option>
						</select>
						<span class="hp_bar">-</span>
						<input type="tel" name="mb_hp2" id="mb_hp2" class="my_ipt2">
						<span class="hp_bar">-</span>
						<input type="tel" name="mb_hp3" id="mb_hp3" class="my_ipt2">
						<p class="my_txt">*당첨확인 등의 문자전송에 사용되므로 정확해야 합니다.</p>
					</td>
				</tr>
				<tr>
					<th>SMS수신</th>
					<td>
						<p class="radio_filter">
							<input type="radio" name="test" id="test1" value="수신함"><label for="test1">수신함</label>
						</p>
						<p class="radio_filter">
							<input type="radio" name="test" id="test2" value="수신하지 않음"><label for="test2">수신하지 않음</label>
						</p>
					</td>
				</tr>
				<tr>
					<th>자동결제</th>
					<td>[이용중 아님]</td>
				</tr-->
			</table>
		</div>

		<!--div class="moonja">
			<p class="moonja_tit">문자메세지 서비스 요일</p>
			<div>
				<p class="radio_filter">
					<input type="radio" name="day" id="day1" value="월요일"><label for="day1">월요일</label>
				</p>
				<p class="radio_filter">
					<input type="radio" name="day" id="day2" value="화요일"><label for="day2">화요일</label>
				</p>
				<p class="radio_filter">
					<input type="radio" name="day" id="day3" value="수요일"><label for="day3">수요일</label>
				</p>
				<p class="radio_filter">
					<input type="radio" name="day" id="day4" value="목요일"><label for="day4">목요일</label>
				</p>
				<p class="radio_filter">
					<input type="radio" name="day" id="day5" value="금요일"><label for="day5">금요일</label>
				</p>
			</div>
		</div-->

		<div class="regi_btn_box">
			<button class="regi_btn">수정하기</button>
			<a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=member_leave.php" class="regi_btn">회원탈퇴</a>
		</div>

	</div>
</form>

<script>
function fnSubmit(){
	var pw = document.getElementById("mb_password").value; //비밀번호
	var pw2 = document.getElementById("mb_password_re").value; // 확인 비밀번호
	if(pw || pw2){
		if(pw.length == 0) {
			alert("비밀번호를 입력해주세요");
			$("#mb_password").focus();
			return false;
		}
		if(pw2.length == 0) {
			alert("비밀번호를 입력해주세요");
			$("#mb_password_re").focus();
			return false;
		}
		if( pw  !=  pw2) {
			alert("비밀번호가 일치하지 않습니다.");
			$("#mb_password_re").focus();
			return false;
		}
	}
}
</script>

<?
	include_once(G5_PATH."/_tail.php");
?>