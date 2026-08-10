<?php
	include_once("_common.php");

	if ((int) $member['mb_level'] < LOTTO_ROLE_ADMIN) {
		alert('접근 권한이 없습니다.', '../');
	}
	print_r($chk);

	for($i=0; $i < count($chk); $i++){
		$sql = "delete from g5_member where 1=1 and mb_id = '{$chk[$i]}'";
		sql_query($sql);

		$sql = "delete from g5_member_etc where 1=1 and mb_id = '{$chk[$i]}'";
		sql_query($sql);
	}
?>