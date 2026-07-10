<?
	include_once("_common.php");
	
	include_once(G5_LADMIN_PATH."/program/lotto.number.php");

	fnGetNumber($mb_id, $cnt, 0, '', true, true, $mb_type);
		
	$sql = "select * from g5_member_etc where 1=1 and mb_id = '{$mb_id}'";
	$row = sql_fetch($sql);

	if($row[recent_turn] != $newTurn){
		$sql = "update g5_member_etc set 
					use_num = '{$cnt}' 
					, recent_turn = '{$newTurn}'
				where 1=1
					and mb_id = '{$mb_id}'
				";
		sql_query($sql);
	}else{
		$sql = "update g5_member_etc set 
					use_num = use_num + {$cnt}
				where 1=1
					and mb_id = '{$mb_id}'
				";
		sql_query($sql);
	}

	

?>