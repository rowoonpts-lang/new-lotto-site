<?
	include_once("_common.php");
	
	$table = "l_turn_".$turn;

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

	/*$sql = "update g5_config set cf_auto2_date = '".date("Y-m-d")."', cf_auto2_ing = '2'";
	sql_query($sql);*/




	$sql = "insert into l_lucky_custom set
				turn = '{$turn}'
				, num1 = '{$num1}'
				, num2 = '{$num2}'
				, num3 = '{$num3}'
				, num4 = '{$num4}'
				, num5 = '{$num5}'
				, num6 = '{$num6}'
				, num7 = '{$num7}'
				, lc_datetime = now()
			";
	sql_query($sql);
	alert("정상적으로 처리되었습니다.");
?>