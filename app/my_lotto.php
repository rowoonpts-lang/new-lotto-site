<?php

include_once __DIR__ . '/_common.php';
include_once G5_PATH . '/include/lotto_app_access.lib.php';
include_once G5_PATH . '/include/lotto_result.lib.php';

lottoAppRequirePaidMember();

$memberId = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$safeMemberId = sql_real_escape_string($memberId);
$resultTable = lotto_result_table_name();

$drawResults = array();

$resultQuery = sql_query(
    "select
        draw_no,
        draw_date,
        num_1,
        num_2,
        num_3,
        num_4,
        num_5,
        num_6,
        bonus_num
     from `{$resultTable}`
     order by draw_no desc",
    false
);

if ($resultQuery) {
    while ($row = sql_fetch_array($resultQuery)) {
        $drawResults[(int) $row['draw_no']] = $row;
    }
}

function lottoAppMyRank($numbers, $result)
{
    if (!is_array($numbers) || count($numbers) !== 6) {
        return '';
    }

    if (!is_array($result) || empty($result['draw_no'])) {
        return '추첨대기';
    }

    $winningNumbers = array(
        (int) $result['num_1'],
        (int) $result['num_2'],
        (int) $result['num_3'],
        (int) $result['num_4'],
        (int) $result['num_5'],
        (int) $result['num_6'],
    );

    $numbers = array_map('intval', $numbers);

    $matchCount = count(
        array_intersect($numbers, $winningNumbers)
    );

    $bonusMatch = in_array(
        (int) $result['bonus_num'],
        $numbers,
        true
    );

    if ($matchCount === 6) {
        return '1등';
    }

    if ($matchCount === 5 && $bonusMatch) {
        return '2등';
    }

    if ($matchCount === 5) {
        return '3등';
    }

    if ($matchCount === 4) {
        return '4등';
    }

    if ($matchCount === 3) {
        return '5등';
    }

    return '미당첨';
}

function lottoAppMyBallClass($number)
{
    $number = (int) $number;

    if ($number <= 10) {
        return 'yellow';
    }

    if ($number <= 20) {
        return 'blue';
    }

    if ($number <= 30) {
        return 'red';
    }

    if ($number <= 40) {
        return 'gray';
    }

    return 'green';
}

$memberRows = array();
$winningRows = array();
$memberDraws = array();

$rankCounts = array(
    '1등' => 0,
    '2등' => 0,
    '3등' => 0,
    '4등' => 0,
    '5등' => 0,
);

$combinationQuery = sql_query(
    "select
        lmc_id,
        draw_no,
        member_type,
        num1,
        num2,
        num3,
        num4,
        num5,
        num6,
        created_at
     from l_member_combination
     where mb_id = '{$safeMemberId}'
     order by draw_no desc, created_at desc, lmc_id desc",
    false
);

if ($combinationQuery) {
    while ($row = sql_fetch_array($combinationQuery)) {
        $drawNo = isset($row['draw_no'])
            ? (int) $row['draw_no']
            : 0;

        if ($drawNo < 1) {
            continue;
        }

        $numbers = array(
            (int) $row['num1'],
            (int) $row['num2'],
            (int) $row['num3'],
            (int) $row['num4'],
            (int) $row['num5'],
            (int) $row['num6'],
        );

        $drawResult = isset($drawResults[$drawNo])
            ? $drawResults[$drawNo]
            : array();

        $rank = lottoAppMyRank(
            $numbers,
            $drawResult
        );

        $item = array(
            'draw_no' => $drawNo,
            'draw_date' => isset($drawResult['draw_date'])
                ? (string) $drawResult['draw_date']
                : '',
            'numbers' => $numbers,
            'rank' => $rank,
            'issued_at' => isset($row['created_at'])
                ? (string) $row['created_at']
                : '',
        );

        $memberRows[] = $item;
        $memberDraws[$drawNo] = true;

        if (isset($rankCounts[$rank])) {
            $rankCounts[$rank]++;
            $winningRows[] = $item;
        }
    }
}

$drawOptions = array_keys($memberDraws);
rsort($drawOptions, SORT_NUMERIC);

$selectedDraw = isset($_GET['turn'])
    ? (int) $_GET['turn']
    : (!empty($drawOptions) ? (int) $drawOptions[0] : 0);

if (
    $selectedDraw > 0
    && !in_array($selectedDraw, $drawOptions, true)
) {
    $selectedDraw = !empty($drawOptions)
        ? (int) $drawOptions[0]
        : 0;
}

$selectedRows = array();

foreach ($memberRows as $item) {
    if ((int) $item['draw_no'] === $selectedDraw) {
        $selectedRows[] = $item;
    }
}

$totalGames = count($memberRows);
$totalDraws = count($memberDraws);
$totalWins = count($winningRows);
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#ffffff">
<title>내 조합 - LottoGPT</title>
<style>
body {
    margin: 0;
    background: #f5f6f8;
    color: #1f2937;
    font-family: Arial, sans-serif;
}
.app-shell {
    max-width: 520px;
    margin: 0 auto;
    padding: 24px 18px 48px;
}
.app-back {
    display: inline-block;
    margin-bottom: 20px;
    color: #374151;
    text-decoration: none;
}
.app-card {
    margin-top: 16px;
    padding: 20px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
}
.summary-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 8px;
}
.summary-grid div {
    padding: 12px 8px;
    background: #f9fafb;
    border-radius: 10px;
    text-align: center;
}
.summary-grid span {
    display: block;
    font-size: 12px;
    color: #6b7280;
}
.summary-grid strong {
    display: block;
    margin-top: 5px;
}
.app-select {
    width: 100%;
    padding: 12px;
    font-size: 16px;
}
.combo-item {
    padding: 16px 0;
    border-bottom: 1px solid #e5e7eb;
}
.combo-item:last-child {
    border-bottom: 0;
}
.combo-head {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 10px;
}
.combo-head small {
    color: #6b7280;
}
.ball-row {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
}
.ball {
    width: 38px;
    height: 38px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: #fff;
    font-weight: 700;
}
.ball.yellow { background: #d6a820; }
.ball.blue { background: #3b82c4; }
.ball.red { background: #dc5454; }
.ball.gray { background: #71717a; }
.ball.green { background: #2f9e68; }
.rank {
    display: inline-block;
    margin-top: 10px;
    padding: 5px 9px;
    background: #eef2f7;
    border-radius: 8px;
    font-weight: 700;
}
.win-item {
    padding: 14px 0;
    border-bottom: 1px solid #e5e7eb;
}
.win-item:last-child {
    border-bottom: 0;
}
.empty {
    color: #6b7280;
    line-height: 1.6;
}
</style>
</head>
<body>
<main class="app-shell">

    <a class="app-back" href="<?=htmlspecialchars(G5_URL . '/app/', ENT_QUOTES, 'UTF-8')?>">
        ← 회원앱 홈
    </a>

    <h1>내 조합</h1>

    <section class="app-card">
        <div class="summary-grid">
            <div>
                <span>제공 회차</span>
                <strong><?=number_format($totalDraws)?>회</strong>
            </div>

            <div>
                <span>제공 조합</span>
                <strong><?=number_format($totalGames)?>게임</strong>
            </div>

            <div>
                <span>당첨 조합</span>
                <strong><?=number_format($totalWins)?>게임</strong>
            </div>
        </div>
    </section>

    <section class="app-card">
        <h2>회차별 받은 조합</h2>

        <?php if (!empty($drawOptions)) { ?>
        <form method="get">
            <select
                class="app-select"
                name="turn"
                onchange="this.form.submit()"
                aria-label="조회 회차"
            >
                <?php foreach ($drawOptions as $drawNo) { ?>
                <option
                    value="<?=$drawNo?>"
                    <?=$selectedDraw === (int) $drawNo ? 'selected' : ''?>
                >
                    <?=$drawNo?>회
                </option>
                <?php } ?>
            </select>
        </form>
        <?php } ?>

        <?php if (empty($selectedRows)) { ?>

        <p class="empty">
            이 계정에 저장된 조합이 없습니다.
        </p>

        <?php } else { ?>

            <?php foreach ($selectedRows as $item) { ?>
            <article class="combo-item">

                <div class="combo-head">
                    <strong><?=$item['draw_no']?>회</strong>

                    <small>
                        <?=!empty($item['issued_at'])
                            ? htmlspecialchars(
                                date(
                                    'Y-m-d',
                                    strtotime($item['issued_at'])
                                ),
                                ENT_QUOTES,
                                'UTF-8'
                            )
                            : '-'?>
                    </small>
                </div>

                <div class="ball-row">
                    <?php foreach ($item['numbers'] as $number) { ?>
                    <span class="ball <?=lottoAppMyBallClass($number)?>">
                        <?=$number?>
                    </span>
                    <?php } ?>
                </div>

                <span class="rank">
                    <?=htmlspecialchars(
                        $item['rank'],
                        ENT_QUOTES,
                        'UTF-8'
                    )?>
                </span>

            </article>
            <?php } ?>

        <?php } ?>
    </section>

    <section class="app-card">
        <h2>내 당첨결과</h2>

        <?php if (empty($winningRows)) { ?>

        <p class="empty">
            확인된 당첨 내역이 없습니다.
        </p>

        <?php } else { ?>

            <?php foreach ($winningRows as $item) { ?>
            <article class="win-item">

                <strong>
                    <?=$item['draw_no']?>회 /
                    <?=htmlspecialchars(
                        $item['rank'],
                        ENT_QUOTES,
                        'UTF-8'
                    )?>
                </strong>

                <div class="ball-row" style="margin-top:10px;">
                    <?php foreach ($item['numbers'] as $number) { ?>
                    <span class="ball <?=lottoAppMyBallClass($number)?>">
                        <?=$number?>
                    </span>
                    <?php } ?>
                </div>

                <?php if ($item['draw_date'] !== '') { ?>
                <small>
                    추첨일:
                    <?=htmlspecialchars(
                        $item['draw_date'],
                        ENT_QUOTES,
                        'UTF-8'
                    )?>
                </small>
                <?php } ?>

            </article>
            <?php } ?>

        <?php } ?>
    </section>

</main>
</body>
</html>
