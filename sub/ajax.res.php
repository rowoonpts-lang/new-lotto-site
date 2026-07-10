<?
	include_once("_common.php");

	$lr_name = sql_escape_string(trim($lr_name));
	$lr_type = sql_escape_string(trim($lr_type));
	$lr_etc  = sql_escape_string(trim($lr_etc));
	$lr_hp1  = preg_replace('/[^0-9]/', '', $lr_hp1);
	$lr_hp2  = preg_replace('/[^0-9]/', '', $lr_hp2);
	$lr_hp3  = preg_replace('/[^0-9]/', '', $lr_hp3);

	$lr_hp = $lr_hp1 . $lr_hp2 . $lr_hp3;

	$sql = "insert into l_res set 
				lr_name = '{$lr_name}',
				lr_hp = '{$lr_hp}',
				lr_type = '{$lr_type}',
				lr_etc = '{$lr_etc}',
				ip = '" . $_SERVER["REMOTE_ADDR"] . "',
				lr_datetime = now()";
	sql_query($sql);
?>
