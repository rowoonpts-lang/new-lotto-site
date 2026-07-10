<?
	include_once("./_common.php");

	$data1 = shell_exec("systemctl stop Oneshot2");
	$data1 = shell_exec("systemctl status Oneshot2");
	echo "<pre> $data1 </pre>";

	$data1 = shell_exec("systemctl start Oneshot2");
	$data1 = shell_exec("systemctl status Oneshot2");
	echo "<pre> $data1 </pre>";

	//system("/home/r001/public_html/Oneshot/java -jar Oneshot2.jar ./Oneshot2.conf > /dev/null &");
?>