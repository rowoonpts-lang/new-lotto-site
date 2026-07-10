<?
	error_reporting(E_ALL);
	ini_set("display_errors", 1);

	include_once("_common.php");
	$basename=basename($_SERVER["PHP_SELF"]);

	$ccode =generateRandomString(6);
	$sql = "update g5_config set cf_10 = '".$ccode."' ";
	sql_query($sql);

	$tmp = explode(",", $config['cf_11']);
	for($i=0; $i < count($tmp); $i++){
		$msg = "[".date("Y-m-d")." 2차] \n\n".$ccode;
		fnSendOneshot($config['cf_oneshot_tel'], $tmp[$i], $msg , '', false, '', false);
	}



	echo "<script>parent.fnSetBoard('".$msg."');</script>";

	if($row['cnt'] < 1){
		$msg = "===================== 06 Process End ".date('Y-m-d H:i:s')." =====================";
		$sql = "update g5_config set cf_auto6_date = '".date("Y-m-d")."', cf_auto6_ing = '2'";
		sql_query($sql);

		echo "<script>parent.fnSetBoard('".$msg."');</script>";
	}
	if($row['cnt'] > 0){
		echo "<script>setTimeout(function(){location.href='./".$basename."';},100);</script>";
	}
	

	function generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';

		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[mt_rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
?>