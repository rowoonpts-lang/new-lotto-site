<?php
include_once(G5_PATH.'/_common.php');
include_once(G5_LADMIN_PATH.'/program/lotto.number.php');

$mb_id = $_POST['mb_id'];
$mb_hp = $_POST['mb_hp'];
$mb_type = $_POST['mb_type'];
$num_cnt = $_POST['num_cnt'];

if (!$mb_id || !$mb_hp) {
    echo json_encode(array('status' => 'error', 'message' => 'Invalid parameters'));
    exit;
}

$today = date('Y-m-d');

// 발송 실행
fnGetNumber($mb_id, $num_cnt, 0, $mb_hp, true, false, $mb_type);

// DB 업데이트
$turn = getTurn();
$sql = "UPDATE g5_member_etc SET 
            use_num = use_num + {$num_cnt}, 
            recent_auto_date = '$today', 
            recent_auto_datetime = now(), 
            recent_turn = '{$turn}' 
        WHERE mb_id = '$mb_id'";
sql_query($sql);

echo json_encode(array('status' => 'success'));
?>
