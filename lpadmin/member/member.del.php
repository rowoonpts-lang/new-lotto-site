<?
	include_once("_common.php");
	
	$mb_id = base64_decode($mb_id);

	$sql = "delete from g5_member where 1=1 and mb_id = '{$mb_id}'";
	sql_query($sql);
	$sql = "delete from g5_member_etc where 1=1 and mb_id = '{$mb_id}'";
	sql_query($sql);
	fnSetLog($member[mb_id],$member[mb_id].'님께서 {$mb_id} 회원을 삭제하셨습니다.');

	alert("정상적으로 삭제되었습니다.");
?>