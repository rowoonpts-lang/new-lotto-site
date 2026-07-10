<?php
include_once(G5_PATH.'/_common.php');
include_once(G5_LADMIN_PATH.'/program/lotto.number.php');

// 관리자 권한 체크 (필요시 추가)
// if ($member['mb_level'] < 10) die('Permission denied.');

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

if (!$today_col) die('Today is Sunday. No SMS to send.');

// 1. 발송 실패 건(result = '0')의 recent_auto_date 초기화
$sql_failed = "SELECT DISTINCT phone_no FROM msg_cust_log_202605 WHERE result = '0' AND DATE(send_time) = '$today'";
$res_failed = sql_query($sql_failed);
$reset_count = 0;
while ($row_failed = sql_fetch_array($res_failed)) {
    $hp = $row_failed['phone_no'];
    sql_query("UPDATE g5_member_etc SET recent_auto_date = '' WHERE mb_hp = '$hp' AND recent_auto_date = '$today'");
    $reset_count += sql_affected_rows();
}

// 2. 대상자 추출 (오늘 미발송자 + 초기화된 실패자)
$sql_common = "
    and a.mb_id = b.mb_id
    and a.start_date <= '$today' 
    and a.end_date >= '$today' 
    and {$today_col} > 0
    and (a.recent_auto_date is null or a.recent_auto_date != '$today')
";

$sql = "SELECT a.*, b.mb_hp, b.mb_type FROM g5_member_etc a, g5_member b WHERE 1=1 {$sql_common}";
$result = sql_query($sql);
$total = sql_num_rows($result);

?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>수동 대량 발송</title>
    <script src="<?=G5_JS_URL?>/jquery-1.12.4.min.js"></script>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        #log { background: #000; color: #0f0; padding: 15px; height: 400px; overflow-y: auto; border-radius: 5px; font-family: monospace; }
        .stat { margin-bottom: 10px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>수동 대량 발송 (오늘: <?=$today?>)</h1>
    <div class="stat">
        실패 건 초기화: <?=$reset_count?> 명<br>
        발송 대상자: <span id="total_count"><?=$total?></span> 명
    </div>
    <div id="log">발송 대기 중...</div>
    <br>
    <button id="start_btn" onclick="startSend()">발송 시작</button>

    <script>
        var targets = [];
        <?php
        while($row = sql_fetch_array($result)) {
            echo "targets.push(".json_encode(array(
                'mb_id' => $row['mb_id'],
                'mb_hp' => $row['mb_hp'],
                'mb_type' => $row['mb_type'],
                'num_cnt' => $row[$today_col]
            )).");\n";
        }
        ?>

        var currentIndex = 0;
        var isRunning = false;

        function log(msg) {
            $('#log').append('<div>' + msg + '</div>');
            $('#log').scrollTop($('#log')[0].scrollHeight);
        }

        function startSend() {
            if (isRunning) return;
            if (targets.length === 0) {
                log('발송할 대상이 없습니다.');
                return;
            }
            isRunning = true;
            $('#start_btn').prop('disabled', true);
            log('발송을 시작합니다...');
            sendNext();
        }

        function sendNext() {
            if (currentIndex >= targets.length) {
                log('모든 발송이 완료되었습니다.');
                isRunning = false;
                return;
            }

            var target = targets[currentIndex];
            $.ajax({
                url: 'manual_mass_send_ajax.php',
                type: 'POST',
                data: target,
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        log('[' + (currentIndex + 1) + '/' + targets.length + '] ' + target.mb_hp + ' 발송 성공');
                    } else {
                        log('[' + (currentIndex + 1) + '/' + targets.length + '] ' + target.mb_hp + ' 발송 실패: ' + res.message);
                    }
                    currentIndex++;
                    setTimeout(sendNext, 1500); // 1초 간격
                },
                error: function() {
                    log('[' + (currentIndex + 1) + '/' + targets.length + '] ' + target.mb_hp + ' 통신 오류');
                    currentIndex++;
                    setTimeout(sendNext, 1500);
                }
            });
        }
    </script>
</body>
</html>
