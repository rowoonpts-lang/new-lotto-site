<?
	include_once("_common.php");

	$turn = getTurn();
	$sql = "SELECT b.mb_id, b.mb_hp FROM g5_member_etc a, g5_member b WHERE 1=1 AND mb_type = '무료회원' and a.mb_id = b.mb_id and b.mb_type = '무료회원' and b.mb_leave_date = '' and (a.recent_free_date is null or (a.recent_free_date != '".date("Y-m-d")."' and a.recent_free_date != '".date("Y-m-d", strtotime("-1 day"))."')) ";
	$result = sql_query($sql);
	for($i=0; $row = sql_fetch_array($result); $i++){
		$sql = "update l_turn_temp set
					mb_id = '{$row['mb_id']}'
					, mb_hp = '{$row['mb_hp']}'
					, mb_type = '무료회원'
					, turn = '{$turn}'
					, lt_datetime = now()
				where 1=1
					and mb_id is null
				limit {$cnt}
				";
		sql_query($sql);

		$sql = "update g5_member_etc set recent_free_date = '".date("Y-m-d")."', recent_free_datetime = now(), recent_turn = '{$turn}' where mb_id = '{$row['mb_id']}'";		
		sql_query($sql);
	}
	
	$sql = "delete from l_turn_temp where mb_id is null";
	sql_query($sql);

	$sql = "
			INSERT INTO l_turn_{$turn} (mb_id, mb_hp, mb_type, turn, num1, num2, num3, num4, num5, num6, lt_datetime ) 
			(SELECT mb_id, mb_hp, mb_type, turn, num1, num2, num3, num4, num5, num6, NOW() FROM l_turn_temp WHERE 1=1);
		";
	sql_query($sql);

	$sql = "delete from l_turn_temp ";
	sql_query($sql);

	$sql = "ALTER TABLE l_turn_temp AUTO_INCREMENT = 1";
	sql_query($sql);

	sql_query("alter table `g5_config` add column `free_num_turn` varchar(50) NOT NULL");
	$sql = "update g5_config set free_num_turn = '{$turn}' ";
	sql_query($sql);

	
	echo "ok";
?>