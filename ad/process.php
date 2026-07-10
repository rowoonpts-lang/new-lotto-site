<?
	include_once("_common.php");
	header('Content-Type: application/json');

	error_reporting(E_ALL);
	ini_set("display_errors", 0);

	$sql = "insert into l_ad_list_dump set
				lu_type = '{$lu_type}'
				, lu_code = '{$lu_code}'
				, name = '{$name}'
				, tel = '{$tel}'
				, etc1 = '{$etc1}'
				, etc2 = '{$etc2}'
				, ip = '{$ip}'
				, refer = '{$refer}'
				, ll_datetime = now()
			";
	//sql_query($sql);

	$error		= "1111";
	$error_text = "정상";
	$lu_type	= str_replace(" ","", $_REQUEST['lu_type']);
	$lu_code	= str_replace(" ","", $_REQUEST['lu_code']);
	$name		= str_replace(" ","", $_REQUEST['name']);
	if(!$name){$name = '익명';}
	$tel		= str_replace("-","",str_replace(" ","", $_REQUEST['tel']));
	$etc1		= get_text($_REQUEST['etc1']);
	$etc2		= get_text($_REQUEST['etc2']);
	$ip			= $_SERVER["REMOTE_ADDR"];
	$refer		= $_SERVER['HTTP_REFERER'];

	if($lu_type == ""){
		$error = "0001";
		$error_text = "필수값 lu_type 누락";
		fnJSon($error, $error_text);
		return false;
	}
	if($lu_code == ""){
		$error = "0002";
		$error_text = "필수값 lu_code 누락";
		fnJSon($error, $error_text);
		return false;
	}
	if($name == ""){
		$error = "0003";
		$error_text = "필수값 name 누락";
		fnJSon($error, $error_text);
		return false;
	}
	if($tel == ""){
		$error = "0004";
		$error_text = "필수값 tel 누락";
		fnJSon($error, $error_text);
		return false;
	}

	$hp = preg_replace("/[^0-9]/", "", $tel);
	if(!preg_match("/^01[0-9]{8,9}$/", $hp)){
		$error = "0005";
		$error_text = "필수값 tel 형식 에러";
		fnJSon($error, $error_text);
		return false;
	}

	$sql = "select count(idx) cnt from l_ad_user where 1=1 and lu_type = '{$lu_type}' and lu_code = '{$lu_code}'";
	$row = sql_fetch($sql);


	if($row['cnt'] < 1){
		$error = "0006";
		$error_text = "매체사 코드 또는 광고 코드 에러";
		fnJSon($error, $error_text);
		return false;
	}

	$sql = "insert into l_ad_list set
				lu_type = '{$lu_type}'
				, lu_code = '{$lu_code}'
				, name = '{$name}'
				, tel = '{$tel}'
				, etc1 = '{$etc1}'
				, etc2 = '{$etc2}'
				, ip = '{$ip}'
				, refer = '{$refer}'
				, ll_datetime = now()
			";
	//sql_query($sql); // 20250512 막음
	$idx = sql_insert_id();

	if($idx){
		$error = "0000";
		$error_text = "정상";
		fnJSon($error, $error_text);
	}else{
		$error = "1111";
		$error_text = "기타 예외 상황 (문의요청)";
		fnJSon($error, $error_text);
	}


function fnJSon($error, $error_text){
	$json = array("result" => $error, "result_desc" => $error_text);
	echo json_encode($json);
}


?>