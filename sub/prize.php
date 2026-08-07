<?php
include_once("./_common.php");

$g5['title'] = 'LottoGPT 회차 당첨 데이터';

// LottoGPT 전용 전체 폭 레이아웃
$lottogpt_full_width_page = true;

add_stylesheet(
    '<link rel="stylesheet" href="' . G5_THEME_URL . '/css/lottogpt.css">',
    0
);

include_once(G5_PATH . "/_head.php");

$range = sql_fetch(
    "select
        min(draw_no) as min_draw,
        max(draw_no) as max_draw
     from g5_lotto_result",
    false
);

$minDraw = isset($range['min_draw']) ? (int) $range['min_draw'] : 0;
$maxDraw = isset($range['max_draw']) ? (int) $range['max_draw'] : 0;

$turn = isset($_GET['turn'])
    ? (int) $_GET['turn']
    : $maxDraw;

if ($turn < $minDraw || $turn > $maxDraw) {
    $turn = $maxDraw;
}

$result = array();

if ($turn > 0) {
    $result = sql_fetch(
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
            rank1_amount
         from g5_lotto_result
         where draw_no = '{$turn}'
         limit 1",
        false
    );
}

$hasResult = is_array($result)
    && !empty($result['draw_no']);

$numbers = array();

if ($hasResult) {
    $numbers = array(
        (int) $result['num_1'],
        (int) $result['num_2'],
        (int) $result['num_3'],
        (int) $result['num_4'],
        (int) $result['num_5'],
        (int) $result['num_6'],
    );
}

function lottogpt_ball_class($number)
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
?>

<script>
document.body.classList.add('lottogpt-page');
</script>

<main class="lg-winning-page">

    <section class="lg-winning-hero">
        <div class="lg-shell">
            <p class="lg-eyebrow">LOTTOGPT WINNING DATA</p>

            <h1>
                회차별<br>
                <strong>당첨 데이터를 확인하세요.</strong>
            </h1>

            <p>
                LottoGPT에 저장된 로또 회차 데이터를 기준으로
                과거 당첨번호와 추첨 결과를 확인할 수 있습니다.
            </p>
        </div>
    </section>

    <section class="lg-winning-section">
        <div class="lg-shell">

            <div class="lg-winning-panel">

                <div class="lg-winning-panel-head">
                    <div>
                        <span class="lg-winning-label">DRAW RESULT</span>
                        <h2>회차 당첨번호</h2>
                    </div>

                    <span class="lg-winning-db-status">
                        <i></i>
                        DATABASE CONNECTED
                    </span>
                </div>

                <?php if ($hasResult) { ?>

                <div class="lg-winning-result">

                    <div class="lg-winning-result-head">
                        <div>
                            <strong>
                                <?=number_format((int) $result['draw_no'])?>회
                            </strong>

                            <span>당첨번호</span>

                            <small>
                                추첨일
                                <?=htmlspecialchars(
                                    date(
                                        'Y.m.d',
                                        strtotime($result['draw_date'])
                                    ),
                                    ENT_QUOTES,
                                    'UTF-8'
                                )?>
                            </small>
                        </div>

                        <form
                            method="get"
                            action="<?=G5_URL?>/sub/prize.php"
                            class="lg-winning-turn-form"
                        >
                            <label for="turn">회차 선택</label>

                            <select
                                id="turn"
                                name="turn"
                                onchange="this.form.submit()"
                            >
                                <?php
                                for (
                                    $draw = $maxDraw;
                                    $draw >= $minDraw;
                                    $draw--
                                ) {
                                ?>
                                <option
                                    value="<?=$draw?>"
                                    <?=$turn === $draw ? 'selected' : ''?>
                                >
                                    <?=$draw?>회
                                </option>
                                <?php } ?>
                            </select>
                        </form>
                    </div>

                    <div class="lg-winning-balls">

                        <?php foreach ($numbers as $number) { ?>
                        <span
                            class="lg-winning-ball <?=lottogpt_ball_class($number)?>"
                        >
                            <?=$number?>
                        </span>
                        <?php } ?>

                        <span class="lg-winning-plus">+</span>

                        <span
                            class="lg-winning-ball <?=lottogpt_ball_class((int) $result['bonus_num'])?>"
                        >
                            <?=(int) $result['bonus_num']?>
                        </span>

                    </div>

                    <div class="lg-winning-summary">

                        <div>
                            <span>1등 당첨자</span>
                            <strong>
                                <?=number_format((int) $result['rank1_winners'])?>명
                            </strong>
                        </div>

                        <div>
                            <span>1등 당첨금</span>
                            <strong>
                                <?=number_format((int) $result['rank1_amount'])?>원
                            </strong>
                        </div>

                    </div>

                </div>

                <?php } else { ?>

                <div class="lg-winning-empty">
                    등록된 당첨 데이터가 없습니다.
                </div>

                <?php } ?>

            </div>

            <div class="lg-winning-guide">

                <article>
                    <span>01</span>
                    <h3>실제 DB 데이터</h3>
                    <p>
                        g5_lotto_result에 저장된 회차별
                        당첨 결과를 기준으로 표시합니다.
                    </p>
                </article>

                <article>
                    <span>02</span>
                    <h3>전체 회차 조회</h3>
                    <p>
                        저장된 1회부터 최신 회차까지
                        원하는 결과를 선택해 확인할 수 있습니다.
                    </p>
                </article>

                <article>
                    <span>03</span>
                    <h3>데이터 안내</h3>
                    <p>
                        과거 당첨 결과와 통계 데이터는
                        미래의 당첨을 보장하지 않습니다.
                    </p>
                </article>

            </div>

        </div>
    </section>

</main>

<?php
include_once(G5_PATH . "/_tail.php");
?>
