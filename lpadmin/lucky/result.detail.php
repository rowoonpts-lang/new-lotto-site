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

$encodedMbId = isset($_GET['mb_id'])
    ? trim((string) $_GET['mb_id'])
    : '';

$targetMbId = $encodedMbId !== ''
    ? base64_decode($encodedMbId, true)
    : false;

if ($targetMbId === false || trim($targetMbId) === '') {
    alert('회원 정보가 올바르지 않습니다.');
}

$targetMbId = trim((string) $targetMbId);

$turn = isset($_GET['turn'])
    ? max(1, (int) $_GET['turn'])
    : 0;

if ($turn < 1) {
    alert('회차 정보가 올바르지 않습니다.');
}

if (!lottoCanViewMember(
    $loginMbId,
    $loginLevel,
    $targetMbId
)) {
    alert('조회 권한이 없습니다.');
}

$targetMbIdSql = sql_real_escape_string($targetMbId);

$memberRow = sql_fetch(
    "select
        mb_id,
        mb_code,
        mb_name,
        mb_hp,
        mb_type
     from g5_member
     where mb_id = '{$targetMbIdSql}'
     limit 1",
    false
);

if (
    !isset($memberRow['mb_id'])
    || trim((string) $memberRow['mb_id']) === ''
) {
    alert('회원 정보를 찾을 수 없습니다.');
}

$summaryRow = sql_fetch(
    "select
        count(*) as combination_count,
        sum(case when result_rank = 1 then 1 else 0 end) as rank1_count,
        sum(case when result_rank = 2 then 1 else 0 end) as rank2_count,
        sum(case when result_rank = 3 then 1 else 0 end) as rank3_count,
        sum(case when result_rank = 4 then 1 else 0 end) as rank4_count,
        sum(case when result_rank = 5 then 1 else 0 end) as rank5_count
     from l_member_combination
     where draw_no = '{$turn}'
       and mb_id = '{$targetMbIdSql}'",
    false
);

$combinationCount = isset($summaryRow['combination_count'])
    ? (int) $summaryRow['combination_count']
    : 0;

$rankCounts = array(
    1 => isset($summaryRow['rank1_count'])
        ? (int) $summaryRow['rank1_count']
        : 0,
    2 => isset($summaryRow['rank2_count'])
        ? (int) $summaryRow['rank2_count']
        : 0,
    3 => isset($summaryRow['rank3_count'])
        ? (int) $summaryRow['rank3_count']
        : 0,
    4 => isset($summaryRow['rank4_count'])
        ? (int) $summaryRow['rank4_count']
        : 0,
    5 => isset($summaryRow['rank5_count'])
        ? (int) $summaryRow['rank5_count']
        : 0,
);

$page = isset($_GET['page'])
    ? max(1, (int) $_GET['page'])
    : 1;

$rows = 100;

$totalPage = $combinationCount > 0
    ? (int) ceil($combinationCount / $rows)
    : 0;

if ($totalPage > 0 && $page > $totalPage) {
    $page = $totalPage;
}

$fromRecord = ($page - 1) * $rows;

$detailResult = sql_query(
    "select
        lmc_id,
        lfc_id,
        member_type,
        candidate_rank,
        candidate_cycle,
        num1,
        num2,
        num3,
        num4,
        num5,
        num6,
        distribution_type,
        distribution_batch,
        distribution_seq,
        score,
        sms_required,
        sms_status,
        sms_result_code,
        sms_sent_at,
        sms_error,
        match_count,
        bonus_match,
        result_rank,
        result_checked_at,
        created_at
     from l_member_combination
     where draw_no = '{$turn}'
       and mb_id = '{$targetMbIdSql}'
     order by distribution_seq asc, lmc_id asc
     limit {$fromRecord}, {$rows}",
    false
);

$listUrl = './result.php?turn=' . $turn;

$pagingQstr = http_build_query(array(
    'mb_id' => $encodedMbId,
    'turn' => $turn,
));

include_once(G5_LADMIN_PATH."/head.php");
?>

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <?=$turn?>회 회원 당첨 상세
        </h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-2 col-6 mb-3">
                <strong>회원코드</strong>
                <div>
                    <?=htmlspecialchars(
                        (string) $memberRow['mb_code'],
                        ENT_QUOTES
                    )?>
                </div>
            </div>

            <div class="col-md-2 col-6 mb-3">
                <strong>이름</strong>
                <div>
                    <?=htmlspecialchars(
                        (string) $memberRow['mb_name'],
                        ENT_QUOTES
                    )?>
                </div>
            </div>

            <div class="col-md-2 col-6 mb-3">
                <strong>아이디</strong>
                <div>
                    <?=htmlspecialchars(
                        (string) $memberRow['mb_id'],
                        ENT_QUOTES
                    )?>
                </div>
            </div>

            <div class="col-md-2 col-6 mb-3">
                <strong>연락처</strong>
                <div>
                    <?=htmlspecialchars(
                        (string) $memberRow['mb_hp'],
                        ENT_QUOTES
                    )?>
                </div>
            </div>

            <div class="col-md-2 col-6 mb-3">
                <strong>현재 회원등급</strong>
                <div>
                    <?=htmlspecialchars(
                        (string) $memberRow['mb_type'],
                        ENT_QUOTES
                    )?>
                </div>
            </div>

            <div class="col-md-2 col-6 mb-3">
                <strong>총 배분 조합</strong>
                <div>
                    <?=number_format($combinationCount)?>건
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <?php for ($rank = 1; $rank <= 5; $rank++) { ?>
    <div class="col-md-2 col-6">
        <div class="small-box bg-light">
            <div class="inner">
                <h3>
                    <?=number_format($rankCounts[$rank])?>
                </h3>

                <p><?=$rank?>등</p>
            </div>
        </div>
    </div>
    <?php } ?>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            배분 번호 상세
        </h3>

        <div class="card-tools">
            총 <?=number_format($combinationCount)?>건
        </div>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
            <tr>
                <th>NO</th>
                <th>번호</th>
                <th>회원등급</th>
                <th>후보순위</th>
                <th>점수</th>
                <th>배분형태</th>
                <th>배분순서</th>
                <th>일치수</th>
                <th>보너스</th>
                <th>당첨결과</th>
                <th>SMS</th>
                <th>배분일시</th>
                <th>결과확인</th>
            </tr>
            </thead>

            <tbody>
            <?php
            $displayed = 0;

            while (
                $detailResult
                && ($row = sql_fetch_array($detailResult))
            ) {
                $displayed++;

                $ballText = implode(',', array(
                    $row['num1'],
                    $row['num2'],
                    $row['num3'],
                    $row['num4'],
                    $row['num5'],
                    $row['num6'],
                ));

                $matchCount = isset($row['match_count'])
                    && $row['match_count'] !== null
                    ? (int) $row['match_count']
                    : null;

                $bonusMatch = isset($row['bonus_match'])
                    && $row['bonus_match'] !== null
                    ? (int) $row['bonus_match']
                    : null;

                $resultRank = isset($row['result_rank'])
                    && $row['result_rank'] !== null
                    ? (int) $row['result_rank']
                    : null;
            ?>
            <tr>
                <td>
                    <?=number_format(
                        $combinationCount
                        - (($page - 1) * $rows)
                        - ($displayed - 1)
                    )?>
                </td>

                <td>
                    <?php
                    $ballNumbers = explode(',', $ballText);

                    foreach ($ballNumbers as $ballNumber) {
                        $ballNumber = (int) $ballNumber;

                        if ($ballNumber <= 10) {
                            $ballClass = 'lotto_ball_style01';
                        } elseif ($ballNumber <= 20) {
                            $ballClass = 'lotto_ball_style02';
                        } elseif ($ballNumber <= 30) {
                            $ballClass = 'lotto_ball_style03';
                        } elseif ($ballNumber <= 40) {
                            $ballClass = 'lotto_ball_style04';
                        } else {
                            $ballClass = 'lotto_ball_style05';
                        }
                    ?>
                    <span class="lotto_ball <?=$ballClass?>">
                        <?=$ballNumber?>
                    </span>
                    <?php } ?>
                </td>

                <td>
                    <?=htmlspecialchars(
                        (string) $row['member_type'],
                        ENT_QUOTES
                    )?>
                </td>

                <td>
                    <?=number_format(
                        (int) $row['candidate_rank']
                    )?>
                </td>

                <td>
                    <?=number_format(
                        (float) $row['score'],
                        6
                    )?>
                </td>

                <td>
                    <?=htmlspecialchars(
                        (string) $row['distribution_type'],
                        ENT_QUOTES
                    )?>
                </td>

                <td>
                    <?=number_format(
                        (int) $row['distribution_seq']
                    )?>
                </td>

                <td>
                    <?=$matchCount !== null
                        ? $matchCount
                        : '-'?>
                </td>

                <td>
                    <?php
                    if ($bonusMatch === null) {
                        echo '-';
                    } elseif ($bonusMatch === 1) {
                        echo '일치';
                    } else {
                        echo '불일치';
                    }
                    ?>
                </td>

                <td>
                    <?php
                    if (empty($row['result_checked_at'])) {
                        echo '결과미확인';
                    } elseif (
                        $resultRank !== null
                        && $resultRank >= 1
                        && $resultRank <= 5
                    ) {
                        echo $resultRank . '등';
                    } else {
                        echo '미당첨';
                    }
                    ?>
                </td>

                <td>
                    <?=htmlspecialchars(
                        (string) $row['sms_status'],
                        ENT_QUOTES
                    )?>
                </td>

                <td>
                    <?=htmlspecialchars(
                        (string) $row['created_at'],
                        ENT_QUOTES
                    )?>
                </td>

                <td>
                    <?php if (!empty($row['result_checked_at'])) { ?>
                    <?=htmlspecialchars(
                        (string) $row['result_checked_at'],
                        ENT_QUOTES
                    )?>
                    <?php } else { ?>
                    -
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>

            <?php if ($displayed < 1) { ?>
            <tr>
                <td colspan="13">
                    해당 회차의 배분 내역이 없습니다.
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
                '?' . $pagingQstr . '&amp;page='
            );
        }
        ?>
    </div>
</div>

<div class="mb-3">
    <a
        href="<?=$listUrl?>"
        class="btn btn-secondary"
    >
        목록으로
    </a>
</div>

<?php
include_once(G5_LADMIN_PATH."/tail.php");
?>
