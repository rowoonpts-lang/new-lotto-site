<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
include_once("_common.php");


header("Content-Type: text/html; charset=UTF-8");


$prodName=$_REQUEST['cre_mb_type'];
$amount = str_replace(",","",$_REQUEST['prodPrice']);
$card_num = $_REQUEST['card_no_noenc1'].$_REQUEST['card_no_noenc2'].$_REQUEST['card_no_noenc3'].$_REQUEST['card_no_noenc4']; // 승인되지 않는 번호입니다. 실제 승인테스트할 카드번호로 변경해주세요.

$card_sell_mm=$_REQUEST['card_sell_mm']; // 일시불 00, 2개월 02, 12개월 12
$user_name=$_REQUEST['mb_name'];


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
function current_millis() {
    list($usec, $sec) = explode(" ", microtime());
    return round(((float)$usec + (float)$sec) * 1000);
}

$orderid = date("YmdHis").mt_rand(10, 99);

if($pay_company == "페이링"){
	$card_expiry_yy="20".$_REQUEST['card_expiry_yy']; // 2021년 07월
	$card_expiry_mm=$_REQUEST['card_expiry_mm']; // 2021년 07월

	$url = "http://api.payring.co.kr/v1/sugi/order.do";



	$post_data["apikey"] = "a38cb601dc8b2efca4dad461b9793276";	//apikey
	$post_data["orderid"] = $orderid;		//결제 요청 주문번호
	$post_data["prodname"] = $prodName;							//상품명
	$post_data["amount"] = $amount;								//결제금액
	$post_data["encinfo"] = $card_num;					//신용카드번호
	$post_data["encdata"] = $card_expiry_yy.$card_expiry_mm;							//신용카드유효기간(YYYYMM)
	$post_data["quota"] = $card_sell_mm;									//일시불:00, 3개월 할부:03
	$post_data["taxcode"] = "00";								//과세:00, 비과세:01
	$post_data["custname"] = $user_name;							//고객명
	$post_data["custemail"] = "aaa@bbb.com";					//고객 email
	$post_data["info1"] = "필요시";								//사용자 정의 필드
	$post_data["info2"] = "필요시";								//사용자 정의 필드


	$json = curl_post($url, $post_data);
	//var_dump($json);
	$json = json_decode($json);

	$rt_value = $json->rescode;
	$rt_msg = $json->resmsg;
	$approval_no = $json->tranid;
	$transaction_no = $json->tranid;
	$card_sell_mm = $post_data['quota'];

}

if($pay_company == "웰컴페이먼츠"){
	$card_expiry_yy=$_REQUEST['card_expiry_yy']; // 2021년 07월
	$card_expiry_mm=$_REQUEST['card_expiry_mm']; // 2021년 07월

	$mid = 'wa00999';
	$pay_type = 'CREDIT_CARD';
	$pay_method = 'CREDIT_UNAUTH_API'; // 비인증: CREDIT_UNAUTH_API, 구인증: CREDIT_OLDAUTH_API
	$order_no = $orderid; // 중복되면 안됩니다. 영문숫자로 구성 공백 및 특수문자 사용금지
	$millis = current_millis();
	$apikey = "e50b278bd894d51dc48196f99eb5ab5d";
	$iv = "98253d251054647d018e0c1566e1308a";

	// 카드번호 암호화
	$card_no = bin2hex(openssl_encrypt($card_num, 'AES-128-CBC', hex2bin($apikey), OPENSSL_RAW_DATA,hex2bin($iv)));

	// 검증값 생성
	$hash_value = hash("sha256", $mid.$pay_type.$pay_method.$order_no.$amount.$millis.$apikey);
	$prodPrice=$amount;
	$data = array(
		'mid' => $mid,
		'pay_type' => $pay_type,
		'pay_method' => $pay_method,
		'card_no' => $card_no,
		'card_expiry_ym' => $card_expiry_yy.$card_expiry_mm,
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

	// Close cURL session handle
	curl_close($ch);

	$data_array = json_decode($result, true);
	$rt_value = $data_array['result_code'];
	$rt_msg = $data_array['result_message'];
	$approval_no = $data_array['approval_no'];
	$transaction_no = $data_array['transaction_no'];
	$card_sell_mm = $data_array['card_sell_mm'];
	$card_name = $data_array['card_name'];
	
}

if($pay_company == "페이업"){
	$expireYear=$_REQUEST['card_expiry_yy']; // 2021년 07월
	$expireMonth=$_REQUEST['card_expiry_mm']; // 2021년 07월

	$mid = 'bk1101';
	$apiCertKey = "d298e8886bba422392bee792dd3824db";

	$orderNumber = $orderid; // 중복되면 안됩니다. 영문숫자로 구성 공백 및 특수문자 사용금지
	$millis = current_millis();

	$quota = $card_sell_mm;
	$itemName = $prodName;
	$userName = $user_name;
	$mobileNumber = "";
	$kakaoSend = "N";
	$userEmail = "";
	$timestamp = date("YmdHis");
	$cardNo = $card_num;
	
	$amount=$amount;
	$birthday = $card_holder_ymd_noenc;
	$cardPw = $card_pw_noenc;

	$data2 = "{$mid}|{$orderNumber}|{$amount}|{$apiCertKey}|{$timestamp}";
	// 검증값 생성
	$hash_value = hash("sha256", $data2);
	$signature = $hash_value;

	$data = array(
		'orderNumber' => $orderNumber,
		'cardNo' => $cardNo,
		'expireMonth' => $expireMonth,
		'expireYear' => $expireYear,
		'amount' => $amount,
		'quota'=> $quota,
		'itemName'=>$itemName,
		'userName'=>$userName,
		'mobileNumber'=>$mobileNumber,
		'kakaoSend'=>$kakaoSend,
		'userEmail'=>$userEmail,
		'signature'=>$signature,
		'timestamp'=>$timestamp,
		'birthday'=>$birthday,
		'cardPw'=>$cardPw
	);

	$payload = json_encode($data);

	$url = "https://api.payup.co.kr/v2/api/payment/{$mid}/keyin2";

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
	$rt_value = $data_array['responseCode'];
	$rt_msg = "[".$rt_value."]".$data_array['responseMsg'];
	$approval_no = $data_array['authNumber'];
	$transaction_no = $data_array['transactionId'];
	$card_name = $data_array['cardName'];

}

/************************/
$lp_status = "입금";
$addQuery = "";
sql_query("alter table `l_pay` add column `su_confirm` varchar(50) NOT NULL");
if($pay_company == "페이업(수기)" || $pay_company == "코리아(수기)" || $pay_company == "세이프(수기)" || $pay_company == "다모아(수기)" || $pay_company == "루멘(수기)" || $pay_company == "온미르(수기)" || $pay_company == "페이츠(수기)" || $pay_company == "참좋은(수기)" || $pay_company == "오앤유(수기)" || $pay_company == "쇼페이(수기)" || $pay_company == "엠터치(수기)"|| $pay_company == "웰페이(수기)"|| $pay_company == "케이비(수기)"|| $pay_company == "캠핑라인(수기)"|| $pay_company == "원넷(수기)"|| $pay_company == "웨이업(수기)"|| $pay_company == "오후(수기)"){
	$card_expiry_yy=$_REQUEST['card_expiry_yy']; // 2021년 07월
	$card_expiry_mm=$_REQUEST['card_expiry_mm']; // 2021년 07월

	$order_no = $orderid; // 중복되면 안됩니다. 영문숫자로 구성 공백 및 특수문자 사용금지

	$data_array = json_decode($result, true);
	$rt_value = "0000";
	$rt_msg = "";
	$approval_no = "";
	$transaction_no = "";
	$card_name = $card_name;
	$lp_status = "주문";

	$addQuery = " , su_confirm = '대기' ";
}
/************************/

if($rt_value == "0000"){
	// 결제성공 및 세팅
	$sql = "update g5_member set mb_type = '{$prodName}' where 1=1 and mb_id = '{$mb_id}'";
	sql_query($sql);

	/*$month = fnGetTypeMonth($prodName);
	$sql = "update g5_member_etc set start_date = '".date('Y-m-d')."', end_date = '".date('Y-m-d', strtotime("+".$month." month"))."' where mb_id = '{$mb_id}'";
	sql_query($sql);*/

	
	$sql = "insert into l_pay set
				mb_id = '{$mb_id}'
				, emp_id = '".$_REQUEST['cre_emp_id']."'
				, pay_method = '신용카드'
				, pay_company = '{$pay_company}'
				, lp_type = '{$prodName}'
				, lp_price = '{$amount}'
				, lp_status = '{$lp_status}'
				, lp_datetime = now()
				, order_no = '{$orderid}'
				, card_sell_mm = '{$card_sell_mm}'
				, approval_no = '{$approval_no}'
				, transaction_no = '{$transaction_no}'
				, approval_ymdhms = ''
				, card_code = ''
				, card_name = '{$card_name}'
				, lp_pay_datetime = now()
				{$addQuery}
			";
	sql_query($sql);
	$lm_memo = $prodName." 신용카드 결제 - ".number_format($amount)."원";
	//fnSetMemo($mb_id, $_POST['cre_emp_id'], '결제', $lm_memo);
	
	$sql2 = "select * from g5_member where 1=1 and mb_id = '{$mb_id}'";
	$row2 = sql_fetch($sql2);

	$mb_hp = $row2['mb_hp'];

	if($pay_company != "페이업(수기)" && $pay_company != "코리아(수기)" && $pay_company != "세이프(수기)" && $pay_company != "다모아(수기)" && $pay_company != "루멘(수기)" && $pay_company != "온미르(수기)" && $pay_company != "페이츠(수기)"  && $pay_company != "참좋은(수기)" && $pay_company != "오앤유(수기)" && $pay_company != "쇼페이(수기)" && $pay_company != "엠터치(수기)"&& $pay_company != "웰페이(수기)"&& $pay_company != "케이비(수기)"&& $pay_company != "캠핑라인(수기)"&& $pay_company != "원넷(수기)"&& $pay_company != "웨이업(수기)"&& $pay_company != "오후(수기)"){
		// 문자발송
		$msg = "회원님께서 요청하신 카드결제가 정상적으로 완료되었습니다.\n로또피크 회원가입에 감사드리며 앞으로 모든 문의사항이나 애로사항은 반드시 대표전화 1800-6803으로 연락부탁드리며 회원님의 당첨에 최선을 다하겠습니다";
		fnSendOneshot($config['cf_oneshot_tel'], $mb_hp, $msg , '');
	}

	echo "0000";
}else{
	echo $rt_msg;
}
?>
