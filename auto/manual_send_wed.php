<?php
include_once('_common.php');
include_once(G5_LADMIN_PATH.'/program/lotto.number.php');

// 오늘 날짜 및 요일 설정
$today = date('Y-m-d');
$w = date('w');
$today_col = '';
switch($w){
    case '1': $today_col = 'num_mon'; break;
    case '2': $today_col = 'num_tue'; break;
    case '3': $today_col = 'num_wed'; break;
    case '4': $today_col = 'num_thur'; break;
    case '5': $today_col = 'num_fri'; break;
    case '6': $today_col = 'num_sat'; break;
}


// 대상자 추출 (오늘 미발송자만)
$sql_common = "
    and a.mb_id = b.mb_id
    and a.start_date <= '$today' 
    and a.end_date >= '$today' 
    and {$today_col} > 0
    and (a.recent_auto_date is null or a.recent_auto_date != '$today')
";

$sql = "select a.*, b.mb_hp, b.mb_type from g5_member_etc a, g5_member b where 1=1 {$sql_common}";
$result = sql_query($sql);
$total = sql_num_rows($result);

echo "Total Targets: $total\n";

$count = 0;
while($row = sql_fetch_array($result)) {
    $num_cnt = $row[$today_col];
    
    // 발송 실행
    fnGetNumber($row['mb_id'], $num_cnt, 0, $row['mb_hp'], true, false, $row['mb_type']);
    
    // DB 업데이트
    $turn = getTurn();
    sql_query("update g5_member_etc set use_num = use_num + {$num_cnt}, recent_auto_date = '$today', recent_auto_datetime = now(), recent_turn = '{$turn}' where mb_id = '{$row['mb_id']}'");
    
    $count++;
    if($count % 10 == 0) echo "Sent: $count / $total\n";
    
    // 0.5초 대기 (업체 누락 방지)
    usleep(500000); 
}

echo "Final Sent: $count\n";
?>
