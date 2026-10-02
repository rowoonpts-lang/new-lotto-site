<?php

include_once __DIR__ . '/_common.php';
include_once G5_PATH . '/include/lotto_app_access.lib.php';
include_once G5_PATH . '/include/lotto_result.lib.php';

lottoAppRequirePaidMember();

$table = lotto_result_table_name();

$summary = sql_fetch(
    "select
        count(*) as total_draws,
        min(draw_no) as first_draw,
        max(draw_no) as latest_draw
     from `{$table}`",
    false
);

$totalDraws = isset($summary['total_draws'])
    ? (int) $summary['total_draws']
    : 0;

$firstDraw = isset($summary['first_draw'])
    ? (int) $summary['first_draw']
    : 0;

$latestDraw = isset($summary['latest_draw'])
    ? (int) $summary['latest_draw']
    : 0;

$numberStats = array();

for ($number = 1; $number <= 45; $number++) {
    $numberStats[$number] = array(
        'count' => 0,
        'last_draw' => 0,
        'recent_count' => 0,
    );
}

$frequencySql = "
    select
        lotto_number,
        count(*) as appearance_count,
        max(draw_no) as last_draw
    from (
        select draw_no, num_1 as lotto_number from `{$table}`
        union all
        select draw_no, num_2 from `{$table}`
        union all
        select draw_no, num_3 from `{$table}`
        union all
        select draw_no, num_4 from `{$table}`
        union all
        select draw_no, num_5 from `{$table}`
        union all
        select draw_no, num_6 from `{$table}`
    ) as number_history
    group by lotto_number
    order by lotto_number asc
";

$frequencyResult = sql_query($frequencySql, false);

if ($frequencyResult) {
    while ($row = sql_fetch_array($frequencyResult)) {
        $number = (int) $row['lotto_number'];

        if ($number < 1 || $number > 45) {
            continue;
        }

        $numberStats[$number]['count'] =
            (int) $row['appearance_count'];

        $numberStats[$number]['last_draw'] =
            (int) $row['last_draw'];
    }
}

$recentFrequencySql = "
    select
        lotto_number,
        count(*) as appearance_count
    from (
        select num_1 as lotto_number from (
            select num_1
            from `{$table}`
            order by draw_no desc
            limit 10
        ) as recent_1

        union all

        select num_2 from (
            select num_2
            from `{$table}`
            order by draw_no desc
            limit 10
        ) as recent_2

        union all

        select num_3 from (
            select num_3
            from `{$table}`
            order by draw_no desc
            limit 10
        ) as recent_3

        union all

        select num_4 from (
            select num_4
            from `{$table}`
            order by draw_no desc
            limit 10
        ) as recent_4

        union all

        select num_5 from (
            select num_5
            from `{$table}`
            order by draw_no desc
            limit 10
        ) as recent_5

        union all

        select num_6 from (
            select num_6
            from `{$table}`
            order by draw_no desc
            limit 10
        ) as recent_6
    ) as recent_numbers
    group by lotto_number
    order by lotto_number asc
";

$recentResult = sql_query($recentFrequencySql, false);

if ($recentResult) {
    while ($row = sql_fetch_array($recentResult)) {
        $number = (int) $row['lotto_number'];

        if ($number >= 1 && $number <= 45) {
            $numberStats[$number]['recent_count'] =
                (int) $row['appearance_count'];
        }
    }
}

$totalNumbers = 0;
$oddCount = 0;
$evenCount = 0;

$rangeStats = array(
    '1~10' => 0,
    '11~20' => 0,
    '21~30' => 0,
    '31~40' => 0,
    '41~45' => 0,
);

foreach ($numberStats as $number => $stat) {
    $count = (int) $stat['count'];

    $totalNumbers += $count;

    if ($number % 2 === 0) {
        $evenCount += $count;
    } else {
        $oddCount += $count;
    }

    if ($number <= 10) {
        $rangeStats['1~10'] += $count;
    } elseif ($number <= 20) {
        $rangeStats['11~20'] += $count;
    } elseif ($number <= 30) {
        $rangeStats['21~30'] += $count;
    } elseif ($number <= 40) {
        $rangeStats['31~40'] += $count;
    } else {
        $rangeStats['41~45'] += $count;
    }
}

$oddPercent = $totalNumbers > 0
    ? ($oddCount / $totalNumbers) * 100
    : 0;

$evenPercent = $totalNumbers > 0
    ? ($evenCount / $totalNumbers) * 100
    : 0;

$hotRanking = $numberStats;

uasort($hotRanking, function ($a, $b) {
    return $b['count'] <=> $a['count'];
});

$hotNumbers = array_slice($hotRanking, 0, 5, true);

$coldRanking = $numberStats;

uasort($coldRanking, function ($a, $b) {
    return $a['count'] <=> $b['count'];
});

$coldNumbers = array_slice($coldRanking, 0, 5, true);

$recentRanking = $numberStats;

uasort($recentRanking, function ($a, $b) {
    return $b['recent_count'] <=> $a['recent_count'];
});

$recentHotNumbers = array_filter(
    array_slice($recentRanking, 0, 10, true),
    function ($stat) {
        return (int) $stat['recent_count'] > 0;
    }
);

function lottoAppStatsBallClass($number)
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
<title>로또 통계 - LottoGPT</title>
<style>
* {
    box-sizing: border-box;
}

body {
    margin: 0;
    background: #f4f6f9;
    color: #1f2937;
    font-family: Arial, "Apple SD Gothic Neo", sans-serif;
}

.app-header {
    position: sticky;
    top: 0;
    z-index: 100;
    background: rgba(255, 255, 255, .97);
    border-bottom: 1px solid #e4e7eb;
}

.app-header-inner {
    max-width: 640px;
    margin: 0 auto;
}

.app-brand {
    padding: 14px 16px 10px;
    font-size: 21px;
    font-weight: 800;
}

.app-nav {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    border-top: 1px solid #f0f1f3;
}

.app-nav a {
    display: flex;
    min-height: 46px;
    align-items: center;
    justify-content: center;
    padding: 8px 4px;
    color: #60666d;
    font-size: 13px;
    font-weight: 700;
    text-align: center;
    text-decoration: none;
}

.app-nav a.active {
    color: #111827;
    border-bottom: 3px solid #111827;
}

.app-shell {
    max-width: 640px;
    margin: 0 auto;
    padding: 18px 14px 48px;
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
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}
.summary-grid div {
    padding: 14px;
    background: #f9fafb;
    border-radius: 12px;
}
.summary-grid span {
    display: block;
    font-size: 13px;
    color: #6b7280;
}
.summary-grid strong {
    display: block;
    margin-top: 6px;
}
.ball-row {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}
.ball-item {
    text-align: center;
}
.ball {
    width: 40px;
    height: 40px;
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
.ball-item small {
    display: block;
    margin-top: 4px;
    color: #6b7280;
}
.stat-list {
    margin: 0;
    padding: 0;
    list-style: none;
}
.stat-list li {
    display: flex;
    justify-content: space-between;
    padding: 9px 0;
    border-bottom: 1px solid #e5e7eb;
}
.notice {
    margin-top: 18px;
    font-size: 13px;
    color: #6b7280;
    line-height: 1.6;
}
</style>
</head>
<body>

<header class="app-header">
    <div class="app-header-inner">
        <div class="app-brand">LottoGPT</div>

        <nav class="app-nav" aria-label="회원앱 메뉴">
            <a href="/app/">
                추천번호
            </a>

            <a href="/app/results.php">
                당첨결과
            </a>

            <a class="active" href="/app/stats.php">
                통계
            </a>

            <a href="/app/my_lotto.php">
                내 당첨
            </a>
        </nav>
    </div>
</header>

<main class="app-shell">

    <h1>로또 데이터 통계</h1>

    <section class="app-card">
        <div class="summary-grid">
            <div>
                <span>분석 회차</span>
                <strong>
                    <?=number_format($firstDraw)?> ~
                    <?=number_format($latestDraw)?>회
                </strong>
            </div>

            <div>
                <span>전체 회차</span>
                <strong><?=number_format($totalDraws)?>회</strong>
            </div>

            <div>
                <span>분석 번호 수</span>
                <strong><?=number_format($totalNumbers)?>개</strong>
            </div>

            <div>
                <span>최신 회차</span>
                <strong><?=number_format($latestDraw)?>회</strong>
            </div>
        </div>
    </section>

    <section class="app-card">
        <h2>전체 출현 상위 번호</h2>

        <div class="ball-row">
            <?php foreach ($hotNumbers as $number => $stat) { ?>
            <div class="ball-item">
                <span class="ball <?=lottoAppStatsBallClass($number)?>">
                    <?=$number?>
                </span>
                <small><?=number_format((int) $stat['count'])?>회</small>
            </div>
            <?php } ?>
        </div>
    </section>

    <section class="app-card">
        <h2>전체 출현 하위 번호</h2>

        <div class="ball-row">
            <?php foreach ($coldNumbers as $number => $stat) { ?>
            <div class="ball-item">
                <span class="ball <?=lottoAppStatsBallClass($number)?>">
                    <?=$number?>
                </span>
                <small><?=number_format((int) $stat['count'])?>회</small>
            </div>
            <?php } ?>
        </div>
    </section>

    <section class="app-card">
        <h2>최근 10회 많이 나온 번호</h2>

        <div class="ball-row">
            <?php foreach ($recentHotNumbers as $number => $stat) { ?>
            <div class="ball-item">
                <span class="ball <?=lottoAppStatsBallClass($number)?>">
                    <?=$number?>
                </span>
                <small><?=number_format((int) $stat['recent_count'])?>회</small>
            </div>
            <?php } ?>
        </div>
    </section>

    <section class="app-card">
        <h2>홀수 · 짝수</h2>

        <ul class="stat-list">
            <li>
                <span>홀수</span>
                <strong>
                    <?=number_format($oddCount)?>개 /
                    <?=number_format($oddPercent, 1)?>%
                </strong>
            </li>

            <li>
                <span>짝수</span>
                <strong>
                    <?=number_format($evenCount)?>개 /
                    <?=number_format($evenPercent, 1)?>%
                </strong>
            </li>
        </ul>
    </section>

    <section class="app-card">
        <h2>번호 구간별 출현 횟수</h2>

        <ul class="stat-list">
            <?php foreach ($rangeStats as $range => $count) { ?>
            <li>
                <span><?=$range?></span>
                <strong><?=number_format($count)?>개</strong>
            </li>
            <?php } ?>
        </ul>
    </section>

    <p class="notice">
        이 통계는 저장된 과거 당첨번호를 집계한 결과입니다.
        과거 출현 빈도가 다음 회차의 당첨 가능성을 보장하지 않습니다.
    </p>

</main>
</body>
</html>
