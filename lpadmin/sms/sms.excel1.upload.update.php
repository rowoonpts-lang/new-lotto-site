<?php
include_once('./_common.php');

// 상품이 많을 경우 대비 설정변경
set_time_limit ( 0 );
ini_set('memory_limit', '50M');

/*error_reporting(E_ALL);

ini_set("display_errors", 1);*/




function only_number($n)
{
    return preg_replace('/[^0-9]/', '', $n);
}

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
		
		fnSendOneshot($config['cf_oneshot_tel'], $mb_hp, $msg, $config['cf_oneshot_080'],false,'excel1');

		usleep(100000); // 0.1초 지연
	}
	alert("정상적으로 처리되었습니다.");
}else{
	alert("등록된 파일이 없습니다.");
}

?>
