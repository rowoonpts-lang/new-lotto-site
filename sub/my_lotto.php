<?php
include_once("_common.php");

if (!$is_member) {
    alert(
        "로그인 후 이용바랍니다.",
        "/bbs/login.php?url=/sub/my_lotto.php"
    );
}

$g5['title'] = 'LottoGPT 마이페이지';
$lottogpt_full_width_page = true;

add_stylesheet(
    '<link rel="stylesheet" href="' . G5_THEME_URL . '/css/lottogpt.css">',
    0
);

include_once(G5_PATH . "/_head.php");

$member_id = isset($member['mb_id'])
    ? $member['mb_id']
    : '';

$member_etc = sql_fetch(
    "select *
     from g5_member_etc
     where mb_id = '" . sql_real_escape_string($member_id) . "'
     limit 1",
    false
);

if (!is_array($member_etc)) {
    $member_etc = array();
}

$latest_result = sql_fetch(
    "select *
     from g5_lotto_result
     order by draw_no desc
     limit 1",
    false
);

$latest_draw = !empty($latest_result['draw_no'])
    ? (int) $latest_result['draw_no']
    : 0;

$selected_draw = isset($_GET['turn'])
    ? (int) $_GET['turn']
    : $latest_draw;

if ($selected_draw < 1) {
    $selected_draw = $latest_draw;
}

$draw_results = array();
$result_query = sql_query(
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
     from g5_lotto_result
     order by draw_no desc",
    false
);

while ($row = sql_fetch_array($result_query)) {
    $draw_results[(int) $row['draw_no']] = $row;
}

function lottogpt_my_rank($numbers, $result)
{
    if (
        !is_array($numbers)
        || count($numbers) !== 6
    ) {
        return '';
    }

    if (
        !is_array($result)
        || empty($result['draw_no'])
    ) {
        return '추첨대기';
    }

    $winning_numbers = array(
        (int) $result['num_1'],
        (int) $result['num_2'],
        (int) $result['num_3'],
        (int) $result['num_4'],
        (int) $result['num_5'],
        (int) $result['num_6'],
    );

    $numbers = array_map('intval', $numbers);

    $match_count = count(
        array_intersect($numbers, $winning_numbers)
    );

    $bonus_match = in_array(
        (int) $result['bonus_num'],
        $numbers,
        true
    );

    if ($match_count === 6) {
        return '1등';
    }

    if ($match_count === 5 && $bonus_match) {
        return '2등';
    }

    if ($match_count === 5) {
        return '3등';
    }

    if ($match_count === 4) {
        return '4등';
    }

    if ($match_count === 3) {
        return '5등';
    }

    return '미당첨';
}

function lottogpt_my_ball_class($number)
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

$safe_member_id = sql_real_escape_string($member_id);

$member_draws = array();

$customer_rows = array();
$winning_rows = array();

$rank_counts = array(
    '1등' => 0,
    '2등' => 0,
    '3등' => 0,
    '4등' => 0,
    '5등' => 0,
);

$total_games = 0;
$total_draws = 0;

$combination_query = sql_query(
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
        result_rank,
        created_at
     from l_member_combination
     where mb_id = '{$safe_member_id}'
     order by draw_no desc, created_at desc, lmc_id desc",
    false
);

while ($row = sql_fetch_array($combination_query)) {
    $draw_no = isset($row['draw_no'])
        ? (int) $row['draw_no']
        : 0;

    if ($draw_no < 1) {
        continue;
    }

    $member_draws[$draw_no] = true;

    $draw_result = isset($draw_results[$draw_no])
        ? $draw_results[$draw_no]
        : array();

    $numbers = array(
        (int) $row['num1'],
        (int) $row['num2'],
        (int) $row['num3'],
        (int) $row['num4'],
        (int) $row['num5'],
        (int) $row['num6'],
    );

    $rank = lottogpt_my_rank(
        $numbers,
        $draw_result
    );

    $item = array(
        'draw_no' => $draw_no,
        'draw_date' => isset($draw_result['draw_date'])
            ? $draw_result['draw_date']
            : '',
        'numbers' => $numbers,
        'rank' => $rank,
        'mb_type' => isset($row['member_type'])
            ? $row['member_type']
            : '',
        'issued_at' => isset($row['created_at'])
            ? $row['created_at']
            : '',
    );

    $customer_rows[] = $item;

    if (isset($rank_counts[$rank])) {
        $rank_counts[$rank]++;
        $winning_rows[] = $item;
    }

    $total_games++;
}

$total_draws = count($member_draws);

$turn_options = array_unique(
    array_merge(
        array_keys($member_draws),
        array_keys($draw_results)
    )
);

rsort($turn_options, SORT_NUMERIC);

usort(
    $customer_rows,
    function ($a, $b) {
        if ($a['draw_no'] === $b['draw_no']) {
            return strcmp(
                $b['issued_at'],
                $a['issued_at']
            );
        }

        return $b['draw_no'] <=> $a['draw_no'];
    }
);

usort(
    $winning_rows,
    function ($a, $b) {
        return $b['draw_no'] <=> $a['draw_no'];
    }
);

if (
    !isset($_GET['turn'])
    && !empty($customer_rows)
) {
    $selected_draw = (int) $customer_rows[0]['draw_no'];
}

if (
    $selected_draw > 0
    && !in_array($selected_draw, $turn_options, true)
) {
    $selected_draw = $latest_draw;
}

$selected_rows = array();

foreach ($customer_rows as $item) {
    if ((int) $item['draw_no'] === $selected_draw) {
        $selected_rows[] = $item;
    }
}

$service_total = 0;

foreach (
    array(
        'num_mon',
        'num_tue',
        'num_wed',
        'num_thur',
        'num_fri',
        'num_sat',
    ) as $key
) {
    $service_total += isset($member_etc[$key])
        ? (int) $member_etc[$key]
        : 0;
}

$service_end = !empty($member_etc['end_date'])
    && $member_etc['end_date'] >= date('Y-m-d')
    ? $member_etc['end_date']
    : '없음';
?>

<script>
document.body.classList.add('lottogpt-page');
</script>

<main class="lg-my-page">

    <section class="lg-my-hero">
        <div class="lg-shell">

            <p class="lg-kicker">MY LOTTO DASHBOARD</p>

            <h1>
                내가 받은 조합과<br>
                <strong>당첨 결과를 확인하세요.</strong>
            </h1>

            <p class="lg-my-hero-copy">
                고객에게 제공된 조합과 공식 당첨 데이터를 비교해
                회차별 결과와 누적 당첨 현황을 보여드립니다.
            </p>

        </div>
    </section>

    <section class="lg-my-summary">
        <div class="lg-shell">

            <div class="lg-my-service-grid">

                <article>
                    <span>가입 상품</span>
                    <strong>
                        <?=htmlspecialchars(
                            isset($member['mb_type'])
                            && trim($member['mb_type']) !== ''
                                ? $member['mb_type']
                                : '미등록',
                            ENT_QUOTES,
                            'UTF-8'
                        )?>
                    </strong>
                </article>

                <article>
                    <span>주간 제공 조합</span>
                    <strong><?=number_format($service_total)?>게임</strong>
                </article>

                <article>
                    <span>서비스 종료일</span>
                    <strong><?=htmlspecialchars($service_end, ENT_QUOTES, 'UTF-8')?></strong>
                </article>

                <article>
                    <span>총 제공 회차</span>
                    <strong><?=number_format($total_draws)?>회</strong>
                </article>

                <article>
                    <span>총 제공 조합</span>
                    <strong><?=number_format($total_games)?>게임</strong>
                </article>

            </div>

            <div class="lg-my-rank-grid">

                <?php foreach ($rank_counts as $rank => $count) { ?>
                <article>
                    <span><?=$rank?></span>
                    <strong><?=number_format($count)?></strong>
                    <small>당첨 게임</small>
                </article>
                <?php } ?>

            </div>

        </div>
    </section>

    <section class="lg-my-section">
        <div class="lg-shell">

            <div class="lg-my-heading">
                <div>
                    <p class="lg-kicker">MY NUMBERS</p>
                    <h2>회차별 받은 조합</h2>
                </div>

                <?php if ($latest_draw > 0) { ?>
                <form method="get" action="">
                    <select
                        name="turn"
                        onchange="this.form.submit()"
                        aria-label="조회 회차"
                    >
                        <?php
                        foreach ($turn_options as $draw_no) {
                        ?>
                        <option
                            value="<?=$draw_no?>"
                            <?=$selected_draw === (int) $draw_no ? 'selected' : ''?>
                        >
                            <?=$draw_no?>회
                        </option>
                        <?php } ?>
                    </select>
                </form>
                <?php } ?>

            </div>

            <div class="lg-my-panel">

                <?php if (empty($selected_rows)) { ?>

                    <div class="lg-my-empty">
                        <strong>
                            <?=$selected_draw > 0
                                ? number_format($selected_draw) . '회'
                                : '현재'?> 제공 조합이 없습니다.
                        </strong>
                        <p>
                            이 계정에 저장된 조합 데이터가 있으면
                            이곳에서 번호와 당첨 결과를 확인할 수 있습니다.
                        </p>
                    </div>

                <?php } else { ?>

                    <div class="lg-my-table-wrap">
                        <table class="lg-my-table">
                            <thead>
                                <tr>
                                    <th>회차</th>
                                    <th>발급일</th>
                                    <th>조합번호</th>
                                    <th>결과</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php foreach ($selected_rows as $item) { ?>
                                <tr>
                                    <td><?=$item['draw_no']?>회</td>

                                    <td>
                                        <?=!empty($item['issued_at'])
                                            ? date(
                                                'Y-m-d',
                                                strtotime($item['issued_at'])
                                            )
                                            : '-'?>
                                    </td>

                                    <td>
                                        <div class="lg-my-balls">
                                            <?php foreach ($item['numbers'] as $number) { ?>
                                            <span class="lg-lotto-ball <?=lottogpt_my_ball_class($number)?>">
                                                <?=$number?>
                                            </span>
                                            <?php } ?>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="lg-my-rank lg-my-rank-<?=htmlspecialchars($item['rank'], ENT_QUOTES, 'UTF-8')?>">
                                            <?=$item['rank']?>
                                        </span>
                                    </td>
                                </tr>
                                <?php } ?>

                            </tbody>
                        </table>
                    </div>

                <?php } ?>

            </div>

        </div>
    </section>

    <section class="lg-my-section lg-my-winning-section">
        <div class="lg-shell">

            <div class="lg-my-heading">
                <div>
                    <p class="lg-kicker">WIN HISTORY</p>
                    <h2>당첨된 회차</h2>
                </div>
            </div>

            <div class="lg-my-panel">

                <?php if (empty($winning_rows)) { ?>

                    <div class="lg-my-empty">
                        <strong>확인된 당첨 내역이 없습니다.</strong>
                        <p>
                            당첨 결과가 확인되면
                            회차와 조합번호가 이곳에 표시됩니다.
                        </p>
                    </div>

                <?php } else { ?>

                    <div class="lg-my-table-wrap">
                        <table class="lg-my-table">
                            <thead>
                                <tr>
                                    <th>회차</th>
                                    <th>등수</th>
                                    <th>조합번호</th>
                                    <th>추첨일</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php foreach ($winning_rows as $item) { ?>
                                <tr>
                                    <td><?=$item['draw_no']?>회</td>

                                    <td>
                                        <span class="lg-my-rank">
                                            <?=$item['rank']?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="lg-my-balls">
                                            <?php foreach ($item['numbers'] as $number) { ?>
                                            <span class="lg-lotto-ball <?=lottogpt_my_ball_class($number)?>">
                                                <?=$number?>
                                            </span>
                                            <?php } ?>
                                        </div>
                                    </td>

                                    <td>
                                        <?=htmlspecialchars(
                                            $item['draw_date'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        )?>
                                    </td>
                                </tr>
                                <?php } ?>

                            </tbody>
                        </table>
                    </div>

                <?php } ?>

            </div>

            <div class="lg-my-note">
                <span>DATA BASIS</span>
                <p>
                    당첨 결과는 저장된 고객 조합 번호와
                    g5_lotto_result의 공식 회차 데이터를 비교하여 계산합니다.
                </p>
            </div>

        </div>
    </section>

</main>

<?php
include_once(G5_PATH . "/_tail.php");
?>
