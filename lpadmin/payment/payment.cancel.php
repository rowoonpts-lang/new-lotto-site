<?php
include_once("_common.php");
/*error_reporting(E_ALL);
ini_set("display_errors", 1);*/


header("Content-Type: text/html; charset=UTF-8");

function curl_post($url, $fields) {

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    curl_setopt($ch, CURLOPT_POST, true);

	$response = curl_exec($ch);

    if (!$response) {
        $body = '{"rescode":"FAIL","resmode":"'.curl_error($ch).'"}';
    } else {
        $body = $response;
    }
    curl_close($ch);
    return $body;
}

if($row['pay_company'] == "페이링"){
	$url = "http://api.payring.co.kr/v1/sugi/cancel.do";
	$orderid = $row['order_no'];

	$post_data["apikey"] = "a38cb601dc8b2efca4dad461b9793276";	//apikey
	$post_data["orderid"] = $orderid;							//결제 요청 주문번호


	$json = curl_post($url, $post_data);
	var_dump($json);
	$json = json_decode($json);
	echo "<br>";
	echo "orderid=".$orderid;
	echo "<br>";
	echo "rescode=".$json->rescode;
	echo "<br>";
	echo "resmsg=".$json->resmsg;
	echo "<br>";


	if($json->rescode == "0000"){
		alert("정상적으로 처리되었습니다.");
	}else{
		alert($json->resmsg);
	}
}
if($row['pay_company'] == "웰컴페이먼츠"){
	function current_millis() {
		list($usec, $sec) = explode(" ", microtime());
		return round(((float)$usec + (float)$sec) * 1000);
	}

	$mid = 'wa00999';
	$user_id = 'wa00999';
	$pay_type = 'CREDIT_CARD';
	$transaction_type = 'CANCEL';
	//$pay_method = 'CREDIT_OLDAUTH_API'; // 비인증: CREDIT_UNAUTH_API, 구인증: CREDIT_OLDAUTH_API
	$transaction_no = $row['transaction_no'];
	$amount = $row['lp_price'];
	$cancel_reason = '관리자에 의한 취소';
	$ip_address = $_SERVER["REMOTE_ADDR"];
	$millis = current_millis();

	$apikey = "e50b278bd894d51dc48196f99eb5ab5d";
	$iv = "98253d251054647d018e0c1566e1308a";

	// 검증값 생성
	$hash_value = hash("sha256", $mid.$transaction_type.$transaction_no.$amount.$millis.$apikey);

	$data = array(
		'pay_type' => $pay_type,
		'transaction_type' => $transaction_type,
		'pay_method' => $pay_method,
		'mid' => $mid,
		'user_id' => $user_id,
		'transaction_no' => $transaction_no,
		'amount' => $amount,
		'cancel_reason'=> $cancel_reason,
		'ip_address'=>$ip_address,
		'millis'=>$millis,
		'hash_value'=>$hash_value
	);

	$payload = json_encode($data);

	// Prepare new cURL resource
	$ch = curl_init('https://payapi.welcomepayments.co.kr/api/payment/cancel');
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
	//print_r($data_array);

	if($data_array['result_code'] == "0000"){
		alert("정상적으로 처리되었습니다.");
	}
}

if($row['pay_company'] == "페이업"){
	$expireYear=$_POST['card_expiry_yy']; // 2021년 07월
	$expireMonth=$_POST['card_expiry_mm']; // 2021년 07월

	$mid = 'bk1101';
	$apiCertKey = "d298e8886bba422392bee792dd3824db";

	

	$data2 = "{$mid}|{$row['transaction_no']}|{$apiCertKey}";
	// 검증값 생성
	$hash_value = hash("sha256", $data2);
	$signature = $hash_value;

	$data = array(
		'merchantId' => $mid,
		'transactionId' => $row['transaction_no'],
		'signature' => $signature
	);

	$payload = json_encode($data);

	$url = "https://api.payup.co.kr/v2/api/payment/{$mid}/cancel2";

	// Prepare new cURL resource
	$ch = curl_init($url);
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

	// Close cURL session handle
	curl_close($ch);

	$data_array = json_decode($result, true);

	if($data_array['responseCode'] == "0000"){
		alert("정상적으로 처리되었습니다.");
	}
}


?>
