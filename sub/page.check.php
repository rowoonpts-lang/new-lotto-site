<?
	if (!isset($g5['title'])) {
		
		switch($request_uri){
			case "/sub/sub0102.php":		$g5['title'] = "인사말";				break;
			case "/sub/sub0101.php":		$g5['title'] = "분석프로그램";			break;
			case "/sub/sub0201.php":		$g5['title'] = "등급안내";				break;
			case "/sub/stats.php":			$g5['title'] = "로또 분석용어";			break;
			case "/sub/stats2.php":			$g5['title'] = "확률과 조합 분석";		break;
			case "/sub/stats3.php":			$g5['title'] = "로또 구입 잘한는법";		break;
			case "/sub/my_lotto.php":		$g5['title'] = "나의 당첨현황";			break;
			case "/sub/deluxe.php":			$g5['title'] = "디럭스 그룹";		break;
			case "/sub/sub0301.php":		$g5['title'] = "문자가 오지 않을 때";		break;
			
		}
	}
?>