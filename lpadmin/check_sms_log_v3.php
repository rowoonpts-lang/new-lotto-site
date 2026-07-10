<?php
include_once('../common.php');
$sql = "select result, count(*) as cnt from msg_cust_log_202605 group by result";
$res = sql_query($sql);
while($row = sql_fetch_array($res)) {
    echo 'Result: ' . $row['result'] . ' | Count: ' . $row['cnt'] . "\n";
}
?>
