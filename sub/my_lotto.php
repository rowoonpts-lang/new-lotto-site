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

<div id="my_lotto" class="my">

	<div class="my_lotto_top my_cont my_cont1 active">
		<div class="my_lotto_box my_box_lt">
			<p class="my_lotto_tit">나의 당첨 현황</p>
			<ul class="my_lotto_ul">
				<li>
					<p class="th_p">
						<span class="rank rank_1">1등</span>
					</p>
					<p class="td_p"><?=$row[lucky1]?>회 당첨되셨습니다.</p>
				</li>
				<li>
					<p class="th_p">
						<span class="rank rank_2">2등</span>
					</p>
					<p class="td_p"><?=$row[lucky2]?>회 당첨되셨습니다.</p>
				</li>
				<li>
					<p class="th_p">
						<span class="rank rank_3">3등</span>
					</p>
					<p class="td_p"><?=$row[lucky3]?>회 당첨되셨습니다.</p>
				</li>
				<li>
					<p class="th_p">
						<span class="rank rank_4">4등</span>
					</p>
					<p class="td_p"><?=$row[lucky4]?>회 당첨되셨습니다.</p>
				</li>
				<li>
					<p class="th_p">
						<span class="rank rank_5">5등</span>
					</p>
					<p class="td_p"><?=$row[lucky5]?>회 당첨되셨습니다.</p>
				</li>
			</ul>
		</div>
		<div class="my_lotto_box my_box_rt">
			<p class="my_lotto_tit">회원정보<a href="<?=G5_URL?>/sub/my_info.php">수정</a></p>
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
			</table>
		</div>
	</div>

</div>

<script>
function check_all(f)
{
    var chk = document.getElementsByName("sms[]");

    for (i=0; i<chk.length; i++)
        chk[i].checked = f.chkall.checked;
}
</script>

<?
	include_once(G5_PATH."/_tail.php");
?>