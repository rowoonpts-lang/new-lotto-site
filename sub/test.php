<?php
include_once("_common.php");

$addQuery = " and phone_no = '01046276555' ";

/*$sql = "select * from msg_cust_log where 1=1 and send_time >= '2025-08-14 09:00:00' and send_time <= '2025-08-14 09:50:00' {$addQuery} ";
$result = sql_query($sql);

for($i=0; $row = sql_fetch_array($result); $i++){
	fnSendOneshot($config['cf_oneshot_tel'], $row['phone_no'], $row['message'], '', true, "");
}*/

$search = 'script'; // 찾고 싶은 문자열
$dbName = G5_MYSQL_DB;

$sql = "
	SELECT TABLE_NAME, COLUMN_NAME 
	FROM INFORMATION_SCHEMA.COLUMNS 
	WHERE TABLE_SCHEMA = '$dbName' 
	  AND DATA_TYPE IN ('varchar', 'text', 'mediumtext', 'longtext')
";
$res = sql_query($sql);

for ($i = 0; $row = sql_fetch_array($res); $i++) {
	$table = $row['TABLE_NAME'];
	$column = $row['COLUMN_NAME'];

	$query = "SELECT `$column` FROM `$table` WHERE `$column` LIKE '%$search%' LIMIT 5";
	$inner_res = sql_query($query);
	$found = false;

	for ($j = 0; $inner_row = sql_fetch_array($inner_res); $j++) {
		if (!$found) {
			echo "<hr><strong>📌 발견: {$table}.{$column}</strong><br>";
			$found = true;
		}
		echo htmlspecialchars($inner_row[$column]) . "<br>";
	}
}

/*
 die();

$search = 'script'; // 찾고 싶은 문자열
$dbName = G5_MYSQL_DB;

$sql = "
    SELECT TABLE_NAME, COLUMN_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = '$dbName' 
      AND DATA_TYPE IN ('varchar', 'text', 'mediumtext', 'longtext')
";
$res = sql_query($sql);

for ($i = 0; $row = sql_fetch_array($res); $i++) {
    $table = $row['TABLE_NAME'];
    $column = $row['COLUMN_NAME'];

    $query = "SELECT `$column` FROM `$table` WHERE `$column` LIKE '%$search%' LIMIT 5";
    $inner_res = sql_query($query);
    $found = false;

    for ($j = 0; $inner_row = sql_fetch_array($inner_res); $j++) {
        if (!$found) {
            echo "<hr><strong>📌 발견: {$table}.{$column}</strong><br>";
            $found = true;
        }
        echo htmlspecialchars($inner_row[$column]) . "<br>";
    }
}*/
?>
