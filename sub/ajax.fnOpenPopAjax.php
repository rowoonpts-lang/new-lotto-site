<?
	include_once("_common.php");

	$sql = "select count(lrl_id) cnt from l_res_log where 1=1 and mb_id = '{$member[mb_id]}' and lrl_type = '{$lr_type}' and substr(lrl_datetime,1,10) = substr(now(), 1, 10)";
	$row = sql_fetch($sql);

	if($row['cnt'] < 1){
		$sql = "insert into l_res_log set
					mb_id = '{$member[mb_id]}'
					, lrl_type = '{$lr_type}' 
					, lrl_datetime = now()
					, ip = '".$_SERVER["REMOTE_ADDR"]."'
				";
		echo $sql;
		sql_query($sql);
	}
?>