<?
	include_once("_common.php");
	$cnt = $create;
	
	$sql = "
		CREATE TABLE `l_turn_temp` (
			`lt_id` INT(11) NOT NULL AUTO_INCREMENT,
			`mb_id` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
			`mb_hp` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
			`mb_type` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
			`turn` INT(11) NULL DEFAULT NULL,
			`num1` INT(11) NULL DEFAULT NULL,
			`num2` INT(11) NULL DEFAULT NULL,
			`num3` INT(11) NULL DEFAULT NULL,
			`num4` INT(11) NULL DEFAULT NULL,
			`num5` INT(11) NULL DEFAULT NULL,
			`num6` INT(11) NULL DEFAULT NULL,
			`result` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
			`sms_yn` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
			`lt_datetime` DATETIME NULL DEFAULT NULL,
			PRIMARY KEY (`lt_id`) USING BTREE,
			INDEX `turn` (`turn`) USING BTREE,
			INDEX `mb_id` (`mb_id`) USING BTREE
		)
		COLLATE='utf8_general_ci'
		ENGINE=InnoDB
		ROW_FORMAT=DYNAMIC
		AUTO_INCREMENT=1
		";
	sql_query($sql);

	for ($i = 0; $i < $cnt; $i++) {
		$lott_num = range(1, 45); // range함수를 통해 1~45까지의 수를 배열로 생성
		shuffle($lott_num);  //배열 변수를 랜덤하게 뒤섞는다
		for ($j = 0; $j < 6; $j++) {
			$lotto[$i][$j] = $lott_num[$j];
		}
		/* 오름차순 정렬 sort 함수*/
		sort($lotto[$i]);
	}
	
	$table_name = "l_turn_temp";

	for($i=0; $i < count($lotto); $i++){
		$sql = "insert into {$table_name} set
					  num1 = '{$lotto[$i][0]}'
					, num2 = '{$lotto[$i][1]}'
					, num3 = '{$lotto[$i][2]}'
					, num4 = '{$lotto[$i][3]}'
					, num5 = '{$lotto[$i][4]}'
					, num6 = '{$lotto[$i][5]}'
					, lt_datetime = now()
				";
		sql_query($sql);
	}

	if($cnt_share == $iii){
		echo "ok";
	}
?>