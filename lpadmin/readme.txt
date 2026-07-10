// 레벨 5부터 관리자 페이지 진입가능

// fnNewMbCode();
새로운 코드 생성

// 모든 등급을 끌어오는 함수
fnGetType();

// 유료 등급을 끌어오는 함수
fnGetTypePre();

// 약관 회원 끌어오는 함수
fnGetTypeYak();

// 등급별 가격 검색
fnGetTypePrice($mb_type);

// 등급별 기간 검색
fnGetTypeMonth($mb_type)

// g5_member_etc에 정보가 없으면 세팅해준다.
setEtcInfo($mb_id, $mb_db);

// 회원정보 수정시 함수(회원아이디는 가장 마지막에 수정해야한다)
setMemberInfo($mb_id, $mb_id_new = "", $mb_hp_new = "")

// 로그를 남김
fnSetLog('아이디','남길글');

// 원샷 문자전송
fnSendOneshot($config['cf_oneshot_tel'], $row[mb_hp], $msg , $config['cf_oneshot_080'], $origin=false, $etc = '');

// 새로운 회차 정보
getTurn();

//메모 남기기
fnSetMemo($mb_id, $from_mb_id, $lm_memo_type = '', $lm_memo = '', $misu = 0, $lm_alarm_type = '', $lm_alarm_date = '', $lm_price = '')

// 로또번호 생성
include_once(G5_LADMIN_PATH."/program/lotto.number.php");
fnGetNumber($mb_id, $cnt, $type = 0, $mb_hp = "", $sms = true, $log = true)

// 당첨정보API
getLuckyNum($turn);

// 암호화
Encrypt($str, $secret_key='secret key', $secret_iv='secret iv');
//복호화
Decrypt($str, $secret_key='secret key', $secret_iv='secret iv')

// SMS 결과
getSmsResult($table, $msg_id);

// SMS 재전송
setSmsReSend($idx)

// 로또 볼 디자인
getBall($number);


// 관리자 이름 송출
$sql_member = "select * from g5_member where 1=1 and mb_level > 5";
$result_member = sql_query($sql_member);
$member_info = array();
for($i=0; $row_info = sql_fetch_array($result_member); $i++){
	$member_info[$row_info[mb_id]] = $row_info[mb_name];
}	

// 자바스크립트 게시물 지우기(head.sub.php에 있음)
function fnProcDel(table, key_name, key_value)


//팀 리스트
getTeamList()

//팀 리스트 2팀
getTeamList2();

//팀 리스트 운영빼고
getTeamList3();

//팀 레벨 권한
getLevelList();

//팀 레벨 페이지
getLevelPage();