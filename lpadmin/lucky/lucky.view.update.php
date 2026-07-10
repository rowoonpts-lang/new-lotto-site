<?
	include_once("_common.php");

	sql_query("alter table g5_config add column `cf_lucky_1` varchar(50) NOT NULL");
	sql_query("alter table g5_config add column `cf_lucky_2` varchar(50) NOT NULL");
	sql_query("alter table g5_config add column `cf_lucky_3` varchar(50) NOT NULL");

	sql_query("alter table g5_config add column `cf_etc_1` varchar(50) NOT NULL");
	sql_query("alter table g5_config add column `cf_etc_2` varchar(50) NOT NULL");
	sql_query("alter table g5_config add column `cf_etc_3` varchar(50) NOT NULL");
	sql_query("alter table g5_config add column `cf_etc_1_1` varchar(50) NOT NULL");
	sql_query("alter table g5_config add column `cf_etc_2_1` varchar(50) NOT NULL");
	sql_query("alter table g5_config add column `cf_etc_3_1` varchar(50) NOT NULL");

	$sql = "update g5_config set 
				cf_lucky_1 = '{$cf_lucky_1}'
				,cf_lucky_2 = '{$cf_lucky_2}'
				,cf_lucky_3 = '{$cf_lucky_3}' 

				,cf_etc_1 = '{$cf_etc_1}' 
				,cf_etc_2 = '{$cf_etc_2}' 
				,cf_etc_3 = '{$cf_etc_3}' 
				,cf_etc_1_1 = '{$cf_etc_1_1}' 
				,cf_etc_2_1 = '{$cf_etc_2_1}' 
				,cf_etc_3_1 = '{$cf_etc_3_1}' 
			where 1=1 
			";
	sql_query($sql);

	alert("정상적으로 저장되었습니다.");
?>