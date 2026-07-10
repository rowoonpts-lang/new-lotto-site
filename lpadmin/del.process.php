<?
	include_once("_common.php");
	$sql = "delete from {$table} where 1=1 and {$key_name} = '{$key_value}'";
	sql_query($sql);

	alert("정상적으로 삭제되었습니다.");
?>