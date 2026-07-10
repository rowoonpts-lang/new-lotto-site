<?
	error_reporting(E_ALL);
	ini_set("display_errors", 1);

	include_once("_common.php");
	/************************************************/
	// 로또 결과 문자발송 
	/************************************************/

	$turn = getTurn()-1;
	$table = "l_turn_".$turn;
	$list = getLuckyNum($turn);
	print_r($list);

	$sql_common = " and result not in ('','낙첨') and sms_yn is null and (mb_type not in ('무료회원') or (mb_type in ('무료회원') and result in ('1등','2등','3등','4등','5등') ))  and mb_id != '01087355438' ";

	if($list['returnValue'] != 'fail'){
	
		$num1 = $list['drwtNo1'];
		$num2 = $list['drwtNo2'];
		$num3 = $list['drwtNo3'];
		$num4 = $list['drwtNo4'];
		$num5 = $list['drwtNo5'];
		$num6 = $list['drwtNo6'];
		$num7 = $list['bnusNo'];

		$resultData = "";
		$resultData .= $num1." ".$num2." ".$num3." ".$num4." ".$num5." ".$num6." 보너스 ".$num7;
		

		$sql = "
				select * 
				from {$table} 
				where 1=1 
					{$sql_common}
				order by lt_id asc
				";
		$result = sql_query($sql);
		$ary = array();
		for($i=0; $row = sql_fetch_array($result); $i++){
			$ary[$row['mb_id']][$row['result']] = $ary[$row['mb_id']][$row['result']] + 1;
			$ary[$row['mb_id']]['mb_id'] = $row['mb_id'];
			$ary[$row['mb_id']]['mb_hp'] = $row['mb_hp'];
			$ary[$row['mb_id']]['mb_type'] = $row['mb_type'];
		}


		$inText = "";
		foreach($ary as $key => $value) {
			$tot = 0;
			$text = "";
			if($value['1등'] > 0){
				$text .= " 1등 ".$value['1등']."개";
				$tot += $value['1등'];
			}
			if($value['2등'] > 0){
				$text .= " 2등 ".$value['2등']."개";
				$tot += $value['2등'];
			}
			if($value['3등'] > 0){
				$text .= " 3등 ".$value['3등']."개";
				$tot += $value['3등'];
			}
			if($value['4등'] > 0){
				$text .= " 4등 ".$value['4등']."개";
				$tot += $value['4등'];
			}
			if($value['5등'] > 0){
				$text .= " 5등 ".$value['5등']."개";
				$tot += $value['5등'];
			}
			
			
			if($tot > 0){
				$msg = "";
				//$msg .= "{$turn}회 당첨결과 ".$resultData." / ".$text." 당첨입니다.\n\n3등이상 당첨되신분들은 당첨금 수령하신이후 구매한 로또영수증과 당첨금 지급내역서 사진을 담당자에게 보내주시면 추첨을 통해 상품권을 발송해드리니 꼭 좀 부탁드립니다!\n";
				$msg .= "{$turn}회 당첨결과 ".$resultData." / ".$text." 당첨입니다.";

				if($value['mb_type'] != '무료회원'){
					if((strpos($msg, '1등') !== false || strpos($msg, '2등') !== false || strpos($msg, '3등') !== false || strpos($msg, '4등') !== false) || ($value['mb_type'] != '무료회원')) {  
						// SMS 발송
						fnSendOneshot($config['cf_oneshot_tel'], $value['mb_hp'], $msg , $config['cf_oneshot_080'],false,"",false);	
					} 
				}else{
					/*if(strpos($msg, '1등') !== false || strpos($msg, '2등') !== false || strpos($msg, ' 3등 ') !== false || strpos($msg, '4등') !== false) {  
						// SMS 발송
						fnSendOneshot($config['cf_oneshot_tel'], $value['mb_hp'], $msg , $config['cf_oneshot_080'],false,"",false);
					} */
				}
				
				$sql = "update g5_member_etc set
							lucky1 = lucky1+'{$value['1등']}'
							, lucky2 = lucky2+'{$value['2등']}'
							, lucky3 = lucky3+'{$value['3등']}'
							, lucky4 = lucky4+'{$value['4등']}'
							, lucky5 = lucky5+'{$value['5등']}'
						where mb_id = '{$value['mb_id']}'
						";
				sql_query($sql);

				if($inText){$inText .= ",";}
				$inText .= "'".$value['mb_id']."'";
			}
		}

		//$sql = "update {$table} set sms_yn = 'y' where 1=1 and result not in ('','낙첨') and mb_id in ({$inText}) where 1=1 {$sql_common} ";
		$sql = "update {$table} set sms_yn = 'y' where 1=1 and mb_id in ({$inText}) {$sql_common} ";
		sql_query($sql);


		$sql = "update g5_config set cf_auto4_date = '".date("Y-m-d")."', cf_auto4_ing = '2'";
		sql_query($sql);

		$msg = "===================== 04 Process End ".date('Y-m-d H:i:s')." =====================";
		echo "<script>parent.fnSetBoard('".$msg."');</script>";
	}else{
		$msg = "000000000000000000000 04 Process Fail ".date('Y-m-d H:i:s')." 000000000000000000000";
		echo "<script>parent.fnSetBoard('".$msg."');</script>";
	}

?>