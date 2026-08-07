<?php
include_once("./_common.php");
include_once(G5_PATH . "/include/lotto_result.lib.php");

$g5['title'] = '로또 데이터 통계';

// LottoGPT 전용 전체 폭 레이아웃
$lottogpt_full_width_page = true;

add_stylesheet(
    '<link rel="stylesheet" href="' . G5_THEME_URL . '/css/lottogpt.css">',
    0
);

$table = lotto_result_table_name();

$summary = sql_fetch(
    "SELECT
        COUNT(*) AS total_draws,
        MIN(draw_no) AS first_draw,
        MAX(draw_no) AS latest_draw
     FROM `{$table}`",
    false
);

$total_draws = isset($summary['total_draws'])
    ? (int) $summary['total_draws']
    : 0;

$first_draw = isset($summary['first_draw'])
    ? (int) $summary['first_draw']
    : 0;

$latest_draw = isset($summary['latest_draw'])
    ? (int) $summary['latest_draw']
    : 0;

/*
 * 전체 회차 번호별 출현 횟수와 마지막 출현 회차
 */
$frequency_sql = "
    SELECT
        lotto_number,
        COUNT(*) AS appearance_count,
        MAX(draw_no) AS last_draw
    FROM (
        SELECT draw_no, num_1 AS lotto_number FROM `{$table}`
        UNION ALL
        SELECT draw_no, num_2 FROM `{$table}`
        UNION ALL
        SELECT draw_no, num_3 FROM `{$table}`
        UNION ALL
        SELECT draw_no, num_4 FROM `{$table}`
        UNION ALL
        SELECT draw_no, num_5 FROM `{$table}`
        UNION ALL
        SELECT draw_no, num_6 FROM `{$table}`
    ) AS number_history
    GROUP BY lotto_number
    ORDER BY lotto_number ASC
";

$frequency_result = sql_query($frequency_sql, false);

$number_stats = array();

for ($number = 1; $number <= 45; $number++) {
    $number_stats[$number] = array(
        'count' => 0,
        'last_draw' => 0,
        'recent_count' => 0,
    );
}

if ($frequency_result) {
    while ($row = sql_fetch_array($frequency_result)) {
        $number = (int) $row['lotto_number'];

        if ($number >= 1 && $number <= 45) {
            $number_stats[$number]['count'] =
                (int) $row['appearance_count'];

            $number_stats[$number]['last_draw'] =
                (int) $row['last_draw'];
        }
    }
}

/*
 * 최근 10회 번호별 출현 횟수
 */
$recent_frequency_sql = "
    SELECT
        lotto_number,
        COUNT(*) AS appearance_count
    FROM (
        SELECT num_1 AS lotto_number FROM (
            SELECT num_1
            FROM `{$table}`
            ORDER BY draw_no DESC
            LIMIT 10
        ) AS recent_1

        UNION ALL

        SELECT num_2 FROM (
            SELECT num_2
            FROM `{$table}`
            ORDER BY draw_no DESC
            LIMIT 10
        ) AS recent_2

        UNION ALL

        SELECT num_3 FROM (
            SELECT num_3
            FROM `{$table}`
            ORDER BY draw_no DESC
            LIMIT 10
        ) AS recent_3

        UNION ALL

        SELECT num_4 FROM (
            SELECT num_4
            FROM `{$table}`
            ORDER BY draw_no DESC
            LIMIT 10
        ) AS recent_4

        UNION ALL

        SELECT num_5 FROM (
            SELECT num_5
            FROM `{$table}`
            ORDER BY draw_no DESC
            LIMIT 10
        ) AS recent_5

        UNION ALL

        SELECT num_6 FROM (
            SELECT num_6
            FROM `{$table}`
            ORDER BY draw_no DESC
            LIMIT 10
        ) AS recent_6
    ) AS recent_numbers
    GROUP BY lotto_number
    ORDER BY lotto_number ASC
";

$recent_result = sql_query($recent_frequency_sql, false);

if ($recent_result) {
    while ($row = sql_fetch_array($recent_result)) {
        $number = (int) $row['lotto_number'];

        if ($number >= 1 && $number <= 45) {
            $number_stats[$number]['recent_count'] =
                (int) $row['appearance_count'];
        }
    }
}

/*
 * 전체 통계 계산
 */
$total_numbers = 0;
$odd_count = 0;
$even_count = 0;

$range_stats = array(
    '1~10' => 0,
    '11~20' => 0,
    '21~30' => 0,
    '31~40' => 0,
    '41~45' => 0,
);

foreach ($number_stats as $number => $stat) {
    $count = (int) $stat['count'];

    $total_numbers += $count;

    if ($number % 2 === 0) {
        $even_count += $count;
    } else {
        $odd_count += $count;
    }

    if ($number <= 10) {
        $range_stats['1~10'] += $count;
    } elseif ($number <= 20) {
        $range_stats['11~20'] += $count;
    } elseif ($number <= 30) {
        $range_stats['21~30'] += $count;
    } elseif ($number <= 40) {
        $range_stats['31~40'] += $count;
    } else {
        $range_stats['41~45'] += $count;
    }
}

$odd_percent = $total_numbers > 0
    ? ($odd_count / $total_numbers) * 100
    : 0;

$even_percent = $total_numbers > 0
    ? ($even_count / $total_numbers) * 100
    : 0;

/*
 * 많이 나온 번호 / 적게 나온 번호
 */
$ranking = $number_stats;

uasort($ranking, function ($a, $b) {
    if ($a['count'] === $b['count']) {
        return 0;
    }

    return ($a['count'] > $b['count']) ? -1 : 1;
});

$hot_numbers = array_slice($ranking, 0, 5, true);

uasort($ranking, function ($a, $b) {
    if ($a['count'] === $b['count']) {
        return 0;
    }

    return ($a['count'] < $b['count']) ? -1 : 1;
});

$cold_numbers = array_slice($ranking, 0, 5, true);

/*
 * 최근 10회에서 많이 나온 번호
 */
$recent_ranking = $number_stats;

uasort($recent_ranking, function ($a, $b) {
    if ($a['recent_count'] === $b['recent_count']) {
        return 0;
    }

    return ($a['recent_count'] > $b['recent_count']) ? -1 : 1;
});

$recent_hot_numbers = array_filter(
    array_slice($recent_ranking, 0, 10, true),
    function ($stat) {
        return (int) $stat['recent_count'] > 0;
    }
);

function lotto_stats_ball_class($number)
{
    $number = (int) $number;

    if ($number <= 10) {
        return 'lg-ball-yellow';
    }

    if ($number <= 20) {
        return 'lg-ball-blue';
    }

    if ($number <= 30) {
        return 'lg-ball-red';
    }

    if ($number <= 40) {
        return 'lg-ball-gray';
    }

    return 'lg-ball-green';
}

include_once(G5_PATH . "/_head.php");
?>

<main class="lg-stats-page">
    <section class="lg-stats-hero">
        <div class="lg-shell">
            <p class="lg-eyebrow">LOTTOGPT DATA LAB</p>
            <h1>로또 데이터 통계</h1>
            <p>
                저장된 실제 당첨번호를 기준으로 번호 출현 빈도와
                홀짝·구간 통계를 계산합니다.
            </p>
        </div>
    </section>

    <section class="lg-stats-section">
        <div class="lg-shell">

            <div class="lg-stats-summary">
                <article>
                    <span>분석 회차</span>
                    <strong>
                        <?=number_format($first_draw)?> ~
                        <?=number_format($latest_draw)?>회
                    </strong>
                </article>

                <article>
                    <span>전체 회차</span>
                    <strong><?=number_format($total_draws)?>회</strong>
                </article>

                <article>
                    <span>분석 번호 수</span>
                    <strong><?=number_format($total_numbers)?>개</strong>
                </article>

                <article>
                    <span>최신 회차</span>
                    <strong><?=number_format($latest_draw)?>회</strong>
                </article>
            </div>

            <section class="lg-stats-panel">
                <div class="lg-stats-panel-head">
                    <div>
                        <span>NUMBER FREQUENCY</span>
                        <h2>번호별 전체 출현 횟수</h2>
                    </div>

                    <p>
                        보너스번호를 제외한 6개 당첨번호 기준입니다.
                    </p>
                </div>

                <div class="lg-number-frequency-grid">
                    <?php foreach ($number_stats as $number => $stat) { ?>
                        <article class="lg-number-frequency-item">
                            <span class="lg-lotto-ball <?=lotto_stats_ball_class($number)?>">
                                <?=$number?>
                            </span>

                            <strong>
                                <?=number_format((int) $stat['count'])?>회
                            </strong>

                            <small>
                                최근 <?=number_format(
                                    $latest_draw - (int) $stat['last_draw']
                                )?>회 미출현
                            </small>
                        </article>
                    <?php } ?>
                </div>
            </section>

            <div class="lg-stats-two-column">
                <section class="lg-stats-panel">
                    <div class="lg-stats-panel-head">
                        <div>
                            <span>ALL TIME</span>
                            <h2>전체 출현 상위 번호</h2>
                        </div>
                    </div>

                    <div class="lg-stats-ranking">
                        <?php
                        $rank = 1;
                        foreach ($hot_numbers as $number => $stat) {
                        ?>
                            <div>
                                <b><?=$rank?></b>

                                <span class="lg-lotto-ball <?=lotto_stats_ball_class($number)?>">
                                    <?=$number?>
                                </span>

                                <strong>
                                    <?=number_format((int) $stat['count'])?>회
                                </strong>
                            </div>
                        <?php
                            $rank++;
                        }
                        ?>
                    </div>
                </section>

                <section class="lg-stats-panel">
                    <div class="lg-stats-panel-head">
                        <div>
                            <span>ALL TIME</span>
                            <h2>전체 출현 하위 번호</h2>
                        </div>
                    </div>

                    <div class="lg-stats-ranking">
                        <?php
                        $rank = 1;
                        foreach ($cold_numbers as $number => $stat) {
                        ?>
                            <div>
                                <b><?=$rank?></b>

                                <span class="lg-lotto-ball <?=lotto_stats_ball_class($number)?>">
                                    <?=$number?>
                                </span>

                                <strong>
                                    <?=number_format((int) $stat['count'])?>회
                                </strong>
                            </div>
                        <?php
                            $rank++;
                        }
                        ?>
                    </div>
                </section>
            </div>

            <section class="lg-stats-panel">
                <div class="lg-stats-panel-head">
                    <div>
                        <span>RECENT 10 DRAWS</span>
                        <h2>최근 10회 출현 번호</h2>
                    </div>

                    <p>
                        최근 10개 회차의 당첨번호 60개를 기준으로 계산합니다.
                    </p>
                </div>

                <div class="lg-recent-number-grid">
                    <?php foreach ($recent_hot_numbers as $number => $stat) { ?>
                        <article>
                            <span class="lg-lotto-ball <?=lotto_stats_ball_class($number)?>">
                                <?=$number?>
                            </span>

                            <strong>
                                <?=number_format(
                                    (int) $stat['recent_count']
                                )?>회
                            </strong>
                        </article>
                    <?php } ?>
                </div>
            </section>

            <div class="lg-stats-two-column">
                <section class="lg-stats-panel">
                    <div class="lg-stats-panel-head">
                        <div>
                            <span>ODD / EVEN</span>
                            <h2>홀수 · 짝수 출현 비율</h2>
                        </div>
                    </div>

                    <div class="lg-ratio-row">
                        <div>
                            <span>홀수</span>
                            <strong><?=number_format($odd_count)?>개</strong>
                            <b><?=number_format($odd_percent, 1)?>%</b>
                        </div>

                        <div>
                            <span>짝수</span>
                            <strong><?=number_format($even_count)?>개</strong>
                            <b><?=number_format($even_percent, 1)?>%</b>
                        </div>
                    </div>

                    <div class="lg-ratio-bar">
                        <span
                            style="width:<?=number_format(
                                $odd_percent,
                                2,
                                '.',
                                ''
                            )?>%"
                        ></span>
                    </div>
                </section>

                <section class="lg-stats-panel">
                    <div class="lg-stats-panel-head">
                        <div>
                            <span>NUMBER RANGE</span>
                            <h2>번호 구간별 출현 횟수</h2>
                        </div>
                    </div>

                    <div class="lg-range-list">
                        <?php foreach ($range_stats as $range => $count) { ?>
                            <?php
                            $range_percent = $total_numbers > 0
                                ? ($count / $total_numbers) * 100
                                : 0;
                            ?>

                            <div>
                                <span><?=$range?></span>

                                <div>
                                    <i
                                        style="width:<?=number_format(
                                            $range_percent,
                                            2,
                                            '.',
                                            ''
                                        )?>%"
                                    ></i>
                                </div>

                                <strong>
                                    <?=number_format($count)?>개
                                </strong>
                            </div>
                        <?php } ?>
                    </div>
                </section>
            </div>

            <div class="lg-stats-notice">
                <i class="fas fa-info-circle"></i>
                <p>
                    이 통계는 저장된 과거 당첨번호를 집계한 결과입니다.
                    과거 출현 빈도가 다음 회차의 당첨 가능성을 보장하지 않습니다.
                </p>
            </div>

        </div>
    </section>
</main>

<?php include_once(G5_PATH . "/_tail.php"); ?>
