<?
	include_once("_common.php");


	$cf080 = $config['cf_oneshot_080'];
	$sms_content = "\n무통장 입금정보\n".$config['cf_mu_num'];
	fnSendOneshot($config['cf_oneshot_tel'], $member['mb_hp'], $sms_content , $cf080);
	alert("정상적으로 처리되었습니다.");
?>