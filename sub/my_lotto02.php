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
				url: "/sub/ajax.my_lotto1.php",
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