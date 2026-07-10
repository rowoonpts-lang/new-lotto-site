<?
	include_once("_common.php");
	if($t == "set"){
		$t_date = $e;
		$d_day = intval((strtotime($t_date) - strtotime($s)) / 86400);
		echo $d_day;
	}
	if($t == "chg"){
		if(!$s){
			$s = date("Y-m-d");
		}
		$e = date("Y-m-d", strtotime($s."+".$l."day"));

		echo json_encode(array('s'=>$s, 'e'=>$e));
	}
?>