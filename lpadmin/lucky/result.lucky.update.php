<?php

include_once("_common.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    alert(
        '올바른 요청이 아닙니다.',
        G5_LADMIN_URL . '/lucky/result.php'
    );
    exit;
}

$loginLevel = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if ($loginLevel < LOTTO_ROLE_ADMIN) {
    alert(
        '관리자 이상만 배출 조합 설정을 변경할 수 있습니다.',
        G5_LADMIN_URL . '/lucky/result.php'
    );
    exit;
}

lottoLuckyTokenCheck();

$cfLucky1 = isset($_POST['cf_lucky_1'])
    ? max(0, (int) $_POST['cf_lucky_1'])
    : 0;

$cfLucky2 = isset($_POST['cf_lucky_2'])
    ? max(0, (int) $_POST['cf_lucky_2'])
    : 0;

$cfLucky3 = isset($_POST['cf_lucky_3'])
    ? max(0, (int) $_POST['cf_lucky_3'])
    : 0;

$requiredColumns = array(
    'cf_lucky_1',
    'cf_lucky_2',
    'cf_lucky_3',
);

foreach ($requiredColumns as $column) {
    $columnResult = sql_query(
        "SHOW COLUMNS FROM {$g5['config_table']} LIKE '{$column}'",
        false
    );

    if (!$columnResult || !sql_fetch_array($columnResult)) {
        alert('배출 조합 설정 DB 컬럼이 없습니다. 관리자에게 문의해주세요.');
    }
}

$sql = "
    UPDATE {$g5['config_table']}
    SET
        cf_lucky_1 = '{$cfLucky1}',
        cf_lucky_2 = '{$cfLucky2}',
        cf_lucky_3 = '{$cfLucky3}'
";

$updated = sql_query($sql, false);

if (!$updated) {
    alert('배출 조합 설정 저장에 실패했습니다.');
}

$turn = isset($_POST['turn'])
    ? max(1, (int) $_POST['turn'])
    : 0;

$returnUrl = G5_LADMIN_URL . '/lucky/result.php';

if ($turn > 0) {
    $returnUrl .= '?turn=' . $turn;
}

alert('배출 조합 설정을 저장했습니다.', $returnUrl);
