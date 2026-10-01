<?php

include_once __DIR__ . '/_common.php';
include_once G5_PATH . '/include/lotto_app_access.lib.php';

lottoAppRequirePaidMember();

$memberType = isset($member['mb_type'])
    ? trim((string) $member['mb_type'])
    : '';
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#ffffff">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="LottoGPT">
<link rel="manifest" href="/app/manifest.webmanifest">
<link rel="apple-touch-icon" href="/app/icons/apple-touch-icon.png">
<title>LottoGPT 회원앱</title>
</head>
<body>
<main style="max-width:520px;margin:40px auto;padding:24px;font-family:sans-serif;">
    <h1>LottoGPT 회원앱</h1>

    <p>
        유료회원 접근 확인이 완료되었습니다.
    </p>

    <section style="margin-top:24px;padding:20px;border:1px solid #ddd;border-radius:12px;">
        <strong>현재 회원등급</strong>
        <p>
            <?=htmlspecialchars($memberType, ENT_QUOTES, 'UTF-8')?>
        </p>
    </section>

    <section style="margin-top:20px;padding:20px;border:1px solid #ddd;border-radius:12px;">
        <strong>로또 당첨결과</strong>
        <p>최근 당첨번호와 회차별 당첨결과를 확인할 수 있습니다.</p>
        <p>
            <a href="<?=htmlspecialchars(G5_URL . '/app/results.php', ENT_QUOTES, 'UTF-8')?>">
                당첨결과 보기
            </a>
        </p>
    </section>

    <section style="margin-top:20px;padding:20px;border:1px solid #ddd;border-radius:12px;">
        <strong>로또 데이터 통계</strong>
        <p>저장된 당첨번호를 기준으로 번호 출현 통계를 확인할 수 있습니다.</p>
        <p>
            <a href="<?=htmlspecialchars(G5_URL . '/app/stats.php', ENT_QUOTES, 'UTF-8')?>">
                로또 통계 보기
            </a>
        </p>
    </section>

    <section style="margin-top:20px;padding:20px;border:1px solid #ddd;border-radius:12px;">
        <strong>내 조합 · 내 당첨결과</strong>
        <p>회원에게 제공된 회차별 조합과 당첨결과를 확인할 수 있습니다.</p>
        <p>
            <a href="<?=htmlspecialchars(G5_URL . '/app/my_lotto.php', ENT_QUOTES, 'UTF-8')?>">
                내 조합 확인하기
            </a>
        </p>
    </section>

    <section style="margin-top:20px;padding:20px;border:1px solid #ddd;border-radius:12px;">
        <strong>앱 기능 준비 중</strong>
        <p>
            최근 당첨결과, 로또 통계, 내 조합,
            내 당첨결과와 앱 알림 기능을 순서대로 연결합니다.
        </p>
    </section>
</main>

<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register(
            '/app/service-worker.js',
            {scope: '/app/'}
        ).catch(function (error) {
            console.error('Service Worker registration failed:', error);
        });
    });
}
</script>
</body>
</html>
