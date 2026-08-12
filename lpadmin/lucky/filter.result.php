<?php

include_once("_common.php");
include_once(G5_LADMIN_PATH."/head.php");

$loginMbId = isset($member['mb_id'])
    ? (string) $member['mb_id']
    : '';

$loginLevel = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if (!lottoCanViewAllMembers($loginLevel)) {
    alert('접근 권한이 없습니다.');
}

$latestResultRow = sql_fetch(
    "select max(draw_no) as draw_no
       from g5_lotto_result",
    false
);

$latestResultTurn = isset($latestResultRow['draw_no'])
    ? (int) $latestResultRow['draw_no']
    : 0;

$currentTurn = $latestResultTurn > 0
    ? $latestResultTurn + 1
    : 0;

$previousTurn = $latestResultTurn;

$sumMin = 100;
$sumMax = 190;

$resultSetting = sql_query(
    "select setting_key, setting_value
       from l_filter_setting
      where setting_key in ('sum_min', 'sum_max')",
    false
);

while ($resultSetting && ($settingRow = sql_fetch_array($resultSetting))) {
    if ($settingRow['setting_key'] === 'sum_min') {
        $sumMin = (int) $settingRow['setting_value'];
    }

    if ($settingRow['setting_key'] === 'sum_max') {
        $sumMax = (int) $settingRow['setting_value'];
    }
}

$currentRun = array(
    'lfr_id' => 0,
    'source_draw_no' => 0,
    'status' => '',
    'candidate_count' => 0,
    'excluded_numbers' => '',
    'started_at' => '',
    'filtered_at' => '',
    'ranked_at' => '',
    'last_error' => '',
);

if ($currentTurn > 0) {
    $currentRunRow = sql_fetch(
        "select
            lfr_id,
            source_draw_no,
            status,
            candidate_count,
            excluded_numbers,
            started_at,
            filtered_at,
            ranked_at,
            last_error
         from l_filter_run
         where draw_no = '{$currentTurn}'
         limit 1",
        false
    );

    if (
        isset($currentRunRow['lfr_id'])
        && (int) $currentRunRow['lfr_id'] > 0
    ) {
        $currentRun = $currentRunRow;
    }
}

$currentCandidateCount = isset($currentRun['candidate_count'])
    ? (int) $currentRun['candidate_count']
    : 0;

$previousCandidateCount = 0;

if ($previousTurn > 0) {
    $row = sql_fetch(
        "select candidate_count
           from l_filter_run
          where draw_no = '{$previousTurn}'
          limit 1",
        false
    );

    if (isset($row['candidate_count'])) {
        $previousCandidateCount = (int) $row['candidate_count'];
    }
}

$candidatePage = isset($_GET['candidate_page'])
    ? max(1, (int) $_GET['candidate_page'])
    : 1;

$candidateRows = 50;
$candidateTotalCount = 0;
$candidateTotalPage = 0;
$candidateFromRecord = 0;
$candidateResult = false;

if ($currentTurn > 0) {
    $candidateCountRow = sql_fetch(
        "select count(*) as cnt
           from l_filter_candidate
          where draw_no = '{$currentTurn}'",
        false
    );

    $candidateTotalCount = isset($candidateCountRow['cnt'])
        ? (int) $candidateCountRow['cnt']
        : 0;

    if ($candidateTotalCount > 0) {
        $candidateTotalPage = (int) ceil(
            $candidateTotalCount / $candidateRows
        );

        if ($candidatePage > $candidateTotalPage) {
            $candidatePage = $candidateTotalPage;
        }

        $candidateFromRecord =
            ($candidatePage - 1) * $candidateRows;

        $candidateResult = sql_query(
            "select
                lfc_id,
                rank_no,
                num1,
                num2,
                num3,
                num4,
                num5,
                num6,
                score,
                sum_value,
                ac_value,
                odd_count,
                low_count,
                carry_count,
                neighbor_count,
                prime_count,
                multiple3_count,
                max_consecutive,
                empty_zone_count,
                created_at
             from l_filter_candidate
             where draw_no = '{$currentTurn}'
             order by rank_no asc
             limit {$candidateFromRecord}, {$candidateRows}",
            false
        );
    }
}

$canManageSetting = lottoCanManageAdminSettings($loginLevel);
?>

<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">총합수 필터 설정</h3>
    </div>

    <form method="post" action="./filter.result.update.php">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-2">
                    <label>최소값</label>
                    <input
                        type="number"
                        name="sum_min"
                        class="form-control"
                        value="<?=$sumMin?>"
                        min="21"
                        max="255"
                        <?=$canManageSetting ? '' : 'readonly'?>
                    >
                </div>

                <div class="col-md-2">
                    <label>최대값</label>
                    <input
                        type="number"
                        name="sum_max"
                        class="form-control"
                        value="<?=$sumMax?>"
                        min="21"
                        max="255"
                        <?=$canManageSetting ? '' : 'readonly'?>
                    >
                </div>

                <div class="col-md-2">
                    <?php if ($canManageSetting) { ?>
                    <button
                        type="submit"
                        class="btn btn-primary btn-block"
                    >
                        저장
                    </button>
                    <?php } ?>
                </div>

                <div class="col-md-6">
                    기본값은 100 ~ 190이며,
                    저장된 값은 다음 필터 실행부터 적용합니다.
                </div>
            </div>
        </div>
    </form>
</div>

<?php
$runStatus = isset($currentRun['status'])
    ? trim((string) $currentRun['status'])
    : '';

$runStatusLabels = array(
    'running' => '실행중',
    'filtered' => '필터완료',
    'failed' => '실패',
);

if ($runStatus === '') {
    $runStatusText = '미실행';
} elseif (isset($runStatusLabels[$runStatus])) {
    $runStatusText = $runStatusLabels[$runStatus];
} else {
    $runStatusText = $runStatus;
}

$sourceDrawNo = isset($currentRun['source_draw_no'])
    ? (int) $currentRun['source_draw_no']
    : 0;

if ($sourceDrawNo < 1) {
    $sourceDrawNo = $previousTurn;
}

$excludedNumbers = isset($currentRun['excluded_numbers'])
    ? trim((string) $currentRun['excluded_numbers'])
    : '';

$startedAt = isset($currentRun['started_at'])
    ? trim((string) $currentRun['started_at'])
    : '';

$filteredAt = isset($currentRun['filtered_at'])
    ? trim((string) $currentRun['filtered_at'])
    : '';

$rankedAt = isset($currentRun['ranked_at'])
    ? trim((string) $currentRun['ranked_at'])
    : '';

$lastError = isset($currentRun['last_error'])
    ? trim((string) $currentRun['last_error'])
    : '';
?>

<div class="card card-info">
    <div class="card-header">
        <h3 class="card-title">필터 실행 상태</h3>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-2 col-6 mb-3">
                <strong>대상 회차</strong>
                <div>
                    <?=number_format($currentTurn)?>회
                </div>
            </div>

            <div class="col-md-2 col-6 mb-3">
                <strong>기준 회차</strong>
                <div>
                    <?=$sourceDrawNo > 0
                        ? number_format($sourceDrawNo) . '회'
                        : '-'?>
                </div>
            </div>

            <div class="col-md-2 col-6 mb-3">
                <strong>실행 상태</strong>
                <div>
                    <?=htmlspecialchars(
                        $runStatusText,
                        ENT_QUOTES
                    )?>
                </div>
            </div>

            <div class="col-md-2 col-6 mb-3">
                <strong>최종 후보</strong>
                <div>
                    <?=number_format(
                        $currentCandidateCount
                    )?>건
                </div>
            </div>

            <div class="col-md-4 col-12 mb-3">
                <strong>추천 제외수</strong>
                <div>
                    <?=$excludedNumbers !== ''
                        ? htmlspecialchars(
                            $excludedNumbers,
                            ENT_QUOTES
                        )
                        : '-'?>
                </div>
            </div>

            <div class="col-md-3 col-6 mb-3">
                <strong>시작 시각</strong>
                <div>
                    <?=$startedAt !== ''
                        ? htmlspecialchars(
                            $startedAt,
                            ENT_QUOTES
                        )
                        : '-'?>
                </div>
            </div>

            <div class="col-md-3 col-6 mb-3">
                <strong>필터 완료</strong>
                <div>
                    <?=$filteredAt !== ''
                        ? htmlspecialchars(
                            $filteredAt,
                            ENT_QUOTES
                        )
                        : '-'?>
                </div>
            </div>

            <div class="col-md-3 col-6 mb-3">
                <strong>랭킹 완료</strong>
                <div>
                    <?=$rankedAt !== ''
                        ? htmlspecialchars(
                            $rankedAt,
                            ENT_QUOTES
                        )
                        : '-'?>
                </div>
            </div>

            <div class="col-md-3 col-12 mb-3">
                <strong>최근 오류</strong>
                <div>
                    <?=$lastError !== ''
                        ? htmlspecialchars(
                            $lastError,
                            ENT_QUOTES
                        )
                        : '-'?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <?=$currentTurn?>회 최종 필터 후보
        </h3>

        <div class="card-tools">
            총 <?=number_format($candidateTotalCount)?>건
        </div>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap">
            <thead>
            <tr>
                <th>순위</th>
                <th>번호</th>
                <th>Trend 점수</th>
                <th>총합</th>
                <th>AC</th>
                <th>홀짝</th>
                <th>저고</th>
                <th>이월수</th>
                <th>이웃수</th>
                <th>소수</th>
                <th>3배수</th>
                <th>최대연번</th>
                <th>멸구간</th>
            </tr>
            </thead>

            <tbody>
            <?php
            $candidateDisplayed = 0;

            while (
                $candidateResult
                && ($candidateRow = sql_fetch_array($candidateResult))
            ) {
                $candidateDisplayed++;

                $candidateBallText = implode(',', array(
                    $candidateRow['num1'],
                    $candidateRow['num2'],
                    $candidateRow['num3'],
                    $candidateRow['num4'],
                    $candidateRow['num5'],
                    $candidateRow['num6'],
                ));

                $oddCount = (int) $candidateRow['odd_count'];
                $evenCount = 6 - $oddCount;

                $lowCount = (int) $candidateRow['low_count'];
                $highCount = 6 - $lowCount;
            ?>
            <tr>
                <td>
                    <?=number_format(
                        (int) $candidateRow['rank_no']
                    )?>
                </td>

                <td>
                    <?php
                    $ballNumbers = explode(',', $candidateBallText);

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
                    <?=number_format(
                        (float) $candidateRow['score'],
                        6
                    )?>
                </td>

                <td>
                    <?=(int) $candidateRow['sum_value']?>
                </td>

                <td>
                    <?=(int) $candidateRow['ac_value']?>
                </td>

                <td>
                    <?=$oddCount?>:<?=$evenCount?>
                </td>

                <td>
                    <?=$lowCount?>:<?=$highCount?>
                </td>

                <td>
                    <?=(int) $candidateRow['carry_count']?>
                </td>

                <td>
                    <?=(int) $candidateRow['neighbor_count']?>
                </td>

                <td>
                    <?=(int) $candidateRow['prime_count']?>
                </td>

                <td>
                    <?=(int) $candidateRow['multiple3_count']?>
                </td>

                <td>
                    <?=(int) $candidateRow['max_consecutive']?>
                </td>

                <td>
                    <?=(int) $candidateRow['empty_zone_count']?>
                </td>
            </tr>
            <?php } ?>

            <?php if ($candidateDisplayed < 1) { ?>
            <tr>
                <td colspan="13">
                    현재 회차의 필터 후보가 없습니다.
                </td>
            </tr>
            <?php } ?>
            </tbody>
        </table>

        <?php
        if ($candidateTotalPage > 1) {
            $candidateQstr = '';

            $candidatePaging = get_paging(
                G5_IS_MOBILE
                    ? $config['cf_mobile_pages']
                    : $config['cf_write_pages'],
                $candidatePage,
                $candidateTotalPage,
                '?' . $candidateQstr
            );

            $candidatePaging = str_replace(
                '&amp;page=',
                '&amp;candidate_page=',
                $candidatePaging
            );

            echo $candidatePaging;
        }
        ?>
    </div>
</div>

<div class="row">
    <div class="col-md-2 col-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h3><?=number_format($previousCandidateCount)?></h3>
                <p><?=$previousTurn?>회 총조합</p>
            </div>
        </div>
    </div>
</div>

<?php
include_once(G5_LADMIN_PATH."/tail.php");
?>
