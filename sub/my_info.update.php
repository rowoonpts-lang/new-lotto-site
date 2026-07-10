<?
	include_once("_common.php");
	
	$sql_password = "";
	if ($mb_password){
		$sql_password = " mb_password = '".get_encrypt_string($mb_password)."' ";
	 }

	$sql = " update g5_member set
				{$sql_password}
				where 1=1
					and mb_id = '{$member['mb_id']}'
				";
	sql_query($sql);

	alert("회원정보가 수정되었습니다.");
?>