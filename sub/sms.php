<?
	include_once(G5_LIB_PATH.'/icode.sms.lib.php'); 
	$sms_content = '['.$wr_10.'] '.$wr_name.'님께서 상담 신청하셨습니다.'; 

	// 보내는사람 
	$send_number = preg_replace('/[^0-9]/', '', '025169902'); 
	// 받는사람 
	$recv_number = preg_replace('/[^0-9]/', '', '01094600127'); 

	if($recv_number) { 
	$SMS = new SMS; // SMS 연결 
	$SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $config['cf_icode_server_port']); 
	$SMS->Add($recv_number, $send_number, $config['cf_icode_id'], iconv("utf-8", "euc-kr", stripslashes($sms_content)), ""); 
	$SMS->Send(); 
	}
?>