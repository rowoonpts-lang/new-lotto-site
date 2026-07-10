<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.sub.php");



	sql_query("alter table ".$g5['member_table']." add column `mb_type` varchar(50) NOT NULL");
	$mb_code = fnNewMbCode();

	$sql = " insert into {$g5['member_table']}
                set mb_id = '{$mb_id}',
					 mb_code = '{$mb_code}',
                     mb_password = '".get_encrypt_string('1234')."',
                     mb_name = '{$mb_name}',
                     mb_nick = 'nick".date("YmdHisB")."',
                     mb_nick_date = '".G5_TIME_YMD."',
                     mb_email = '".date("YmdHisB")."@lottopeak.co.kr',
                     mb_homepage = '{$mb_homepage}',
					 mb_tel = '{$mb_hp}',
                     mb_hp = '{$mb_hp}',
                     mb_zip1 = '{$mb_zip1}',
                     mb_zip2 = '{$mb_zip2}',
                     mb_addr1 = '{$mb_addr1}',
                     mb_addr2 = '{$mb_addr2}',
                     mb_addr3 = '{$mb_addr3}',
                     mb_addr_jibeon = '{$mb_addr_jibeon}',
                     mb_signature = '{$mb_signature}',
                     mb_profile = '{$mb_profile}',
                     mb_today_login = null,
                     mb_datetime = '".G5_TIME_YMDHIS."',
                     mb_ip = '{$_SERVER['REMOTE_ADDR']}',
                     mb_level = '{$config['cf_register_level']}',
                     mb_recommend = '{$mb_recommend}',
                     mb_login_ip = '{$_SERVER['REMOTE_ADDR']}',
                     mb_mailling = '{$mb_mailling}',
                     mb_sms = '{$mb_sms}',
                     mb_open = '{$mb_open}',
                     mb_open_date = '".G5_TIME_YMD."',
					 mb_type = '{$mb_type}',
                     mb_1 = '{$mb_1}',
                     mb_2 = '{$mb_2}',
                     mb_3 = '{$mb_3}',
                     mb_4 = '{$mb_4}',
                     mb_5 = '{$mb_5}',
                     mb_6 = '{$mb_6}',
                     mb_7 = '{$mb_7}',
                     mb_8 = '{$mb_8}',
                     mb_9 = '{$mb_9}',
                     mb_10 = '{$mb_10}'
			";
	sql_query($sql);


	$mb_no = sql_insert_id();

	setEtcInfo($mb_id, $mb_db);
	fnSetMemo($mb_id, $member[mb_id], '대리가입', $lm_memo = '대리가입 완료');
?>
<script>
$(function(){
	alert("정상적으로 임시회원 가입이 완료되었습니다.");
	window.opener.location.reload();
	window.close();
});
</script>