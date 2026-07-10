<?php
include_once("_common.php");
/*error_reporting(E_ALL);
ini_set("display_errors", 1);*/

function current_millis() {
    list($usec, $sec) = explode(" ", microtime());
    return round(((float)$usec + (float)$sec) * 1000);
}

$mid = 'wa00464';
$pay_type = 'CREDIT_CARD';
$pay_method = 'CREDIT_OLDAUTH_API'; // 비인증: CREDIT_UNAUTH_API, 구인증: CREDIT_OLDAUTH_API
$order_no = date('YmdHisB'); // 중복되면 안됩니다. 영문숫자로 구성 공백 및 특수문자 사용금지
$amount = str_replace(",","",$_POST['prodPrice']);
$millis = current_millis();
$card_no_noenc = $_POST['card_no_noenc1'].$_POST['card_no_noenc2'].$_POST['card_no_noenc3'].$_POST['card_no_noenc4']; // 승인되지 않는 번호입니다. 실제 승인테스트할 카드번호로 변경해주세요.
$card_pw_noenc = $_POST['card_pw_noenc']; // 카드비밀번호 앞 2자리입니다. 샘플값입니다.
$card_holder_ymd_noenc = $_POST['card_holder_ymd_noenc']; // 71년3월8일 인 경우 샘플값입니다.
$apikey = "12686e4b63b468838c1fa17f7e643441";
$iv = "0579dfeb2ad347919ddb700359013ef4";

// 카드번호 암호화
$card_no = bin2hex(openssl_encrypt($card_no_noenc, 'AES-128-CBC', hex2bin($apikey), OPENSSL_RAW_DATA,hex2bin($iv)));

// 카드비밀번호 앞2자리 암호화
$card_pw = bin2hex(openssl_encrypt($card_pw_noenc, 'AES-128-CBC', hex2bin($apikey), OPENSSL_RAW_DATA,hex2bin($iv)));

// 카드소유자 생년월일 암호화
$card_holder_ymd = bin2hex(openssl_encrypt($card_holder_ymd_noenc, 'AES-128-CBC', hex2bin($apikey), OPENSSL_RAW_DATA,hex2bin($iv)));


// 검증값 생성
$hash_value = hash("sha256", $mid.$pay_type.$pay_method.$order_no.$amount.$millis.$apikey);
$mb_id = $_POST['mb_id'];
$card_expiry_ym=$_POST['card_expiry_ym']; // 2021년 07월
$user_name=$_POST['mb_name'];
$prodPrice=$amount;
$prodName=$_POST['cre_mb_type'];
$card_sell_mm = $_POST['card_sell_mm']; // 일시불 00, 2개월 02, 12개월 12
$data = array(
    'mid' => $mid,
    'pay_type' => $pay_type,
    'pay_method' => $pay_method,
    'card_no' => $card_no,
    'card_pw' => $card_pw,
    'card_holder_ymd' => $card_holder_ymd,
    'card_expiry_ym' => $card_expiry_ym,
    'order_no'=> $order_no,
    'user_name'=>$user_name,
    'amount'=>$prodPrice,
    'product_name'=>$prodName,
    'card_sell_mm'=>$card_sell_mm,
    'millis'=>$millis,
    'hash_value'=>$hash_value
);

$payload = json_encode($data);

// Prepare new cURL resource
$ch = curl_init('https://payapi.welcomepayments.co.kr/api/payment/approval');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

// Set HTTP Header for POST request
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Content-Length: ' . strlen($payload))
);

// Submit the POST request
$result = curl_exec($ch);
curl_close($ch);
// Close cURL session handle




$data_array = json_decode($result, true);

if($data_array['result_code'] == "0000"){
	// 결제성공 및 세팅
	$sql = "update g5_member set mb_type = '{$prodName}' where 1=1 and mb_id = '{$mb_id}'";
	sql_query($sql);

	/*$month = fnGetTypeMonth($prodName);
	$sql = "update g5_member_etc set start_date = '".date('Y-m-d')."', end_date = '".date('Y-m-d', strtotime("+".$month." month"))."' where mb_id = '{$mb_id}'";
	sql_query($sql);*/


	$sql = "insert into l_pay set
				mb_id = '{$mb_id}'
				, emp_id = '".$_POST['cre_emp_id']."'
				, pay_method = '신용카드'
				, lp_type = '{$prodName}'
				, lp_price = '{$amount}'
				, lp_status = '입금'
				, lp_datetime = now()
				, order_no = '{$data_array['order_no']}'
				, card_sell_mm = '{$data_array['card_sell_mm']}'
				, approval_no = '{$data_array['approval_no']}'
				, transaction_no = '{$data_array['transaction_no']}'
				, approval_ymdhms = '{$data_array['approval_ymdhms']}'
				, card_code = '{$data_array['card_code']}'
				, card_name = '{$data_array['card_name']}'
				, lp_pay_datetime = now()
			";
	sql_query($sql);

	$lm_memo = $prodName." 신용카드 결제 - ".number_format($amount)."원";
	//fnSetMemo($mb_id, $_POST['cre_emp_id'], '결제', $lm_memo);
}


echo $result;

?>
