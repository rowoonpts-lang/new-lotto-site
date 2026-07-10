<?
	include_once("_common.php");
	include_once(G5_PATH."/_head.php");

	$turn = getTurn()-1; 
	if(!$endTurn){ $endTurn = getTurn()-1; }
	if(!$ver){ $ver = '1'; }
	$list = getLuckyNum($turn);
?>

<?if(!G5_IS_MOBILE){?>
<style>
	#sub_div .sub_top{display: none;}
	#sub_div .inner{padding: 0;}
	#sub_div > .inner{width: 100%;}
</style>
<?}else{?>
<style>
	#sub_div .sub_top{display: none;}
	#sub_div .inner{padding: 0;padding-left: 15px;padding-right: 15px;}
	#sub_div > .inner{width: 100%;padding-left: 0;padding-right: 0;}
</style>
<?}?>


<section id="npf">
	<div class="npf_top">
		<div class="tit">모두가 기다리고 기다렸던<br><span class="c_r">로또 1등</span>&nbsp;당첨의 끝판왕!&nbsp;<span class="font_gm_b c_r">	PERFECT!</span></div>
		<div class="desc">기존 TOP-CLASS 등급의 Change-Up!</div>
	</div>
	<div class="npf_bg">
		<div class="inner npf_inner">
			<div id="" class="perfect_div">
				<div class="mbs_rt">
					<div class="mbs_rt_box mbs_rt_perfect">
						<p class="mbs_type">VVIP 회원</p>
						<h3 class="mbs_grade font_gm_b">PERFECT</h3>
						<p class="mbs_price"><b>2,750,000</b> 원</p>
						<div class="mbs_btn_box">
							<button type="button" class="mbs_btn" onclick="fnBbsPopOn('VVIP회원');">상담 문의</button>
						</div>
					</div>
				</div>
			</div>
			<div class="perfect_inner2">
				<div class="mid01_box">
					<h2 class="mid01_h2">MBC 행복드림 동행복권 추첨방송</h2>
					<div class="mid01_move mid01_1_border">
						<p class="mid01_move_img"><img src="<?=G5_THEME_IMG_URL?>/mid01_1_img1.jpg" alt=""></p>
						<p class="mid01_move_txt"><?=$turn?>회ㅣ<?=date("Y년 m월 d일", strtotime($list['drwNoDate']))?> 방송</p>
						<a href="https://dhlottery.co.kr/common.do?method=main" target="_blank" class="mid01_move_a">동행복권사이트 바로가기</a>
					</div>
				</div>
				<div class="mid01_box mid01_box2">
					<?include_once(G5_PATH."/sub/main.lucky.php");?>					
				</div>
			</div>


			<div class="s3_tit">나의 로또 보관함</div>
			<?
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
			<div id="my_lotto" class="my my_perfect">

				<div class="my_lotto_bot my_cont my_cont3 active">
					<div class="select_box">
						<div class="sel_lt">
							<select name="" id="turn_2" class="w91">
								<?
									$endTurn = getTurn();
									$turn = getTurn();
									for($i=$endTurn; $i >= 700; $i--){
								?>
								<option value="<?=$i?>" <?if($turn == $i){echo "selected";}?>><?=$i?>회 보관함</option>
								<?	}?>
							</select>
						</div>
						<div class="sel_rt">
							<form name="" id="" action="">
								<p class="radio_filter">
									<input type="radio" name="sql_type_2" id="test1" value="전체" checked><label for="test1">전체</label>
								</p>
								<p class="radio_filter">
									<input type="radio" name="sql_type_2" id="test2" value="당첨"><label for="test2">당첨</label>
								</p>
								<p class="radio_filter">
									<input type="radio" name="sql_type_2" id="test3" value="낙첨"><label for="test3">낙첨</label>
								</p>
								<button class="w91" type="button" onClick="fnCngTurn2_2()">검색하기</button>
							</form>
						</div>
					</div>
					<div id="my_lotto2">
						<?
							
							include_once(G5_PATH."/sub/ajax.my_lotto2.php");
						?>
					</div>
					<script>
					function fnCngTurn2_2(){
						v = $("#turn_2").val();

						$.ajax({
							type: "POST",
							url: "<?=G5_URL?>/sub/ajax.my_lotto2.php",
							data: {turn : v, type : $("input:radio[name='sql_type_2']:checked").val()}, 
							cache: false,
							async: false,
							contentType : "application/x-www-form-urlencoded; charset=UTF-8",
							success: function(data) {
								$("#my_lotto2").html(data);
							}
						});
						return false;
					}
					</script>

					
				</div>

			</div>

			<div class="s3_tit">나의 로또 당첨 현황</div>
			<div id="my_lotto" class="my my_perfect" style="margin-bottom:0 !important;">

				<div class="my_lotto_bot my_cont my_cont2 active">
					<div id="my_lotto1">
						<?
							$turn = getTurn()-1;
							include_once(G5_PATH."/sub/ajax.my_lotto1.php");
						?>
					</div>
					<script>
					function fnCngTurn1(v){
						$.ajax({
							type: "POST",
							url: "<?=G5_URL?>/sub/ajax.my_lotto1.php",
							data: {turn : v}, 
							cache: false,
							async: false,
							contentType : "application/x-www-form-urlencoded; charset=UTF-8",
							success: function(data) {
								$("#my_lotto1").html(data);
							}
						});
						return false;
					}
					</script>
				</div>

			</div>
		</div>
	</div>
</section>


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