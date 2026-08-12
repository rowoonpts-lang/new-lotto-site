<?php

include_once("_common.php");

$loginMbId = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$loginLevel = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if (!lottoIsStaffLevel($loginLevel)) {
    alert('접근 권한이 없습니다.');
}

$canViewAll = lottoCanViewAllMembers($loginLevel);

$latestResultRow = sql_fetch(
    "select max(draw_no) as draw_no
       from l_member_combination
      where result_rank between 1 and 5",
    false
);

$turn = isset($_GET['turn'])
    ? max(1, (int) $_GET['turn'])
    : (
        isset($latestResultRow['draw_no'])
            ? (int) $latestResultRow['draw_no']
            : 0
    );

if ($turn < 1) {
    $latestDrawRow = sql_fetch(
        "select max(draw_no) as draw_no
           from g5_lotto_result",
        false
    );

    $turn = isset($latestDrawRow['draw_no'])
        ? (int) $latestDrawRow['draw_no']
        : 0;
}

$allowedSchSelect = array(
    'b.mb_name',
    'b.mb_hp',
    'b.mb_id',
    'd.mb_name',
);

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

$page = isset($_GET['page'])
    ? max(1, (int) $_GET['page'])
    : 1;

$allowedMemberIdsSql = '';

if (!$canViewAll) {
    $staffIds = array($loginMbId);

    if (
        $loginLevel === LOTTO_ROLE_STAFF2
        || $loginLevel === LOTTO_ROLE_TEAM_LEADER
    ) {
        $childStaffIds =
            lottoGetDirectChildStaffIds($loginMbId);

        foreach ($childStaffIds as $childStaffId) {
            $staffIds[] = $childStaffId;
        }
    }

    $staffIds = array_values(
        array_unique($staffIds)
    );

    $staffIdsSql = array();

    foreach ($staffIds as $staffId) {
        $staffIdsSql[] =
            "'" . sql_real_escape_string($staffId) . "'";
    }

    if (count($staffIdsSql) > 0) {
        $allowedMemberIdsSql = "
            and c.staff_mb_id in (
                " . implode(',', $staffIdsSql) . "
            )
        ";
    } else {
        $allowedMemberIdsSql = " and 1 = 0 ";
    }
}

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
      {$allowedMemberIdsSql}
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

$rankCounts = array(
    1 => 0,
    2 => 0,
    3 => 0,
    4 => 0,
    5 => 0,
);

$rankResult = sql_query(
    "select
        a.result_rank,
        count(*) as cnt
     {$sqlCommon}
     {$sqlSearch}
     group by a.result_rank",
    false
);

while (
    $rankResult
    && ($rankRow = sql_fetch_array($rankResult))
) {
    $rank = (int) $rankRow['result_rank'];

    if (isset($rankCounts[$rank])) {
        $rankCounts[$rank] =
            (int) $rankRow['cnt'];
    }
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

$rows = 50;

$totalPage = $totalCount > 0
    ? (int) ceil($totalCount / $rows)
    : 0;

if (
    $totalPage > 0
    && $page > $totalPage
) {
    $page = $totalPage;
}

$fromRecord =
    ($page - 1) * $rows;

$resultList = sql_query(
    "select
        a.lmc_id,
        a.draw_no,
        a.mb_id,
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

        c.staff_mb_id,

        d.mb_name as staff_name

     {$sqlCommon}
     {$sqlSearch}

     order by
        a.result_rank asc,
        a.lmc_id desc

     limit {$fromRecord}, {$rows}",
    false
);

$qstr = http_build_query(array(
    'turn' => $turn,
    'sch_select' => $schSelect,
    'sch_text' => $schText,
    'sch_mb_type' => $schMbType,
    'lucky_result' => $luckyResult,
));

include_once(G5_LADMIN_PATH."/head.php");
?>

<div class="row">
    <?php for ($rank = 1; $rank <= 5; $rank++) { ?>
    <div class="col-md-2 col-6">
        <div class="small-box bg-light">
            <div class="inner">
                <h3>
                    <?=number_format(
                        $rankCounts[$rank]
                    )?>
                </h3>

                <p><?=$rank?>등</p>
            </div>
        </div>
    </div>
    <?php } ?>
</div>

<div class="card card-default">
    <div class="card-body">
        <form
            method="get"
            autocomplete="off"
        >
            <div class="row">
                <div class="col-md-1">
                    <input
                        type="number"
                        class="form-control"
                        name="turn"
                        value="<?=$turn?>"
                        min="1"
                        placeholder="회차"
                    >
                </div>

                <div class="col-md-2">
                    <select
                        class="form-control"
                        name="sch_select"
                    >
                        <option value="">
                            전체검색
                        </option>

                        <option
                            value="b.mb_name"
                            <?=$schSelect === 'b.mb_name'
                                ? 'selected'
                                : ''?>
                        >
                            이름
                        </option>

                        <option
                            value="b.mb_hp"
                            <?=$schSelect === 'b.mb_hp'
                                ? 'selected'
                                : ''?>
                        >
                            연락처
                        </option>

                        <option
                            value="b.mb_id"
                            <?=$schSelect === 'b.mb_id'
                                ? 'selected'
                                : ''?>
                        >
                            아이디
                        </option>

                        <option
                            value="d.mb_name"
                            <?=$schSelect === 'd.mb_name'
                                ? 'selected'
                                : ''?>
                        >
                            담당자
                        </option>
                    </select>
                </div>

                <div class="col-md-2">
                    <input
                        type="text"
                        class="form-control"
                        name="sch_text"
                        value="<?=htmlspecialchars(
                            $schText,
                            ENT_QUOTES
                        )?>"
                        placeholder="검색어"
                    >
                </div>

                <div class="col-md-2">
                    <select
                        class="form-control"
                        name="sch_mb_type"
                    >
                        <option value="">
                            등급전체
                        </option>

                        <?php
                        $memberTypes = fnGetType();

                        foreach (
                            $memberTypes as $memberType
                        ) {
                        ?>
                        <option
                            value="<?=$memberType?>"
                            <?=$schMbType === $memberType
                                ? 'selected'
                                : ''?>
                        >
                            <?=$memberType?>
                        </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <select
                        class="form-control"
                        name="lucky_result"
                    >
                        <option value="0">
                            당첨전체
                        </option>

                        <?php
                        for (
                            $rank = 1;
                            $rank <= 5;
                            $rank++
                        ) {
                        ?>
                        <option
                            value="<?=$rank?>"
                            <?=$luckyResult === $rank
                                ? 'selected'
                                : ''?>
                        >
                            <?=$rank?>등
                        </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-1">
                    <button
                        type="submit"
                        class="btn btn-danger btn-block"
                    >
                        검색
                    </button>
                </div>

                <?php if ($canViewAll) { ?>
                <div class="col-md-2">
                    <a
                        href="./result.excel.php?<?=$qstr?>"
                        class="btn btn-success btn-block"
                    >
                        엑셀 다운로드
                    </a>
                </div>
                <?php } ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <?=$turn?>회 당첨결과
        </h3>

        <div class="card-tools">
            총 <?=number_format($totalCount)?>건
        </div>
    </div>

    <div class="card-body table-responsive p-0">
        <table
            class="table table-hover text-nowrap"
        >
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
                $resultList
                && ($row = sql_fetch_array($resultList));
                $i++
            ) {
                $detailUrl =
                    './result.detail.php?mb_id='
                    . urlencode(
                        base64_encode($row['mb_id'])
                    )
                    . '&turn='
                    . (int) $row['draw_no'];

                $ballNumbers = array(
                    (int) $row['num1'],
                    (int) $row['num2'],
                    (int) $row['num3'],
                    (int) $row['num4'],
                    (int) $row['num5'],
                    (int) $row['num6'],
                );

                $staffName = isset($row['staff_name'])
                    ? trim(
                        (string) $row['staff_name']
                    )
                    : '';
            ?>
            <tr>
                <td>
                    <?=$totalCount
                        - (($page - 1) * $rows)
                        - $i?>
                </td>

                <td>
                    <?=(int) $row['draw_no']?>
                </td>

                <td>
                    <a href="<?=$detailUrl?>">
                        <?=htmlspecialchars(
                            (string) $row['mb_name'],
                            ENT_QUOTES
                        )?>
                    </a>
                </td>

                <td>
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

                <td>
                    <?php
                    foreach (
                        $ballNumbers as $ballNumber
                    ) {
                        if ($ballNumber <= 10) {
                            $ballClass =
                                'lotto_ball_style01';
                        } elseif ($ballNumber <= 20) {
                            $ballClass =
                                'lotto_ball_style02';
                        } elseif ($ballNumber <= 30) {
                            $ballClass =
                                'lotto_ball_style03';
                        } elseif ($ballNumber <= 40) {
                            $ballClass =
                                'lotto_ball_style04';
                        } else {
                            $ballClass =
                                'lotto_ball_style05';
                        }
                    ?>
                    <span
                        class="lotto_ball <?=$ballClass?>"
                    >
                        <?=$ballNumber?>
                    </span>
                    <?php } ?>
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

            <?php if ($totalCount < 1) { ?>
            <tr>
                <td colspan="9">
                    당첨 내역이 없습니다.
                </td>
            </tr>
            <?php } ?>
            </tbody>
        </table>

        <?php
        if ($totalPage > 1) {
            echo get_paging(
                G5_IS_MOBILE
                    ? $config['cf_mobile_pages']
                    : $config['cf_write_pages'],
                $page,
                $totalPage,
                '?' . $qstr
            );
        }
        ?>
    </div>
</div>

<?php
include_once(G5_LADMIN_PATH."/tail.php");
?>
