<?php
include_once('_common.php');
include_once(G5_LADMIN_PATH.'/program/lotto.number.php');
$test_hp = '01012345678';
$msg = "[테스트] 발송 간격 조정 후 테스트입니다.";
$res = fnSendOneshot($config['cf_oneshot_tel'], $test_hp, $msg , $config['cf_oneshot_080'], false, '', true);
echo "Response: ";
var_dump($res);
?>
