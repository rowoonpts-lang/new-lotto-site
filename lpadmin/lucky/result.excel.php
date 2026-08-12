<?php

include_once("_common.php");

$loginLevel = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if (!lottoCanViewAllMembers($loginLevel)) {
    alert('엑셀 다운로드 권한이 없습니다.');
}

$allowedSchSelect = array(
    'b.mb_name',
    'b.mb_hp',
    'b.mb_id',
    'd.mb_name',
);

$turn = isset($_GET['turn'])
    ? max(1, (int) $_GET['turn'])
    : 0;

if ($turn < 1) {
    alert('회차 정보가 올바르지 않습니다.');
}

$schSelect = isset($_GET['sch_select'])
    && in_array(
        (string) $_GET['sch_select'],
        $allowedSchSelect,
        true
    )
        ? (string) $_GET['sch_select']
        : '';

$schText = isset($_GET['sch_text'])
    ? trim((string) $_GET['sch_text'])
    : '';

$schMbType = isset($_GET['sch_mb_type'])
    ? trim((string) $_GET['sch_mb_type'])
    : '';

$luckyResult = isset($_GET['lucky_result'])
    ? max(0, (int) $_GET['lucky_result'])
    : 0;

$schTextSql = sql_real_escape_string($schText);
$schMbTypeSql = sql_real_escape_string($schMbType);

$sqlCommon = "
    from l_member_combination a

    inner join g5_member b
        on b.mb_id = a.mb_id

    left join l_member_assignment c
        on c.mb_id = a.mb_id

    left join g5_member d
        on d.mb_id = c.staff_mb_id
";

$sqlSearch = "
    where a.draw_no = '{$turn}'
      and a.result_rank between 1 and 5
";

if ($schText !== '') {
    if ($schSelect !== '') {
        $sqlSearch .= "
            and {$schSelect}
                like '%{$schTextSql}%'
        ";
    } else {
        $sqlSearch .= "
            and (
                b.mb_name like '%{$schTextSql}%'
                or b.mb_hp like '%{$schTextSql}%'
                or b.mb_id like '%{$schTextSql}%'
                or d.mb_name like '%{$schTextSql}%'
            )
        ";
    }
}

if ($schMbType !== '') {
    $sqlSearch .= "
        and a.member_type = '{$schMbTypeSql}'
    ";
}

if (
    $luckyResult >= 1
    && $luckyResult <= 5
) {
    $sqlSearch .= "
        and a.result_rank = '{$luckyResult}'
    ";
}

$countRow = sql_fetch(
    "select count(*) as cnt
     {$sqlCommon}
     {$sqlSearch}",
    false
);

$totalCount = isset($countRow['cnt'])
    ? (int) $countRow['cnt']
    : 0;

$excelResult = sql_query(
    "select
        a.draw_no,
        a.member_type,
        a.num1,
        a.num2,
        a.num3,
        a.num4,
        a.num5,
        a.num6,
        a.result_rank,
        a.created_at,

        b.mb_name,
        b.mb_hp,

        d.mb_name as staff_name

     {$sqlCommon}
     {$sqlSearch}

     order by
        a.result_rank asc,
        a.lmc_id desc",
    false
);

$filename = 'lotto_result_' . $turn . '.xls';

header("Content-type: application/vnd.ms-excel");
header("Content-type: application/vnd.ms-excel; charset=utf-8");
header(
    "Content-Disposition: attachment; filename={$filename}"
);
header("Content-Description: PHP Generated Data");

?>
<meta
    http-equiv="Content-Type"
    content="application/vnd.ms-excel; charset=UTF-8"
>

<table border="1">
    <thead>
    <tr>
        <th>NO</th>
        <th>회차</th>
        <th>이름</th>
        <th>연락처</th>
        <th>등급</th>
        <th>담당자</th>
        <th>번호</th>
        <th>당첨결과</th>
        <th>배분일시</th>
    </tr>
    </thead>

    <tbody>
    <?php
    for (
        $i = 0;
        $excelResult
        && ($row = sql_fetch_array($excelResult));
        $i++
    ) {
        $staffName = isset($row['staff_name'])
            ? trim((string) $row['staff_name'])
            : '';

        $ballText = implode(',', array(
            (int) $row['num1'],
            (int) $row['num2'],
            (int) $row['num3'],
            (int) $row['num4'],
            (int) $row['num5'],
            (int) $row['num6'],
        ));
    ?>
    <tr>
        <td>
            <?=$totalCount - $i?>
        </td>

        <td>
            <?=(int) $row['draw_no']?>
        </td>

        <td>
            <?=htmlspecialchars(
                (string) $row['mb_name'],
                ENT_QUOTES
            )?>
        </td>

        <td style="mso-number-format:'\@';">
            <?=htmlspecialchars(
                (string) $row['mb_hp'],
                ENT_QUOTES
            )?>
        </td>

        <td>
            <?=htmlspecialchars(
                (string) $row['member_type'],
                ENT_QUOTES
            )?>
        </td>

        <td>
            <?=$staffName !== ''
                ? htmlspecialchars(
                    $staffName,
                    ENT_QUOTES
                )
                : '-'?>
        </td>

        <td style="mso-number-format:'\@';">
            <?=htmlspecialchars(
                $ballText,
                ENT_QUOTES
            )?>
        </td>

        <td>
            <?=(int) $row['result_rank']?>등
        </td>

        <td>
            <?=htmlspecialchars(
                (string) $row['created_at'],
                ENT_QUOTES
            )?>
        </td>
    </tr>
    <?php } ?>
    </tbody>
</table>
