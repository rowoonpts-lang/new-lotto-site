<?php
	include_once("_common.php");

	$type = isset($_POST['type'])
		? trim((string) $_POST['type'])
		: '';

	$price = fnGetTypePrice($type);

	echo $price === null
		? ''
		: number_format($price);
?>