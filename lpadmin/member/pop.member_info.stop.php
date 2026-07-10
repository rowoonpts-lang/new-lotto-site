<?
	include_once("_common.php");

	$mb_id = base64_decode($mb_id);

	sql_query("alter table g5_member_etc add column stop_start_date varchar(50) NOT NULL");

	if($type == "hold"){

		$sql = "select * from g5_member_etc where mb_id = '{$mb_id}'";
		$row = sql_fetch($sql);

		//$left_day = intval((strtotime($row[end_date]) - strtotime($row[start_date])) / 86400);
		$left_day = intval((strtotime($row[end_date]) - strtotime(date("Y-m-d"))) / 86400);
		if($left_day){
			$sql = "update g5_member_etc set stop_start_date = start_date, left_day = '{$left_day}', hold_datetime = now(), start_date = null, end_date = null where 1=1 and mb_id = '{$mb_id}'";
			sql_query($sql);
		}
	}
	if($type == "start"){
		$sql = "select * from g5_member_etc where mb_id = '{$mb_id}'";
		$row = sql_fetch($sql);

		//$left_day = intval((strtotime($row[end_date]) - strtotime($row[start_date])) / 86400);
		$left_day = intval((strtotime($row[end_date]) - strtotime(date("Y-m-d"))) / 86400);

		//$sql = "update g5_member_etc set left_day = '0', hold_datetime = null, start_date = now(), end_date = '".date("Y-m-d H:i:s", strtotime("+".$row['left_day']."day"))."' where 1=1 and mb_id = '{$mb_id}'";
		$sql = "update g5_member_etc set left_day = '0', hold_datetime = null, start_date = stop_start_date, end_date = '".date("Y-m-d H:i:s", strtotime("+".$row['left_day']."day"))."' where 1=1 and mb_id = '{$mb_id}'";
		sql_query($sql);
	}

	alert("정상적으로 처리되었습니다.");
	/*include_once("_common.php");

	$mb_id = base64_decode($mb_id);
	if($type == "hold"){

		$sql = "select * from g5_member_etc where mb_id = '{$mb_id}'";
		$row = sql_fetch($sql);

		$left_day = intval((strtotime($row[end_date]) - strtotime($row[start_date])) / 86400);
		if($left_day){
			$sql = "update g5_member_etc set left_day = '{$left_day}', hold_datetime = now(), start_date = null, end_date = null where 1=1 and mb_id = '{$mb_id}'";
			sql_query($sql);
		}
	}
	if($type == "start"){
		$sql = "select * from g5_member_etc where mb_id = '{$mb_id}'";
		$row = sql_fetch($sql);

		$left_day = intval((strtotime($row[end_date]) - strtotime($row[start_date])) / 86400);

		$sql = "update g5_member_etc set left_day = '0', hold_datetime = null, start_date = now(), end_date = '".date("Y-m-d H:i:s", strtotime("+".$row['left_day']."day"))."' where 1=1 and mb_id = '{$mb_id}'";
		sql_query($sql);
	}

	alert("정상적으로 처리되었습니다.");*/
?>