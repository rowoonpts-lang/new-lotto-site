<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.sub.php");
	include_once(G5_LADMIN_PATH."/program/lotto.number.php");

	if($mb_id != $mb_id_temp || $mb_hp != $mb_hp_temp){

		if($mb_hp != $mb_hp_temp){
			setMemberInfo($mb_id_temp, '', $mb_hp);
			
		}

		if($mb_id != $mb_id_temp){
			setMemberInfo($mb_id_temp, $mb_id, '');
		}
	}

	$common_sql = "";
	if(!$start_date){
		$common_sql .= " , start_date = null ";
	}else{
		$common_sql .= " , start_date = '{$start_date}' ";
	}
	if(!$end_date){
		$common_sql .= " , end_date = null ";
	}else{
		$common_sql .= " , end_date = '{$end_date}' ";
	}
	
	$sql = "select * from g5_member_etc where 1=1 and mb_id = '{$mb_id}'";
	$row = sql_fetch($sql);
	
	$weekTotal = $row[num_mon]+$row[num_tue]+$row[num_wed]+$row[num_thur]+$row[num_fri]+$row[num_sat];
	if($weekTotal >= $left_num ){
		
		if($left_num > 0){
			$common_sql .= " , use_num = '".($weekTotal-$left_num)."' ";
		}else{
			$common_sql .= " , use_num = '{$weekTotal}' ";
		}
	}else{
		$common_sql .= " , use_num = '0' ";
	}
	

	sql_query("alter table `g5_member_etc` add column `free_num_qty` int NOT NULL");
	sql_query("alter table `g5_member_etc` add column `free_num_date` varchar(50) NOT NULL");

	$sql = "
				update g5_member_etc set
					  num_mon = '{$num_mon}'
					, num_tue = '{$num_tue}'
					, num_wed = '{$num_wed}'
					, num_thur = '{$num_thur}'
					, num_fri = '{$num_fri}'
					, num_sat = '{$num_sat}'
					, free_num_qty = '{$free_num_qty}'
					, free_num_date = '{$free_num_date}'
					{$common_sql}
				where 1=1
					and mb_id = '{$mb_id}'
			";

	sql_query($sql);
	
	$common_sql = "";
	if($mb_password){
		$common_sql .= " , mb_password = '".get_encrypt_string($mb_password)."'";
	}

	if($mb_name){
		$common_sql .= " , mb_name = '{$mb_name}' ";
	}

	$sql = "update g5_member set mb_type = '{$mb_type}' {$common_sql } where mb_id = '{$mb_id}'";
	sql_query($sql);
	
	
	if($dr_num > 0){
		$cnt = $dr_num;
		
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
	}



	fnSetLog($member[mb_id],$mb_id.'님의 회원정보를 수정하였습니다.');
	alert("정상적으로 처리되었습니다.", G5_LADMIN_URL."/member/pop.member_info.php?mb_id=".base64_encode($mb_id));
?>
<script>
$(function(){
	/*alert("정상적으로 처리되었습니다.");
	window.opener.location.reload();
	location.href="<?=G5_LADMIN_URL?>/member/pop.member_info.php?mb_id=<?=base64_encode($mb_id)?>";*/
});
</script>