<?php
include_once("./_common.php");

$g5['title'] = 'LottoGPT 등급안내';

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

<main class="lg-membership-page">

    <section class="lg-membership-hero">
        <div class="lg-shell">
            <p class="lg-eyebrow">LOTTOGPT MEMBERSHIP</p>

            <h1>
                나에게 맞는<br>
                <strong>분석 서비스를 선택하세요.</strong>
            </h1>

            <p>
                LottoGPT는 이용 목적과 분석 범위에 따라
                네 가지 서비스 등급을 제공합니다.
            </p>
        </div>
    </section>

    <section class="lg-membership-section">
        <div class="lg-shell">

            <div class="lg-membership-grid">

                <article class="lg-membership-card lg-membership-basic">
                    <div class="lg-membership-card-head">
                        <span class="lg-membership-level">01 · BASIC</span>
                        <h2>Basic</h2>
                        <p>기본 데이터 분석</p>
                    </div>

                    <div class="lg-membership-price">
                        <strong>220,000</strong>
                        <span>원</span>
                    </div>

                    <ul class="lg-membership-features">
                        <li>로또 데이터 기반 기본 분석</li>
                        <li>당첨번호 및 회차 데이터 확인</li>
                        <li>기본 통계 정보 제공</li>
                    </ul>

                    <a
                        class="lg-membership-btn pop_res_open"
                        onclick="fnShowpop('1')"
                    >
                        상담문의
                    </a>
                </article>

                <article class="lg-membership-card lg-membership-pro">
                    <div class="lg-membership-badge">RECOMMENDED</div>

                    <div class="lg-membership-card-head">
                        <span class="lg-membership-level">02 · PRO</span>
                        <h2>Pro</h2>
                        <p>고급 데이터 분석</p>
                    </div>

                    <div class="lg-membership-price">
                        <strong>440,000</strong>
                        <span>원</span>
                    </div>

                    <ul class="lg-membership-features">
                        <li>Basic 등급의 분석 기능 포함</li>
                        <li>확장된 데이터 통계 분석</li>
                        <li>다양한 번호 패턴 정보 제공</li>
                    </ul>

                    <a
                        class="lg-membership-btn pop_res_open"
                        onclick="fnShowpop('2')"
                    >
                        상담문의
                    </a>
                </article>

                <article class="lg-membership-card lg-membership-premium">
                    <div class="lg-membership-card-head">
                        <span class="lg-membership-level">03 · PREMIUM</span>
                        <h2>Premium</h2>
                        <p>프리미엄 분석 서비스</p>
                    </div>

                    <div class="lg-membership-price lg-membership-price-contact">
                        <strong>금액문의</strong>
                    </div>

                    <ul class="lg-membership-features">
                        <li>상세 서비스 내용은 상담을 통해 안내합니다.</li>
                    </ul>

                    <a
                        class="lg-membership-btn pop_res_open"
                        onclick="fnShowpop('3')"
                    >
                        상담문의
                    </a>
                </article>

                <article class="lg-membership-card lg-membership-ai-premium">
                    <div class="lg-membership-card-head">
                        <span class="lg-membership-level">04 · AI PREMIUM</span>
                        <h2>AI Premium</h2>
                        <p>맞춤형 AI 분석</p>
                    </div>

                    <div class="lg-membership-price lg-membership-price-contact">
                        <strong>금액문의</strong>
                    </div>

                    <ul class="lg-membership-features">
                        <li>Pro 등급의 분석 기능 포함</li>
                        <li>확장형 데이터 분석 서비스</li>
                        <li>이용 목적에 따른 맞춤 상담</li>
                    </ul>

                    <a
                        class="lg-membership-btn pop_res_open"
                        onclick="fnShowpop('4')"
                    >
                        상담문의
                    </a>
                </article>

            </div>

            <div class="lg-membership-notice">
                <span>NOTICE</span>
                <p>
                    서비스 내용과 이용 기간은 상담 과정에서 최종 확인될 수 있습니다.
                    LottoGPT의 분석 정보는 당첨을 보장하지 않으며
                    데이터 확인과 통계 분석을 위한 참고 자료로 제공됩니다.
                </p>
            </div>

        </div>
    </section>

</main>

<?php
include_once(G5_PATH . "/_tail.php");
?>
