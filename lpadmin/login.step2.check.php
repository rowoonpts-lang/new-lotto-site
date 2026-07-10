<?
	include_once("_common.php");

	if(!$step2){
		alert("2차인증 코드를 입력해주세요.");
	}
	if($step2 != $config['cf_10']){
		$sql = "insert into g1_2step_ip set step2 = '{$step2}', g2i_ip = '".$_SERVER["REMOTE_ADDR"]."', g2i_datetime = now()";
		sql_query($sql);

		alert('2차인증 코드가 맞지 않습니다.\n접속 IP : '.$_SERVER["REMOTE_ADDR"]);
	}

	$_SESSION['ss_step2'] = $config['cf_10'];

	goto_url(G5_LADMIN_URL);
?>