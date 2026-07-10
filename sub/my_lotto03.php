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
				url: "/sub/ajax.my_lotto2.php",
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