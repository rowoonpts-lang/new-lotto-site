<?
	include_once("_common.php");

	$sql = "select * from l_ad_list where 1=1 and tel = '".str_replace("-","",$mb_hp)."' order by ll_datetime desc limit 1";
	$row = sql_fetch($sql);

	echo $row[lu_code];
?>