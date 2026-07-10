<?
	error_reporting(E_ALL);
	ini_set("display_errors", 1);

	include_once("_common.php");
	/************************************************/
	// 로또 결과 업데이트
	/************************************************/

	$turn = getTurn();
	

	$table = "l_turn_".$turn;
	//echo $table;
	$list = getLuckyNum($turn);
	print_r($list);

	if($list['returnValue'] != 'fail'){
	
		$num1 = $list['drwtNo1'];
		$num2 = $list['drwtNo2'];
		$num3 = $list['drwtNo3'];
		$num4 = $list['drwtNo4'];
		$num5 = $list['drwtNo5'];
		$num6 = $list['drwtNo6'];
		$num7 = $list['bnusNo'];

		$sql = "
				update {$table} set 
				result = (case (
							(case num1 when {$num1} then 2 when {$num2} then 2 when {$num3} then 2 when {$num4} then 2 when {$num5} then 2 when {$num6} then 2 when {$num7} then 1 else 0 end)
							+(case num2 when {$num1} then 2 when {$num2} then 2 when {$num3} then 2 when {$num4} then 2 when {$num5} then 2 when {$num6} then 2 when {$num7} then 1 else 0 end)
							+(case num3 when {$num1} then 2 when {$num2} then 2 when {$num3} then 2 when {$num4} then 2 when {$num5} then 2 when {$num6} then 2 when {$num7} then 1 else 0 end)
							+(case num4 when {$num1} then 2 when {$num2} then 2 when {$num3} then 2 when {$num4} then 2 when {$num5} then 2 when {$num6} then 2 when {$num7} then 1 else 0 end)
							+(case num5 when {$num1} then 2 when {$num2} then 2 when {$num3} then 2 when {$num4} then 2 when {$num5} then 2 when {$num6} then 2 when {$num7} then 1 else 0 end)
							+(case num6 when {$num1} then 2 when {$num2} then 2 when {$num3} then 2 when {$num4} then 2 when {$num5} then 2 when {$num6} then 2 when {$num7} then 1 else 0 end)
							) when 6 then '5등' when 7 then '5등' when 8 then '4등' when 9 then '4등' when 10 then '3등' when 11 then '2등' when 12 then '1등'  else '낙첨' end
							)
				where 1=1
					and turn = '{$turn}'
			";
		sql_query($sql);

		$sql = "update g5_config set cf_auto2_date = '".date("Y-m-d")."', cf_auto2_ing = '2'";
		sql_query($sql);

		$msg = "===================== 02 Process End ".date('Y-m-d H:i:s')." =====================";
		echo "<script>parent.fnSetBoard('".$msg."');</script>";
	}else{
		$msg = "000000000000000000000 02 Process Fail ".date('Y-m-d H:i:s')." 000000000000000000000";
		echo "<script>parent.fnSetBoard('".$msg."');</script>";
	}

?>