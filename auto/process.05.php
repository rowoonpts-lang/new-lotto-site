<?
	error_reporting(E_ALL);
	ini_set("display_errors", 1);

	include_once("_common.php");
	$basename=basename($_SERVER["PHP_SELF"]);

	include_once(G5_LADMIN_PATH."/program/lotto.number.php");
	$turn = getTurn();


	$sql_common = "
					and a.mb_id = b.mb_id
					and b.mb_type = '무료회원'
					and b.mb_leave_date = ''
					and (a.recent_free_date is null or (a.recent_free_date != '".date("Y-m-d")."' and a.recent_free_date != '".date("Y-m-d", strtotime("-1 day"))."'))
					and substr(b.mb_datetime,1,10) >= '".date("Y-m-d", strtotime("-6 month"))."'
					and (free_sms_turn != '{$turn}' or free_sms_turn is null) 
					";
	// 번호받는 사람 조건식
	$sql = "select *, b.mb_type
			from g5_member_etc a, g5_member b
			where 1=1 
				{$sql_common}
			limit 1
			";
	$row = sql_fetch($sql);

	
	if($row['mb_id']){
		// 문자로 번호보내기
		
		$num_cnt = 10;
		
		if($row['free_num_date'] >= date("Y-m-d") && $row['free_num_qty'] > 0){
			$num_cnt = $row['free_num_qty'];
			fnGetNumber($row['mb_id'], $num_cnt, $type = 0, $row['mb_hp_etc'], false, false, $row['mb_type']);
		}else{
			fnGetNumber($row['mb_id'], $num_cnt, $type = 0, $row['mb_hp_etc'], false, false, $row['mb_type']);
		}
		
		

		$sql = "update g5_member_etc set recent_free_date = '".date("Y-m-d")."', recent_free_datetime = now(), recent_turn = '{$turn}' where mb_id = '{$row['mb_id']}'";		

		sql_query($sql);
	}

	$sql = "select count(a.mb_id) cnt from g5_member_etc a, g5_member b where 1=1 {$sql_common}";
	$row = sql_fetch($sql);
	
	$msg = "";
	$msg = "process Ing - Left Count : ".$row['cnt'];

	echo "<script>parent.fnSetBoard('".$msg."');</script>";

	if($row['cnt'] < 1){
		$msg = "===================== 05 Process End ".date('Y-m-d H:i:s')." =====================";
		$sql = "update g5_config set cf_auto5_date = '".date("Y-m-d")."', cf_auto5_ing = '2'";
		sql_query($sql);

		echo "<script>parent.fnSetBoard('".$msg."');</script>";
	}
	if($row['cnt'] > 0){
		echo "<script>setTimeout(function(){location.href='./".$basename."';},100);</script>";
	}

?>