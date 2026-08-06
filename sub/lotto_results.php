<?php
include_once("./_common.php");
include_once(G5_PATH . "/include/lotto_result.lib.php");

$page_title = '로또 당첨결과';
$g5['title'] = $page_title;

// 이 페이지는 LottoGPT 전용 레이아웃을 사용합니다.
$lottogpt_full_width_page = true;

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$page = max(1, $page);

$rows_per_page = 10;
$table = lotto_result_table_name();

$count_row = sql_fetch(
    "SELECT COUNT(*) AS cnt FROM `{$table}`",
    false
);

$total_rows = isset($count_row['cnt'])
    ? (int) $count_row['cnt']
    : 0;

$total_pages = max(1, (int) ceil($total_rows / $rows_per_page));

if ($page > $total_pages) {
    $page = $total_pages;
}

$offset = ($page - 1) * $rows_per_page;

$result = sql_query(
    "SELECT *
       FROM `{$table}`
      ORDER BY draw_no DESC
      LIMIT {$offset}, {$rows_per_page}",
    false
);

$lotto_rows = array();

if ($result) {
    while ($row = sql_fetch_array($result)) {
        $lotto_rows[] = $row;
    }
}

function lotto_result_list_ball_class($number)
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

$pagination_start = max(1, $page - 2);
$pagination_end = min($total_pages, $page + 2);

add_stylesheet(
    '<link rel="stylesheet" href="' . G5_THEME_URL . '/css/lottogpt.css">',
    0
);

include_once(G5_PATH . "/_head.php");
?>

<main class="lg-results-page">
    <section class="lg-results-hero">
        <div class="lg-shell">
            <p class="lg-eyebrow">LOTTO 6/45 RESULT DATA</p>
            <h1>로또 당첨결과</h1>
            <p>
                1회부터 최신 회차까지 저장된 공식 당첨번호와
                당첨 정보를 확인할 수 있습니다.
            </p>
        </div>
    </section>

    <section class="lg-results-section">
        <div class="lg-shell">
            <div class="lg-results-summary">
                <div>
                    <span>저장된 전체 회차</span>
                    <strong><?=number_format($total_rows)?>회</strong>
                </div>

                <div>
                    <span>현재 페이지</span>
                    <strong><?=number_format($page)?> / <?=number_format($total_pages)?></strong>
                </div>
            </div>

            <div class="lg-results-list">
                <?php if ($lotto_rows) { ?>
                    <?php foreach ($lotto_rows as $row) { ?>
                        <?php
                        $draw_no = (int) $row['draw_no'];
                        $has_detail_prize =
                            (int) $row['rank2_winners'] > 0
                            || (int) $row['rank3_winners'] > 0
                            || (int) $row['rank4_winners'] > 0
                            || (int) $row['rank5_winners'] > 0;
                        ?>

                        <article class="lg-result-item">
                            <header class="lg-result-item-head">
                                <div>
                                    <strong><?=number_format($draw_no)?>회</strong>
                                    <span><?=get_text($row['draw_date'])?> 추첨</span>
                                </div>

                                <a
                                    href="https://www.dhlottery.co.kr/lt645/result"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    공식 결과
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </header>

                            <div class="lg-result-item-body">
                                <div class="lg-result-numbers">
                                    <div class="lg-result-balls">
                                        <?php for ($number_index = 1; $number_index <= 6; $number_index++) { ?>
                                            <?php $number = (int) $row['num_' . $number_index]; ?>

                                            <span class="lg-lotto-ball <?=lotto_result_list_ball_class($number)?>">
                                                <?=$number?>
                                            </span>
                                        <?php } ?>

                                        <b class="lg-bonus-plus">+</b>

                                        <?php $bonus_number = (int) $row['bonus_num']; ?>

                                        <span class="lg-lotto-ball <?=lotto_result_list_ball_class($bonus_number)?>">
                                            <?=$bonus_number?>
                                        </span>
                                    </div>

                                    <small>마지막 공은 보너스번호입니다.</small>
                                </div>

                                <div class="lg-result-prize">
                                    <span>1등 당첨</span>
                                    <strong>
                                        <?=number_format((int) $row['rank1_winners'])?>게임
                                    </strong>
                                    <b>
                                        <?=number_format((int) $row['rank1_amount'])?>원
                                    </b>

                                    <?php if (!$has_detail_prize) { ?>
                                        <small>2등~5등 상세 자료 없음</small>
                                    <?php } ?>
                                </div>
                            </div>
                        </article>
                    <?php } ?>
                <?php } else { ?>
                    <div class="lg-results-empty">
                        저장된 당첨결과가 없습니다.
                    </div>
                <?php } ?>
            </div>

            <?php if ($total_pages > 1) { ?>
                <nav class="lg-results-pagination" aria-label="당첨결과 페이지">
                    <?php if ($page > 1) { ?>
                        <a href="?page=1" aria-label="첫 페이지">
                            <i class="fas fa-angle-double-left"></i>
                        </a>

                        <a href="?page=<?=$page - 1?>" aria-label="이전 페이지">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    <?php } ?>

                    <?php for ($page_number = $pagination_start; $page_number <= $pagination_end; $page_number++) { ?>
                        <a
                            href="?page=<?=$page_number?>"
                            class="<?=$page_number === $page ? 'is-active' : ''?>"
                        >
                            <?=$page_number?>
                        </a>
                    <?php } ?>

                    <?php if ($page < $total_pages) { ?>
                        <a href="?page=<?=$page + 1?>" aria-label="다음 페이지">
                            <i class="fas fa-angle-right"></i>
                        </a>

                        <a href="?page=<?=$total_pages?>" aria-label="마지막 페이지">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    <?php } ?>
                </nav>
            <?php } ?>
        </div>
    </section>
</main>

<?php include_once(G5_PATH . "/_tail.php"); ?>
