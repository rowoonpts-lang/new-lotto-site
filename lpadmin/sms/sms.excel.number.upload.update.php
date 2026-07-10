<?php
include_once('./_common.php');
include_once(G5_LADMIN_PATH."/program/lotto.number.php");

// 상품이 많을 경우 대비 설정변경
set_time_limit ( 0 );
ini_set('memory_limit', '50M');

/*error_reporting(E_ALL);

ini_set("display_errors", 1);*/




function only_number($n)
{
    return preg_replace('/[^0-9]/', '', $n);
}


$newTurn = getTurn();
sql_query("alter table `g5_member` add column `free_sms_turn` varchar(50) NOT NULL");


if($_FILES['excelfile']['tmp_name']) {
    $file = $_FILES['excelfile']['tmp_name'];

    include_once(G5_LIB_PATH.'/Excel/reader.php');

    $data = new Spreadsheet_Excel_Reader();

    // Set output Encoding.
    $data->setOutputEncoding('UTF-8');

    $data->read($file);

    //error_reporting(E_ALL ^ E_NOTICE);

    for ($i = 2; $i <= $data->sheets[0]['numRows']; $i++) {
		$j = 1;

        $mb_hp              = str_replace('-','',addslashes($data->sheets[0]['cells'][$i][$j++]));
		$mb_hp              = str_replace(' ','',$mb_hp);
		
		$sql = "select * from g5_member where 1=1 and mb_hp = '{$mb_hp}' and (free_sms_turn != '{$newTurn}' or free_sms_turn is null) limit 1";
		$row = sql_fetch($sql);

		$mb_hp = $row['mb_hp'];
		$mb_id = $row['mb_id'];
		$mb_type = $row['mb_type'];
		if($mb_id){
			

			fnGetNumber($mb_id, $cnt, $type = 0, $mb_hp, true, false, $mb_type, true);

			
			$sql2 = "update g5_member set free_sms_turn = '{$newTurn}' where 1=1 and mb_id = '{$mb_id}'";
			sql_query($sql2);

			usleep(10000); // 0.1초 지연
		}
	}
	alert("정상적으로 처리되었습니다.");

}else{
	alert("등록된 파일이 없습니다.");
}

?>
