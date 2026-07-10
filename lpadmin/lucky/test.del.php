<?
	include_once("_common.php");

	$sql = "delete from l_filter_temp where 1=1 and type = '{$f_type}'";
	sql_query($sql);

	alert("정상적으로 삭제되었습니다.");
?>