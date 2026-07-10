<?
	error_reporting(E_ALL);
	ini_set("display_errors", 1);

	include_once("_common.php");
	/************************************************/
	// 로또 리셋
	/************************************************/

	$w = date("w");
	//$w = 1;

	if($w == 0){

		$sql = "update g5_member_etc set use_num = '0'";
		sql_query($sql);

		$sql = "update g5_config set cf_auto3_date = '".date("Y-m-d")."', cf_auto3_ing = '2'";
		sql_query($sql);

		$msg = "===================== 03 Process End ".date('Y-m-d H:i:s')." =====================";
		echo "<script>parent.fnSetBoard('".$msg."');</script>";
		echo $msg; 
	}
?>