<?
	include_once("_common.php");
	
	$filter2 = array();
	if(str_replace("","",$f_filter2) != ""){
		$filter2 = explode(",", $f_filter2);
	}
	
	if(count($filter2) > 0){
		if(count($filter2) != 2){
			// 총합 갯수 이상
			echo "0000";
			return false;
		}else{
			if($filter2[0] < 90 || $filter2[1] > 180 || !$filter2[0] || !$filter2[1]){
				
				//허용범위초과
				echo "0001";
				return false;
			}
		}
	}
	/*if($f_filter4 >= 6){
		$f_filter4 = "";
	}*/
	$f_filter4_text = "";

	for($i=0; $i < count($f_filter4); $i++){
		if($f_filter4_text){$f_filter4_text.=",";}
		$f_filter4_text .= $f_filter4[$i];
	}

	
	$f_filter5_text = "";

	for($i=0; $i < count($f_filter5); $i++){
		if($f_filter5_text){$f_filter5_text.=",";}
		$f_filter5_text .= $f_filter5[$i];
	}

	$f_filter6_text = "";

	for($i=0; $i < count($f_filter6); $i++){
		if($f_filter6_text){$f_filter6_text.=",";}
		$f_filter6_text .= $f_filter6[$i];
	}

	$f_filter7_text = "";

	for($i=0; $i < count($f_filter7); $i++){
		if($f_filter7_text){$f_filter7_text.=",";}
		$f_filter7_text .= $f_filter7[$i];
	}

	sql_query("alter table `l_filter` add column `f_filter6` varchar(50) NOT NULL");
	sql_query("alter table `l_filter` add column `f_filter7` varchar(50) NOT NULL");

	$sql = "update l_filter set 
				f_filter1 = '".str_replace(" ","",$f_filter1)."'
				, f_filter2 = '".str_replace(" ","",$f_filter2)."'
				, f_filter3 = '".$f_filter3."'
				, f_filter4 = '".$f_filter4_text."'
				, f_filter5 = '".$f_filter5_text."'
				, f_filter6 = '".$f_filter6_text."'
				, f_filter7 = '".$f_filter7_text."'
			where 1=1
				and f_id = '{$f_id}'
			";
	sql_query($sql);
	echo "1111";
?>