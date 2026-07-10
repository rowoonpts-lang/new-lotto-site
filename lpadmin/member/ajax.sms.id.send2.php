<?
	include_once("_common.php");
	
	fnSetLog($member[mb_id], $mb_id.'님 회원 아이디 전송');
	$sql = "update g5_member set mb_password = '".get_encrypt_string('1234')."' where mb_id = '{$mb_id}'";
	sql_query($sql);

	$sql = "select * from {$g5['member_table']} where mb_id = '{$mb_id}' ";
	$row = sql_fetch($sql);

	//$msg = $row[mb_name]."님의 아이디는 ".$row[mb_id]." / 비밀번호는 1234입니다.\n".$config['cf_url'];
	$msg = "로또피크 분석담당자 ".$member['mb_name']."입니다\n회원님의 아이디는 회원님 휴대폰번호이고 비밀번호는 1234입니다\n네이버 또는 다음에 로또피크 검색후 사이트 접속하시면 되십니다\n감사합니다";
	//$msg = "로또중심 분석담당자 ".$member['mb_name']."입니다.\n로또중심 무료번호는 ".$config['cf_url']." 접속 또는 네이버에 로또중심 검색후\n아이디는 회원님 휴대폰번호 임시비밀번호는 1234로 \n로그인후 마이페이지-나의로또보관함에서 확인하시면 되시고\n매주 목요일 4시이후 확인 가능하시니깐 꼭 구매 부탁드립니다";
	$msg = "로또피크 분석담당자 ".$member['mb_name']."입니다\n회원님의 아이디는 회원님 휴대폰번호이고 비밀번호는 1234입니다\n네이버 또는 다음에 로또피크 검색후 사이트 접속하시면 되십니다\n그리고 고객센터 상담시간은 평일 오전10시에서 오후6시까지이고 토요일 일요일은 휴무이오니 착오없으시길 바라겠습니다\n감사합니다";

	

	fnSendOneshot($config['cf_oneshot_tel'], $row[mb_hp], $msg , $config['cf_oneshot_080']);
?>