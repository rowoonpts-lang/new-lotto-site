<?
	include_once("_common.php");

	$sql = "select count(idx) cnt from l_ad_user where 1=1 and lu_id = '{$lu_id}' and lu_pw = '{$lu_pw}' ";
	$row = sql_fetch($sql);

	if($row['cnt'] < 1){
		alert("아이디 혹은 패스워드가 일치하지 않습니다.");
	}else{
		$sql = "select * from l_ad_user where 1=1 and lu_id = '{$lu_id}'";
		$row = sql_fetch($sql);

		if($row['st_tp'] == '0'){
			alert("관리자 페이지 접속이 중지된 아이디입니다.");
		}
		if($row['del_yn'] == '1'){
			alert("삭제된 매체사 아이디 입니다.");
		}

		$_SESSION['ad_id'] = $lu_id;

		$sql = "update l_ad_user set login_datetime = now() where lu_id = '{$lu_id}'";
		sql_query($sql);
		goto_url("./process.list.php");
	}
?>