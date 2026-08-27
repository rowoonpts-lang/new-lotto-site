<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once '_common.php';
include_once G5_PATH . '/include/lotto_combination_sms.lib.php';

date_default_timezone_set('Asia/Seoul');

$now = new DateTimeImmutable(
    'now',
    new DateTimeZone('Asia/Seoul')
);

$result = lottoCombinationSmsQueueWeekday($now);

if (!empty($result['success'])) {
    sql_query(
        "update g5_config
         set
            cf_auto1_date = '" . date('Y-m-d') . "',
            cf_auto1_ing = '2'",
        false
    );
}

$message = '01 SMS Process - '
    . 'Status: '
    . (isset($result['status']) ? $result['status'] : 'unknown')
    . ' / Queued: '
    . (isset($result['queued_count']) ? (int) $result['queued_count'] : 0)
    . ' / Skipped: '
    . (isset($result['skipped_count']) ? (int) $result['skipped_count'] : 0)
    . ' / Failed: '
    . (isset($result['failed_count']) ? (int) $result['failed_count'] : 0);

if (empty($result['success'])) {
    $message .= ' / Error: '
        . (isset($result['error'])
            ? (string) $result['error']
            : '문자 발송 처리 실패');
}

echo "<script>parent.fnSetBoard("
    . json_encode($message, JSON_UNESCAPED_UNICODE)
    . ");</script>";
?>
