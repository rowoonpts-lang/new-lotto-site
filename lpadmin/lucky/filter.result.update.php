<?php

include_once("_common.php");

$loginLevel = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if (!lottoCanManageAdminSettings($loginLevel)) {
    alert('필터 설정을 변경할 권한이 없습니다.');
}

$sumMin = isset($_POST['sum_min'])
    ? (int) $_POST['sum_min']
    : 0;

$sumMax = isset($_POST['sum_max'])
    ? (int) $_POST['sum_max']
    : 0;

/*
 * 로또 6개 번호의 실제 가능한 총합 범위:
 * 최소 1+2+3+4+5+6 = 21
 * 최대 40+41+42+43+44+45 = 255
 */
if ($sumMin < 21 || $sumMin > 255) {
    alert('총합수 최소값은 21부터 255 사이여야 합니다.');
}

if ($sumMax < 21 || $sumMax > 255) {
    alert('총합수 최대값은 21부터 255 사이여야 합니다.');
}

if ($sumMin > $sumMax) {
    alert('총합수 최소값은 최대값보다 클 수 없습니다.');
}

$updatedBy = sql_real_escape_string(
    isset($member['mb_id'])
        ? (string) $member['mb_id']
        : ''
);

$settings = array(
    'sum_min' => $sumMin,
    'sum_max' => $sumMax,
);

foreach ($settings as $key => $value) {
    $keySql = sql_real_escape_string($key);
    $valueSql = sql_real_escape_string((string) $value);

    $sql = "insert into l_filter_setting set
                setting_key = '{$keySql}',
                setting_value = '{$valueSql}',
                updated_by = '{$updatedBy}'
            on duplicate key update
                setting_value = '{$valueSql}',
                updated_by = '{$updatedBy}'";

    sql_query($sql);
}

goto_url('./filter.result.php');
