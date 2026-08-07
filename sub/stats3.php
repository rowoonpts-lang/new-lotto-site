<?php
include_once("./_common.php");

$g5['title'] = 'LottoGPT 로또 이용 가이드';

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

<main class="lg-play-guide-page">

    <section class="lg-play-guide-hero">
        <div class="lg-shell">

            <p class="lg-eyebrow">LOTTOGPT PLAY GUIDE</p>

            <h1>
                로또를 더 합리적으로<br>
                <strong>이용하는 방법.</strong>
            </h1>

            <p class="lg-play-guide-hero-copy">
                로또의 통계와 조합 데이터는 과거 결과와 전체 조합의 특성을
                이해하는 데 도움을 줄 수 있습니다.
                하지만 특정 번호나 패턴의 미래 당첨을 보장하지는 않습니다.
            </p>

        </div>
    </section>

    <section class="lg-play-guide-section">
        <div class="lg-shell">

            <div class="lg-play-guide-heading">
                <p class="lg-eyebrow">BASIC PRINCIPLES</p>
                <h2>먼저 알아야 할 6가지</h2>
                <p>
                    번호를 선택하는 방식보다 중요한 것은
                    확률의 의미를 정확히 이해하고 무리하지 않는 것입니다.
                </p>
            </div>

            <div class="lg-play-guide-grid">

                <article class="lg-play-guide-card">
                    <span class="lg-play-guide-number">01</span>
                    <span class="lg-play-guide-code">EQUAL CHANCE</span>

                    <h3>모든 6개 번호 조합의 확률은 같습니다.</h3>

                    <p>
                        로또 6/45에서 특정한 6개 번호 한 조합이
                        1등 번호와 일치할 확률은 다른 조합과 동일합니다.
                    </p>
                </article>

                <article class="lg-play-guide-card">
                    <span class="lg-play-guide-number">02</span>
                    <span class="lg-play-guide-code">STATISTICS</span>

                    <h3>통계는 과거와 전체 조합을 설명합니다.</h3>

                    <p>
                        홀짝, 번호 합, 연속번호 같은 데이터는
                        과거 결과나 전체 조합의 분포를 이해하기 위한 정보입니다.
                    </p>
                </article>

                <article class="lg-play-guide-card">
                    <span class="lg-play-guide-number">03</span>
                    <span class="lg-play-guide-code">PATTERN</span>

                    <h3>특정 패턴이 미래 당첨을 보장하지 않습니다.</h3>

                    <p>
                        어떤 형태의 조합이 전체에서 많이 존재하더라도
                        특정 한 조합의 미래 당첨 확률이 높아지는 것은 아닙니다.
                    </p>
                </article>

                <article class="lg-play-guide-card">
                    <span class="lg-play-guide-number">04</span>
                    <span class="lg-play-guide-code">BUDGET</span>

                    <h3>구매 금액은 미리 정해 관리합니다.</h3>

                    <p>
                        구매 횟수를 늘리면 구입한 조합의 수는 늘어나지만
                        과도한 지출을 정당화하는 이유가 되지는 않습니다.
                    </p>
                </article>

                <article class="lg-play-guide-card">
                    <span class="lg-play-guide-number">05</span>
                    <span class="lg-play-guide-code">BELIEF</span>

                    <h3>징크스와 고정수는 확률을 바꾸지 않습니다.</h3>

                    <p>
                        꿈, 날짜, 특정 숫자에 대한 개인적인 의미는
                        번호를 선택하는 기준이 될 수 있지만 수학적 확률을 바꾸지는 않습니다.
                    </p>
                </article>

                <article class="lg-play-guide-card">
                    <span class="lg-play-guide-number">06</span>
                    <span class="lg-play-guide-code">AUTO / MANUAL</span>

                    <h3>자동과 수동의 1게임 당첨 확률은 같습니다.</h3>

                    <p>
                        번호를 직접 선택했는지 자동으로 선택했는지는
                        완성된 한 조합의 1등 당첨 확률에 영향을 주지 않습니다.
                    </p>
                </article>

            </div>

        </div>
    </section>

    <section class="lg-play-guide-data">
        <div class="lg-shell">

            <div class="lg-play-guide-heading">
                <p class="lg-eyebrow">COMBINATION REFERENCE</p>
                <h2>조합 분포는 이렇게 볼 수 있습니다.</h2>
                <p>
                    아래 값은 앞서 확인한 전체 조합 데이터를
                    이해하기 쉽게 요약한 참고 정보입니다.
                </p>
            </div>

            <div class="lg-play-guide-stats">

                <article>
                    <span>홀짝 주요 구성</span>

                    <strong>81.31%</strong>

                    <p>
                        2홀4짝, 3홀3짝, 4홀2짝 조합을
                        모두 합한 전체 조합 비율입니다.
                    </p>
                </article>

                <article>
                    <span>연속번호 포함 조합</span>

                    <strong>52.87%</strong>

                    <p>
                        2개 이상의 연속번호가 존재하는
                        전체 조합의 비율입니다.
                    </p>
                </article>

                <article>
                    <span>전체 6개 번호 조합</span>

                    <strong>8,145,060</strong>

                    <p>
                        45개의 번호 중 6개를 선택할 때
                        만들 수 있는 전체 조합 수입니다.
                    </p>
                </article>

            </div>

            <div class="lg-play-guide-explain">

                <div>
                    <p class="lg-eyebrow">HOW TO READ</p>

                    <h2>
                        많이 존재하는 형태와<br>
                        <strong>더 잘 당첨되는 조합은 다릅니다.</strong>
                    </h2>
                </div>

                <div class="lg-play-guide-explain-copy">
                    <p>
                        예를 들어 2홀4짝, 3홀3짝, 4홀2짝 형태를 합하면
                        전체 조합에서 큰 비중을 차지합니다.
                    </p>

                    <p>
                        이것은 그런 형태에 해당하는 서로 다른 조합의 개수가
                        많다는 의미입니다. 이미 완성된 특정 6개 번호 한 조합의
                        1등 확률이 다른 조합보다 높다는 의미는 아닙니다.
                    </p>
                </div>

            </div>

        </div>
    </section>

    <section class="lg-play-guide-notice">
        <div class="lg-shell">

            <div class="lg-play-guide-notice-panel">

                <span>RESPONSIBLE PLAY</span>

                <h2>데이터는 참고하고, 구매는 계획적으로.</h2>

                <p>
                    LottoGPT는 로또 데이터를 분석하고 이해하기 위한 정보를 제공합니다.
                    과거 데이터, 통계, 번호 패턴은 미래의 당첨번호나
                    당첨 가능성을 보장하지 않습니다.
                </p>

            </div>

        </div>
    </section>

</main>

<?php
include_once(G5_PATH . "/_tail.php");
?>
