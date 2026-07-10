<?
	include_once("_common.php");

	$sql = "select * from g5_config limit 1";
	$row = sql_fetch($sql);
	
	$sw = 0;
	$weekAry = explode("|",$config['cf_auto6_week']);

	// 스케줄링에 포함되어 있다면
	$w = date("w");
	//$w = 1;
	
	if(in_array($w, $weekAry)){
		if($row[cf_auto6_date] != date("Y-m-d")){
			$sql = "update g5_config set cf_auto6_ing = '1' ";
			sql_query($sql);
			if($row[cf_auto6_time] < date("H:i:s")){
				$sw = 1;
			}
		}else{
			if($row[cf_auto6_time] < date("H:i:s")){
				if($row[cf_auto6_ing] != 2){
					$sw = 1;
				}
			}
		}
	}	

	echo $sw;
?>