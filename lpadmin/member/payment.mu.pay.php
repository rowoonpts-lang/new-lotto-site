<?
	include_once("_common.php");
	$lp_id = $_GET['lp_id'];

	$sql = "select * from l_pay where 1=1 and lp_id = '{$lp_id}'";
	$row = sql_fetch($sql);

	$sql = "update l_pay set lp_status = '입금', lp_pay_datetime = now() where lp_id = '{$lp_id}'";
	sql_query($sql);

	// 결제성공 및 세팅
	$sql = "update g5_member set mb_type = '{$row[lp_type]}' where 1=1 and mb_id = '{$row['mb_id']}'";
	sql_query($sql);


	
	/*$lm_memo = $row[lp_type]." 무통장 결제 입금확인 - ".number_format($row[lp_price])."원";
	fnSetMemo($row[mb_id], $member['mb_id'], '결제', $lm_memo);*/
	$msg = "회원님 요청하신 결제가 정상적으로 완료 되었습니다\n담당자가 ".$config['cf_3']."번으로 연락드릴예정이오니\n전화 꼭 받아주세요! 가입을 감사드립니다!";
	fnSendOneshot($config['cf_oneshot_tel'], $row['mb_hp'], $msg , '');

	alert("정상적으로 처리되었습니다.");

?>