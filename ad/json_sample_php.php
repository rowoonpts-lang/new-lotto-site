<?php
	// 광고 URL 입력
	$url = 'http://xn--1-3x5em0yglcw0h.kr/ad/process.php?lu_type=01&lu_code=01&name=홍길동&tel=01012345678';
	$json_string = file_get_contents_curl_able($url);
	
	$list = json_decode($json_string,TRUE);// JSON 데이터를 배열로 변환

	// 결과값 호출
	echo $list['result'];
	echo $list['result_desc'];

	
	function file_get_contents_curl_able($url) {
		$ch = curl_init();
	 
		curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);       
	 
		$data = curl_exec($ch);
		curl_close($ch);
	 
		return $data;
	}
?>