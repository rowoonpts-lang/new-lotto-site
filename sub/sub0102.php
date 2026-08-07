<?php
include_once("./_common.php");

$g5['title'] = 'LottoGPT 소개';

// LottoGPT 전용 전체 폭 레이아웃
$lottogpt_full_width_page = true;

add_stylesheet(
    '<link rel="stylesheet" href="' . G5_THEME_URL . '/css/lottogpt.css">',
    0
);

include_once(G5_PATH . "/_head.php");
?>

<script>
document.body.classList.add('lottogpt-page');
</script>

<main class="lg-about-page">

    <section class="lg-about-hero">
        <div class="lg-shell">
            <p class="lg-eyebrow">ABOUT LOTTOGPT</p>

            <h1>
                로또 데이터를<br>
                <strong>더 쉽고 명확하게</strong>
            </h1>

            <p class="lg-about-hero-copy">
                LottoGPT는 로또 회차 데이터를 체계적으로 정리하고 분석하여
                누구나 과거 당첨 결과와 번호 흐름을 쉽고 편리하게 확인할 수 있도록
                돕는 데이터 기반 정보 서비스입니다.
            </p>
        </div>
    </section>

    <section class="lg-about-section">
        <div class="lg-shell">

            <div class="lg-about-heading">
                <p class="lg-eyebrow">WHAT WE DO</p>
                <h2>데이터에서 정보를 찾습니다.</h2>
                <p>
                    단순히 번호를 보여주는 것을 넘어,
                    축적된 로또 데이터를 보기 쉽고 이해하기 쉬운 형태로 제공합니다.
                </p>
            </div>

            <div class="lg-about-values">

                <article class="lg-about-card">
                    <span class="lg-about-card-number">01</span>
                    <h3>데이터 기반</h3>
                    <p>
                        저장된 로또 회차 데이터를 기준으로
                        당첨번호와 각종 통계 정보를 제공합니다.
                    </p>
                </article>

                <article class="lg-about-card">
                    <span class="lg-about-card-number">02</span>
                    <h3>명확한 정보</h3>
                    <p>
                        복잡한 데이터를 누구나 쉽게 확인할 수 있도록
                        간결하고 직관적인 화면으로 정리합니다.
                    </p>
                </article>

                <article class="lg-about-card">
                    <span class="lg-about-card-number">03</span>
                    <h3>지속적인 분석</h3>
                    <p>
                        새로운 회차 데이터를 계속 축적하면서
                        다양한 통계와 분석 기능을 단계적으로 발전시킵니다.
                    </p>
                </article>

            </div>

        </div>
    </section>

    <section class="lg-about-message">
        <div class="lg-shell">
            <div class="lg-about-message-panel">
                <div>
                    <p class="lg-eyebrow">OUR PRINCIPLE</p>
                    <h2>확률을 약속하지 않고,<br>데이터를 정확하게 보여줍니다.</h2>
                </div>

                <p>
                    로또는 무작위 추첨 방식의 게임이며 과거 데이터가 미래의 당첨을
                    보장하지 않습니다. LottoGPT는 특정 번호의 당첨을 보장하거나
                    확정적인 결과를 제시하지 않으며, 이용자가 데이터를 확인하고
                    참고할 수 있는 정보와 분석 도구를 제공하는 것을 목표로 합니다.
                </p>
            </div>
        </div>
    </section>

</main>

<?php
include_once(G5_PATH . "/_tail.php");
?>
