<?
	include_once("_common.php");

	// 사용자 입력값 필터링 및 이스케이프 처리
	$lr_name = sql_escape_string(trim($lr_name));
	$lr_type = sql_escape_string(trim($lr_type));
	$lr_etc  = sql_escape_string(trim($lr_etc));

	// 휴대폰 번호 숫자만 추출
	$lr_hp1 = preg_replace('/[^0-9]/', '', $lr_hp1);
	$lr_hp2 = preg_replace('/[^0-9]/', '', $lr_hp2);
	$lr_hp3 = preg_replace('/[^0-9]/', '', $lr_hp3);
	$lr_hp = $lr_hp1 . $lr_hp2 . $lr_hp3;

	$ip = sql_escape_string($_SERVER["REMOTE_ADDR"]);

	$sql = "insert into l_res set 
				lr_name = '{$lr_name}',
				lr_hp = '{$lr_hp}',
				lr_type = '{$lr_type}',
				lr_etc = '{$lr_etc}',
				ip = '{$ip}',
				lr_datetime = now()";
	sql_query($sql);
?>
