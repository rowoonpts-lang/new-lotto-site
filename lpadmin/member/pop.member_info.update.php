<?php
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.sub.php");

	$distribution_day = isset($_POST['distribution_day'])
		? trim((string) $_POST['distribution_day'])
		: 'thur';

	$distribution_qty = isset($_POST['distribution_qty'])
		? (int) $_POST['distribution_qty']
		: 20;

	$distribution_days = array(
		'mon' => 'num_mon',
		'tue' => 'num_tue',
		'wed' => 'num_wed',
		'thur' => 'num_thur',
		'fri' => 'num_fri',
	);

	if (!isset($distribution_days[$distribution_day])) {
		alert("로또 배분 요일이 올바르지 않습니다.");
		exit;
	}

	if ($distribution_qty < 1) {
		alert("로또 배분 수량은 1개 이상이어야 합니다.");
		exit;
	}

	$distribution_values = array(
		'num_mon' => 0,
		'num_tue' => 0,
		'num_wed' => 0,
		'num_thur' => 0,
		'num_fri' => 0,
		'num_sat' => 0,
	);

	$distribution_values[$distribution_days[$distribution_day]] = $distribution_qty;

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
	

	sql_query("alter table `g5_member_etc` add column `free_num_qty` int NOT NULL");
	sql_query("alter table `g5_member_etc` add column `free_num_date` varchar(50) NOT NULL");

	$sql = "
				update g5_member_etc set
					  num_mon = '{$distribution_values['num_mon']}'
					, num_tue = '{$distribution_values['num_tue']}'
					, num_wed = '{$distribution_values['num_wed']}'
					, num_thur = '{$distribution_values['num_thur']}'
					, num_fri = '{$distribution_values['num_fri']}'
					, num_sat = '{$distribution_values['num_sat']}'
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