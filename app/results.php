<?php

include_once __DIR__ . '/_common.php';
include_once G5_PATH . '/include/lotto_app_access.lib.php';
include_once G5_PATH . '/include/lotto_result.lib.php';

lottoAppRequirePaidMember();

$resultTable = lotto_result_table_name();
$latestResult = lotto_result_get_latest_saved();

$latestDraw = !empty($latestResult['draw_no'])
    ? (int) $latestResult['draw_no']
    : 0;

$selectedDraw = isset($_GET['turn'])
    ? (int) $_GET['turn']
    : $latestDraw;

if ($selectedDraw < 1) {
    $selectedDraw = $latestDraw;
}

$drawOptions = array();

$drawQuery = sql_query(
    "select draw_no, draw_date
     from `{$resultTable}`
     order by draw_no desc",
    false
);

if ($drawQuery) {
    while ($row = sql_fetch_array($drawQuery)) {
        $drawOptions[] = array(
            'draw_no' => (int) $row['draw_no'],
            'draw_date' => isset($row['draw_date'])
                ? (string) $row['draw_date']
                : '',
        );
    }
}

$selectedResult = array();

if ($selectedDraw > 0) {
    $selectedResult = sql_fetch(
        "select
            draw_no,
            draw_date,
            num_1,
            num_2,
            num_3,
            num_4,
            num_5,
            num_6,
            bonus_num,
            rank1_winners,
            rank1_amount,
            rank2_winners,
            rank2_amount,
            rank3_winners,
            rank3_amount,
            rank4_winners,
            rank4_amount,
            rank5_winners,
            rank5_amount
         from `{$resultTable}`
         where draw_no = '{$selectedDraw}'
         limit 1",
        false
    );
}

if (!is_array($selectedResult)) {
    $selectedResult = array();
}

function lottoAppBallClass($number)
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
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#ffffff">
<title>당첨결과 - LottoGPT</title>
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
    margin-bottom: 22px;
    color: #374151;
    text-decoration: none;
}
.app-card {
    margin-top: 18px;
    padding: 22px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
}
.app-select {
    width: 100%;
    padding: 12px;
    font-size: 16px;
}
.app-balls {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 18px;
}
.app-ball {
    width: 42px;
    height: 42px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: #fff;
    font-weight: 700;
}
.app-ball.yellow { background: #d6a820; }
.app-ball.blue { background: #3b82c4; }
.app-ball.red { background: #dc5454; }
.app-ball.gray { background: #71717a; }
.app-ball.green { background: #2f9e68; }
.app-bonus {
    margin-left: 4px;
    align-self: center;
    font-weight: 700;
}
.app-rank-table {
    width: 100%;
    margin-top: 18px;
    border-collapse: collapse;
}
.app-rank-table th,
.app-rank-table td {
    padding: 10px 4px;
    border-bottom: 1px solid #e5e7eb;
    text-align: right;
}
.app-rank-table th:first-child,
.app-rank-table td:first-child {
    text-align: left;
}
.app-empty {
    text-align: center;
    color: #6b7280;
}
</style>
</head>
<body>
<main class="app-shell">

    <a class="app-back" href="<?=htmlspecialchars(G5_URL . '/app/', ENT_QUOTES, 'UTF-8')?>">
        ← 회원앱 홈
    </a>

    <h1>로또 당첨결과</h1>

    <?php if (!empty($drawOptions)) { ?>
    <form method="get">
        <select
            class="app-select"
            name="turn"
            onchange="this.form.submit()"
            aria-label="조회 회차"
        >
            <?php foreach ($drawOptions as $option) { ?>
            <option
                value="<?=$option['draw_no']?>"
                <?=$selectedDraw === $option['draw_no'] ? 'selected' : ''?>
            >
                <?=$option['draw_no']?>회
                <?=$option['draw_date'] !== ''
                    ? ' (' . htmlspecialchars($option['draw_date'], ENT_QUOTES, 'UTF-8') . ')'
                    : ''?>
            </option>
            <?php } ?>
        </select>
    </form>
    <?php } ?>

    <section class="app-card">

        <?php if (empty($selectedResult['draw_no'])) { ?>

        <div class="app-empty">
            저장된 당첨결과가 없습니다.
        </div>

        <?php } else { ?>

        <h2>
            제<?=number_format((int) $selectedResult['draw_no'])?>회
        </h2>

        <p>
            추첨일:
            <?=htmlspecialchars(
                (string) $selectedResult['draw_date'],
                ENT_QUOTES,
                'UTF-8'
            )?>
        </p>

        <div class="app-balls">
            <?php for ($i = 1; $i <= 6; $i++) {
                $number = (int) $selectedResult['num_' . $i];
            ?>
            <span class="app-ball <?=lottoAppBallClass($number)?>">
                <?=$number?>
            </span>
            <?php } ?>

            <span class="app-bonus">+</span>

            <?php $bonus = (int) $selectedResult['bonus_num']; ?>
            <span class="app-ball <?=lottoAppBallClass($bonus)?>">
                <?=$bonus?>
            </span>
        </div>

        <table class="app-rank-table">
            <thead>
                <tr>
                    <th>등수</th>
                    <th>당첨자</th>
                    <th>1인당 당첨금</th>
                </tr>
            </thead>
            <tbody>
                <?php for ($rank = 1; $rank <= 5; $rank++) { ?>
                <tr>
                    <td><?=$rank?>등</td>
                    <td>
                        <?=number_format(
                            (int) $selectedResult['rank' . $rank . '_winners']
                        )?>명
                    </td>
                    <td>
                        <?=number_format(
                            (int) $selectedResult['rank' . $rank . '_amount']
                        )?>원
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php } ?>

    </section>

</main>
</body>
</html>
