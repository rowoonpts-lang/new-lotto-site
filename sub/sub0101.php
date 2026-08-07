<?php
include_once("./_common.php");

$g5['title'] = 'LottoGPT 분석 시스템';

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

<main class="lg-analysis-page">

    <section class="lg-analysis-hero">
        <div class="lg-shell">
            <p class="lg-eyebrow">LOTTOGPT ANALYSIS SYSTEM</p>

            <h1>
                로또 데이터를<br>
                <strong>분석 가능한 정보로.</strong>
            </h1>

            <p class="lg-analysis-hero-copy">
                LottoGPT는 축적된 로또 회차 데이터를 정리하고 분석하여
                번호 분포와 통계 정보를 사용자가 쉽게 확인할 수 있는 형태로 제공합니다.
            </p>
        </div>
    </section>

    <section class="lg-analysis-section">
        <div class="lg-shell">

            <div class="lg-analysis-heading">
                <p class="lg-eyebrow">ANALYSIS FLOW</p>
                <h2>데이터가 정보가 되는 과정</h2>
                <p>
                    원본 회차 데이터를 그대로 보여주는 것에 그치지 않고,
                    정리와 분석 과정을 거쳐 이해하기 쉬운 정보로 구성합니다.
                </p>
            </div>

            <div class="lg-analysis-flow">

                <article class="lg-analysis-step">
                    <span class="lg-analysis-step-number">01</span>
                    <span class="lg-analysis-step-code">DATA</span>
                    <h3>데이터 축적</h3>
                    <p>
                        회차별 당첨번호와 추첨 결과 데이터를
                        지속적으로 저장하고 관리합니다.
                    </p>
                </article>

                <article class="lg-analysis-step">
                    <span class="lg-analysis-step-number">02</span>
                    <span class="lg-analysis-step-code">CLEAN</span>
                    <h3>데이터 정리</h3>
                    <p>
                        저장된 번호와 회차 정보를 분석하기 쉬운
                        일정한 형태로 정리합니다.
                    </p>
                </article>

                <article class="lg-analysis-step">
                    <span class="lg-analysis-step-number">03</span>
                    <span class="lg-analysis-step-code">ANALYZE</span>
                    <h3>통계 분석</h3>
                    <p>
                        출현 빈도와 번호 구간 등
                        다양한 통계 정보를 계산합니다.
                    </p>
                </article>

                <article class="lg-analysis-step">
                    <span class="lg-analysis-step-number">04</span>
                    <span class="lg-analysis-step-code">PATTERN</span>
                    <h3>데이터 탐색</h3>
                    <p>
                        축적된 결과에서 확인할 수 있는
                        번호 분포와 데이터 특성을 탐색합니다.
                    </p>
                </article>

                <article class="lg-analysis-step">
                    <span class="lg-analysis-step-number">05</span>
                    <span class="lg-analysis-step-code">OUTPUT</span>
                    <h3>정보 제공</h3>
                    <p>
                        분석 결과를 표와 카드 등
                        사용자가 이해하기 쉬운 형태로 제공합니다.
                    </p>
                </article>

            </div>

        </div>
    </section>

    <section class="lg-analysis-principle">
        <div class="lg-shell">

            <div class="lg-analysis-principle-panel">

                <div>
                    <p class="lg-eyebrow">DATA PRINCIPLE</p>

                    <h2>
                        예측을 약속하기보다<br>
                        <strong>데이터를 보여줍니다.</strong>
                    </h2>
                </div>

                <div class="lg-analysis-principle-copy">
                    <p>
                        LottoGPT의 분석은 과거 로또 데이터를 기반으로
                        통계와 정보 확인을 돕기 위한 기능입니다.
                    </p>

                    <p>
                        로또 추첨은 무작위 방식이므로 과거 결과와 통계가
                        미래의 당첨번호 또는 당첨 가능성을 보장하지 않습니다.
                    </p>
                </div>

            </div>

        </div>
    </section>

</main>

<?php
include_once(G5_PATH . "/_tail.php");
?>
