<?
	include_once("_common.php");
	$type = "실버에디션6";
	if($f_type){
		$type = $f_type;
	}
	
	$sql = "select * from l_filter where 1=1 and f_type = '{$type}'";
	//$sql = "select * from l_filter where 1=1 and f_type = '실버체험'";
	$row = sql_fetch($sql);
	$filter = array();
	
	$tmp1 = explode(",",$row['f_filter1']);//제외수
	$tmp2 = explode(",",$row['f_filter2']);//총합
	$tmp3 = $row['f_filter3'];//AF
	$tmp4 = $row['f_filter4'];//홀짝
	$tmp5 = $row['f_filter5'];//저고
	$tmp6 = $row['f_filter6'];//연번
	$tmp7 = $row['f_filter7'];//끝수
	if($tmp1[0]){
		$filter['제외수'] = $tmp1;
	}
	if($tmp2[0]){
		$filter['총합'] = $tmp2;
	}
	$filter['AF'] = $tmp3;
	$filter['홀짝'] = $tmp4;
	$filter['저고'] = $tmp5;
	$filter['연번'] = $tmp6;
	$filter['끝수'] = $tmp7;
	
	if(!$f_type){
		$list = fnFilter(10, $filter, $type);
	}else{
		$list = fnFilter(50000, $filter, $type);
		alert("정상적으로 생성되었습니다.");
	}
	//print_r($list);
?>