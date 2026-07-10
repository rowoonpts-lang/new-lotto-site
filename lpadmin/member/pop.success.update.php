<?
	include_once("_common.php");

	$sql = "select * from l_turn_{$turn} where 1=1 and mb_id = '{$mb_id}' and result = '낙첨' limit 1";
	$row = sql_fetch($sql);

	$sql = "update l_turn_{$turn} set
				num1 = '{$test1}'
				, num2 = '{$test2}'
				, num3 = '{$test3}'
				, num4 = '{$test4}'
				, num5 = '{$test5}'
				, num6 = '{$test6}'
				, result = '{$test_class}'
			where 1=1
				and lt_id = '{$row['lt_id']}'
			";
	sql_query($sql);

	alert("정상적으로 처리되었습니다.");
?>