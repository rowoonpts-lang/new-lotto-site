<?php

include_once("_common.php");
include_once(G5_PATH."/include/lotto_admin_ip.lib.php");

header('Content-Type: application/json; charset=utf-8');

function ipSaveResponse($success, $message, $value = '')
{
    echo json_encode(array(
        'success' => (bool) $success,
        'message' => (string) $message,
        'value' => (string) $value,
    ));
    exit;
}

$loginLevel = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if ($loginLevel < LOTTO_ROLE_ADMIN) {
    ipSaveResponse(false, '관리자 이상만 변경할 수 있습니다.');
}

if (
    !isset($_SERVER['REQUEST_METHOD'])
    || $_SERVER['REQUEST_METHOD'] !== 'POST'
) {
    ipSaveResponse(false, '잘못된 요청입니다.');
}

if (!lottoEmpTokenCheck()) {
    ipSaveResponse(
        false,
        '올바르지 않은 요청입니다. 화면을 새로고침한 후 다시 시도해 주세요.'
    );
}


$rawValue = isset($_POST['v'])
    ? trim((string) $_POST['v'])
    : '';

if ($rawValue === '') {
    $normalizedValue = '';
} else {
    $parts = explode('|', $rawValue);
    $normalizedIps = array();

    foreach ($parts as $part) {
        $ip = trim((string) $part);

        if ($ip === '') {
            continue;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            ipSaveResponse(
                false,
                '올바르지 않은 IP가 있습니다: '.$ip
            );
        }

        $normalizedIps[$ip] = $ip;
    }

    $normalizedValue = implode('|', array_values($normalizedIps));
}

$normalizedSql = sql_real_escape_string($normalizedValue);

$result = sql_query(
    "update {$g5['config_table']}
        set cf_ip = '{$normalizedSql}'",
    false
);

if (!$result) {
    ipSaveResponse(false, '허용 IP 저장에 실패했습니다.');
}

ipSaveResponse(
    true,
    '허용 IP가 저장되었습니다.',
    $normalizedValue
);
