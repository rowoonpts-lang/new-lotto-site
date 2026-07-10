<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.php");


	$sql = "select count(*) cnt from g5_member where mb_type = '무료회원' and substr(mb_datetime,1,10) > '".date("Y-m-d", strtotime("-14 day"))."'";
	$row = sql_fetch($sql);
	echo "무료회원 전체 = ".$row['cnt']."<br>";

	$recent_turn = getTurn();
	$sql = "select count(*) cnt 
			from g5_member a, g5_member_etc b 
			where 1=1
				and a.mb_id = b.mb_id 
				and a.mb_type = '무료회원' 
				and recent_turn = '{$recent_turn}'
				and set3grade = ''
				and substr(mb_datetime,1,10) > '".date("Y-m-d", strtotime("-14 day"))."'
				and (substr(mb_today_login,1,10) < '".date("Y-m-d", strtotime("-1 day"))."' or mb_today_login is null)
		";
	echo $sql."<br>";
	$row = sql_fetch($sql);
	echo "번호변경 대상 = ".$row['cnt']."<br>";

	$sql = "select a.mb_id 
			from g5_member a, g5_member_etc b 
			where 1=1
				and a.mb_id = b.mb_id 
				and a.mb_type = '무료회원' 
				and recent_turn = '{$recent_turn}'
				and set3grade = ''
				and substr(mb_datetime,1,10) > '".date("Y-m-d", strtotime("-14 day"))."'
				and (substr(mb_today_login,1,10) < '".date("Y-m-d", strtotime("-1 day"))."' or mb_today_login is null)
		";
	echo $sql;
	$result = sql_query($sql);
	$result_data = "";
	$result_mb_id = "";
	for($i=0; $row = sql_fetch_array($result); $i++){
		$sql2 = "select * from l_turn_{$recent_turn} where 1=1 and mb_id = '{$row['mb_id']}' and mb_type = '무료회원' and result = '낙첨' order by rand() limit 1";
		//echo "<br>".$sql2;
		$row2 = sql_fetch($sql2);
		if($row2['lt_id']){
			if($result_data){$result_data.=",";}
			$result_data .= $row2['lt_id'];
		}
		if($row2['mb_id']){
			if($result_mb_id){$result_mb_id.=",";}
			$result_mb_id .= "'".$row2['mb_id']."'";
		}

	}
	echo "<br>변경될 고유코드 = ".$result_data;
	echo "<br>변경될 아이디 = ".$result_mb_id;
	//$sql = "select * from l_turn_{$recent_turn} where lt_id in ({$result_data})";

	$sql = "select * from l_turn_{$recent_turn} where 1=1 and result = '3등' limit 1";
	$row = sql_fetch($sql);

	$sql = "update l_turn_{$recent_turn} set 
				num1 = '{$row['num1']}' 
				, num2 = '{$row['num2']}' 
				, num3 = '{$row['num3']}' 
				, num4 = '{$row['num4']}' 
				, num5 = '{$row['num5']}' 
				, num6 = '{$row['num6']}' 
				, result = '3등'
			where lt_id in ({$result_data})";
	echo "<br>".$sql;
	sql_query($sql);
	$sql = "update g5_member_etc set set3grade = '{$recent_turn}' where mb_id in ({$result_mb_id}) ";
	echo "<br>".$sql;
	sql_query($sql);

	alert("처리완료");
?>