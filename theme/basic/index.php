<?php
if (!defined('_INDEX_')) define('_INDEX_', true);
if (!defined('_GNUBOARD_')) exit;

if (G5_IS_MOBILE) {
    include_once(G5_THEME_MOBILE_PATH.'/index.php');
    return;
}

include_once(G5_THEME_PATH.'/head.php');

$turn = max(1, (int) getTurn() - 1);
$notice_rows = array();
$notice_result = sql_query("select wr_id, wr_subject, wr_datetime from g5_write_notice order by wr_datetime desc limit 3", false);
if ($notice_result) {
    while ($notice_row = sql_fetch_array($notice_result)) {
        $notice_rows[] = $notice_row;
    }
}

$analysis_total = (int) ($config['cf_lucky_1'] ?? 0)
    + (int) ($config['cf_lucky_2'] ?? 0)
    + (int) ($config['cf_lucky_3'] ?? 0);
?>
<link rel="stylesheet" href="<?=G5_THEME_CSS_URL?>/lottogpt.css?ver=20260806">
<script>document.body.classList.add('lottogpt-page');</script>

<main class="lg-home">
    <section class="lg-hero">
        <div class="lg-shell lg-hero-grid">
            <div class="lg-hero-copy">
                <div class="lg-live-badge"><span></span> LOTTOGPT AI ENGINE ACTIVE</div>
                <p class="lg-eyebrow">PREMIUM AI LOTTO DATA PLATFORM</p>
                <h1>AI가 분석한 데이터,<br><strong>가능성의 새로운 기준</strong></h1>
                <p class="lg-lead">누적 회차와 통계 데이터를 기반으로 번호 흐름을 분석하고, 사용자가 더 쉽게 판단할 수 있도록 핵심 정보를 정리합니다.</p>
                <div class="lg-actions">
                    <a class="lg-btn lg-btn-primary" href="<?=G5_URL?>/sub/my_lotto.php">AI 번호 분석 시작</a>
                    <a class="lg-btn lg-btn-secondary" href="<?=G5_URL?>/sub/stats.php">지난 회차 분석 보기</a>
                </div>
                <ul class="lg-trust-list">
                    <li><span>AI</span> 데이터 기반 분석</li>
                    <li><span>23Y</span> 누적 회차 데이터</li>
                    <li><span>SAFE</span> 통계 정보 중심</li>
                </ul>
            </div>

            <div class="lg-analysis-card">
                <div class="lg-card-head">
                    <div><span class="lg-status-dot"></span>AI 분석 현황</div>
                    <span>실시간 분석 중</span>
                </div>
                <div class="lg-analysis-main">
                    <div class="lg-gauge" aria-label="분석 진행률 94퍼센트">
                        <div><strong>94%</strong><span>분석 완료</span></div>
                    </div>
                    <div class="lg-orbit" aria-hidden="true">
                        <div class="lg-orbit-core">AI</div>
                        <span class="lg-orbit-ring lg-orbit-ring-1"></span>
                        <span class="lg-orbit-ring lg-orbit-ring-2"></span>
                        <span class="lg-orbit-node lg-orbit-node-1"></span>
                        <span class="lg-orbit-node lg-orbit-node-2"></span>
                        <span class="lg-orbit-node lg-orbit-node-3"></span>
                        <span class="lg-orbit-node lg-orbit-node-4"></span>
                    </div>
                </div>
                <div class="lg-progress"><span></span></div>
                <div class="lg-number-title">
                    <strong>추천 번호 UI</strong>
                    <small>기능 연결 전 디자인 샘플</small>
                </div>
                <ul class="lg-balls">
                    <li>03</li><li>08</li><li>14</li><li>27</li><li>38</li><li>42</li>
                </ul>
                <div class="lg-card-stats">
                    <div><span>분석 회차</span><strong><?=number_format($turn)?>회</strong></div>
                    <div><span>배출 조합</span><strong><?=number_format($analysis_total)?>개</strong></div>
                    <div><span>분석 상태</span><strong>READY</strong></div>
                    <div><span>업데이트</span><strong>WEEKLY</strong></div>
                </div>
            </div>
        </div>
    </section>
    <section class="lg-metrics">
        <div class="lg-shell lg-metric-grid">
            <article><span>현재 분석 회차</span><strong><?=number_format($turn)?>회</strong><small>최신 회차 기준</small></article>
            <article><span>1등 배출 조합</span><strong><?=number_format((int)($config['cf_lucky_1'] ?? 0))?>개</strong><small>관리 데이터 기준</small></article>
            <article><span>2등 배출 조합</span><strong><?=number_format((int)($config['cf_lucky_2'] ?? 0))?>개</strong><small>관리 데이터 기준</small></article>
            <article><span>3등 배출 조합</span><strong><?=number_format((int)($config['cf_lucky_3'] ?? 0))?>개</strong><small>관리 데이터 기준</small></article>
            <article><span>서비스 상태</span><strong class="lg-teal">ONLINE</strong><small>정상 운영 중</small></article>
        </div>
    </section>

    <section class="lg-section">
        <div class="lg-shell">
            <div class="lg-section-title">
                <p>LOTTOGPT ANALYSIS PROCESS</p>
                <h2>데이터가 분석 결과로 이어지는 과정</h2>
            </div>
            <div class="lg-process-grid">
                <article><b>01</b><i class="fas fa-database"></i><h3>데이터 수집</h3><p>누적 회차와 공개 통계 데이터를 정리합니다.</p></article>
                <article><b>02</b><i class="fas fa-brain"></i><h3>패턴 분석</h3><p>구간, 홀짝, 빈도 등 여러 기준으로 비교합니다.</p></article>
                <article><b>03</b><i class="fas fa-bullseye"></i><h3>조합 구성</h3><p>조건에 맞는 번호 조합을 단계적으로 구성합니다.</p></article>
                <article><b>04</b><i class="fas fa-chart-line"></i><h3>결과 제공</h3><p>사용자가 이해하기 쉬운 데이터 화면으로 제공합니다.</p></article>
            </div>
        </div>
    </section>

    <section class="lg-section lg-section-dark">
        <div class="lg-shell lg-content-grid">
            <article class="lg-panel lg-insight-panel">
                <div class="lg-panel-head"><h2>AI 인사이트</h2><a href="<?=G5_URL?>/sub/stats.php">데이터랩 보기</a></div>
                <div class="lg-insight-grid">
                    <div><span>HOT NUMBER</span><strong>14 · 27 · 38</strong><small>화면 구성용 샘플</small></div>
                    <div><span>STRONG RANGE</span><strong>21 ~ 30</strong><small>화면 구성용 샘플</small></div>
                    <div><span>ODD : EVEN</span><strong>3 : 3</strong><small>균형 조합 예시</small></div>
                </div>
            </article>

            <article class="lg-panel lg-report-panel">
                <div class="lg-panel-head"><h2>최근 공지사항</h2><a href="<?=G5_BBS_URL?>/board.php?bo_table=notice">전체보기</a></div>
                <ul class="lg-notice-list">
                    <?php if ($notice_rows) { ?>
                        <?php foreach ($notice_rows as $notice) { ?>
                            <li>
                                <a href="<?=G5_BBS_URL?>/board.php?bo_table=notice&amp;wr_id=<?=(int)$notice['wr_id']?>"><?=get_text($notice['wr_subject'])?></a>
                                <time><?=substr((string)$notice['wr_datetime'], 0, 10)?></time>
                            </li>
                        <?php } ?>
                    <?php } else { ?>
                        <li class="lg-empty">등록된 공지사항이 없습니다.</li>
                    <?php } ?>
                </ul>
            </article>

            <article class="lg-panel lg-premium-panel">
                <span class="lg-premium-label">PREMIUM AI</span>
                <h2>더 깊이 있는 분석을 경험하세요</h2>
                <ul><li>등급별 서비스 안내</li><li>회원 전용 페이지 연결</li><li>기존 결제·회원 기능 유지</li></ul>
                <a class="lg-btn lg-btn-primary" href="<?=G5_URL?>/sub/sub0201.php">멤버십 자세히 보기</a>
            </article>
        </div>
    </section>

    <section class="lg-quote">
        <div class="lg-shell">데이터는 과거를 말하고, AI는 가능성을 분석합니다.</div>
    </section>
</main>

<script>
$(function () {
    var menuNames = ['AI 소개', '멤버십', '데이터랩', '고객지원', 'MY GPT', 'Premium AI'];
    $('.header .menu > li > a').each(function (index) {
        if (menuNames[index]) $(this).text(menuNames[index]);
    });
    $('.header .logo img').attr('alt', 'LottoGPT');
});
</script>

<?php include_once(G5_THEME_PATH.'/tail.php'); ?>
