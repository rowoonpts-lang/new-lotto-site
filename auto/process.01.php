<?
	error_reporting(E_ALL);
	ini_set("display_errors", 1);

	include_once("_common.php");
	$basename=basename($_SERVER["PHP_SELF"]);



	$w = date("w");
	//$w = 1;
	$today_col = "";
	switch($w){
		case "1": $today_col = "num_mon"; break;
		case "2": $today_col = "num_tue"; break;
		case "3": $today_col = "num_wed"; break;
		case "4": $today_col = "num_thur"; break;
		case "5": $today_col = "num_fri"; break;
		case "6": $today_col = "num_sat"; break;
		default : $today_col = ""; break;
	}
	
	$sql_common = "
					and a.mb_id = b.mb_id
					and a.start_date <= substr(now(),1,10) 
					and a.end_date >= substr(now(),1,10) 
					and {$today_col} > 0
					and (a.recent_auto_date is null or a.recent_auto_date != '".date("Y-m-d")."')
					";
	// 번호받는 사람 조건식
	$sql = "select *, b.mb_type
			from g5_member_etc a, g5_member b
			where 1=1 
				{$sql_common}
			limit 1
			";
	$row = sql_fetch($sql);

	// 문자로 번호보내기
	include_once(G5_LADMIN_PATH."/program/lotto.number.php");
	$num_cnt = $row[$today_col];
	$week_num = $row['num_mon']+$row['num_tue']+$row['num_wed']+$row['num_thur']+$row['num_fri']+$row['num_sat'];
	$left_num = $week_num - $row['use_num'];
	if($left_num < $num_cnt){
		$num_cnt = $left_num;
	}
	//fnGetNumber($row['mb_id'], $num_cnt, $type = 0, $row['mb_hp_etc'], true, false, $row['mb_type']);
	fnGetNumber($row['mb_id'], $num_cnt, $type = 0, $row['mb_hp'], true, false, $row['mb_type']);
	$turn = getTurn();

	$sql = "update g5_member_etc set use_num = use_num + {$num_cnt} , recent_auto_date = '".date("Y-m-d")."', recent_auto_datetime = now(), recent_turn = '{$turn}' where mb_id = '{$row['mb_id']}'";

	

	sql_query($sql);

	$sql = "select count(a.mb_id) cnt from g5_member_etc a, g5_member b where 1=1 {$sql_common}";
	$row = sql_fetch($sql);
	
	$msg = "";
	$msg = "process Ing - Left Count : ".$row['cnt'];

	echo "<script>parent.fnSetBoard('".$msg."');</script>";

	if($row['cnt'] < 1){
		$msg = "===================== 01 Process End ".date('Y-m-d H:i:s')." =====================";
		$sql = "update g5_config set cf_auto1_date = '".date("Y-m-d")."', cf_auto1_ing = '2'";
		sql_query($sql);

		echo "<script>parent.fnSetBoard('".$msg."');</script>";
	}
	if($row['cnt'] > 0){
		echo "<script>setTimeout(function(){location.href='./".$basename."';},1000);</script>";
	}

?>