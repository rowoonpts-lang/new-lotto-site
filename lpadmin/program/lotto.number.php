<?
	include_once("_common.php");

	$newTurn = getTurn();
	
	function fnGetNumber($mb_id, $cnt, $type = 0, $mb_hp = "", $sms = true, $log = true, $mb_type="무료조합",$direct= false){
		global $newTurn;
		global $member;
		global $config;
		$lotto = array();
		


		$sql_filter = "select count(*) cnt from l_filter_temp where 1=1 and type = '{$mb_type}' and st_tp = 1 order by lf_id asc";
		$row_filter = sql_fetch($sql_filter);
		if($row_filter['cnt'] > $cnt){

			$sql_filter = "select * from l_filter_temp where 1=1 and type = '{$mb_type}' and st_tp = 1 order by rand() limit {$cnt}";
			$result_filter = sql_query($sql_filter);
			$randLfid = "";
			for($i=0; $row_filter = sql_fetch_array($result_filter); $i++){
				if($randLfid){$randLfid.=",";}
				$randLfid .= $row_filter['lf_id'];
				
				$lotto[$i][0] = $row_filter['num1'];
				$lotto[$i][1] = $row_filter['num2'];
				$lotto[$i][2] = $row_filter['num3'];
				$lotto[$i][3] = $row_filter['num4'];
				$lotto[$i][4] = $row_filter['num5'];
				$lotto[$i][5] = $row_filter['num6'];
			}
			sql_query("update l_filter_temp set st_tp = '0' where 1=1 and lf_id in ({$randLfid})");
		}else{
			// 알고리즘 넣으세요
			if($type == 0){
				for ($i = 0; $i < $cnt; $i++) {
					$lott_num = range(1, 45); // range함수를 통해 1~45까지의 수를 배열로 생성
					shuffle($lott_num);  //배열 변수를 랜덤하게 뒤섞는다
					for ($j = 0; $j < 6; $j++) {
						$lotto[$i][$j] = $lott_num[$j];
					}
					/* 오름차순 정렬 sort 함수*/
					sort($lotto[$i]);
					

					// 3연번 안나오게 처리
					$un = 0; 
					if($lotto[$i][0]+1 == $lotto[$i][1]){$un++;}
					if($lotto[$i][1]+1 == $lotto[$i][2]){$un++;}
					if($lotto[$i][2]+1 == $lotto[$i][3]){$un++;}
					if($lotto[$i][3]+1 == $lotto[$i][4]){$un++;}
					if($lotto[$i][4]+1 == $lotto[$i][5]){$un++;}
					if($lotto[$i][5]+1 == $lotto[$i][6]){$un++;}

					//echo $un."=".$lotto[$i][0]."/".$lotto[$i][1]."/".$lotto[$i][2]."/".$lotto[$i][3]."/".$lotto[$i][4]."/".$lotto[$i][5]."/".$lotto[$i][6]."<br>";
					if($un >= 2){
						$i--;
					}
				}
			}
			// 알고리즘 넣으세요
		}
		
	
		

		// 생성된 번호 DB에 인서트
		// 테이블이 없다면 생성
		$table_name = "l_turn_".$newTurn;
		$result_exist = sql_query("SHOW TABLES LIKE '".$table_name."'");
		$row_exist = sql_fetch_array($result_exist); 

		if(!$row_exist){
			fnCreateTurnTable($table_name);
		}

		$msg = $newTurn."회차";
		
		if($mb_hp == ""){
			$sql = "select * from g5_member where mb_id = '{$mb_id}'";
			$row = sql_fetch($sql);
			$mb_hp = $row['mb_hp'];
		}

		$addQuery = "";
		sql_query("alter table {$table_name} add column `direct_yn` varchar(10) NOT NULL");

		if($direct){
			$addQuery .= " , direct_yn = 'Y' ";
		}

		$is_create = false;
		for($i=0; $i < count($lotto); $i++){
			$sql = "insert into {$table_name} set
						mb_id = '{$mb_id}'
						, mb_hp = '{$mb_hp}'
						, mb_type = '{$mb_type}'
						, turn = '{$newTurn}'
						, num1 = '{$lotto[$i][0]}'
						, num2 = '{$lotto[$i][1]}'
						, num3 = '{$lotto[$i][2]}'
						, num4 = '{$lotto[$i][3]}'
						, num5 = '{$lotto[$i][4]}'
						, num6 = '{$lotto[$i][5]}'
						, lt_datetime = now()
						{$addQuery}
					";
			sql_query($sql);
			$msg .= "\n".($i+1).") ".$lotto[$i][0].",".$lotto[$i][1].",".$lotto[$i][2].",".$lotto[$i][3].",".$lotto[$i][4].",".$lotto[$i][5];
			if(($i+1)%5 == 0 && count($lotto) != ($i+1)){
				$msg .= "\n";
			}

			$is_create = true;
		}
		// 200928 스팸으로 주소도 빼기
		//$msg .= "\n".$config['cf_url'];


		
		if($sms == true && $is_create == true){
			//fnSendOneshot($config['cf_oneshot_tel'], $mb_hp, $msg , $config['cf_oneshot_080']);
			fnSendOneshot($config['cf_oneshot_tel'], $mb_hp, $msg , $config['cf_oneshot_080'], false, '', false);
		}

		if($log == true && $is_create == true){
			if($member[mb_id]){
				fnSetLog($member[mb_id],$mb_id.'님에게 수동조합 '.$cnt.'개를 보냈습니다.');
			}
		}

	}


	

	
?>