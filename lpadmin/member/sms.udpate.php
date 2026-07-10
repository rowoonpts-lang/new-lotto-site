<?
	include_once("_common.php");
	$cf080 = "";
	if($chk == "1"){
		$cf080 = $config['cf_oneshot_080'];
	}
	fnSendOneshot($config['cf_oneshot_tel'], $mb_hp, $sms_content , $cf080);
	alert("정상적으로 처리되었습니다.");
?>