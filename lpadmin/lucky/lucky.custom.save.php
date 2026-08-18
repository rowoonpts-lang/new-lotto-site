<?php
include_once('_common.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    alert('잘못된 요청입니다.');
}

if (!isset($is_admin) || $is_admin !== 'super') {
    alert('최고관리자만 사용할 수 있습니다.');
}

$tableCheck = sql_fetch(
    "SHOW TABLES LIKE 'l_lucky_custom'",
    false
);

if (!is_array($tableCheck) || count($tableCheck) < 1) {
    alert('당첨번호 테이블이 없습니다.');
}

$turn = isset($_POST['turn']) ? (int) $_POST['turn'] : 0;
$numbers = array();

for ($index = 1; $index <= 7; $index++) {
    $numbers[$index] = isset($_POST['num'.$index])
        ? (int) $_POST['num'.$index]
        : 0;
}

if ($turn < 1 || $turn > 9999) {
    alert('올바른 회차를 입력해주세요.');
}

foreach ($numbers as $numberValue) {
    if ($numberValue < 1 || $numberValue > 45) {
        alert('당첨번호는 1부터 45 사이여야 합니다.');
    }
}

if (count(array_unique($numbers)) !== 7) {
    alert('당첨번호와 보너스번호는 중복될 수 없습니다.');
}

$existing = sql_fetch(
    "select lc_id
       from l_lucky_custom
      where turn = '{$turn}'
      limit 1",
    false
);

if (isset($existing['lc_id']) && (int) $existing['lc_id'] > 0) {
    alert('이미 등록된 회차입니다. 기존 자료를 삭제한 뒤 다시 등록해주세요.');
}

$sql = "
    insert into l_lucky_custom set
        turn = '{$turn}',
        num1 = '{$numbers[1]}',
        num2 = '{$numbers[2]}',
        num3 = '{$numbers[3]}',
        num4 = '{$numbers[4]}',
        num5 = '{$numbers[5]}',
        num6 = '{$numbers[6]}',
        num7 = '{$numbers[7]}',
        lc_datetime = now()
";

sql_query($sql);

alert(
    '정상적으로 등록되었습니다.',
    './lucky.custom.php'
);
