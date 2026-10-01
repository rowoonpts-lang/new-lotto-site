<?php

if (!defined('_GNUBOARD_')) {
    exit;
}

function lottoAppIsPaidMember(array $member)
{
    $memberId = isset($member['mb_id'])
        ? trim((string) $member['mb_id'])
        : '';

    $memberType = isset($member['mb_type'])
        ? trim((string) $member['mb_type'])
        : '';

    return $memberId !== ''
        && $memberType !== ''
        && $memberType !== '무료회원';
}

function lottoAppRequirePaidMember()
{
    global $is_member, $member;

    if (!$is_member) {
        alert(
            '로그인이 필요한 서비스입니다.',
            G5_BBS_URL
            . '/login.php?url='
            . urlencode(G5_URL . '/app/')
        );
    }

    if (lottoAppIsPaidMember($member)) {
        return;
    }

    http_response_code(403);

    echo '<!doctype html>';
    echo '<html lang="ko">';
    echo '<head>';
    echo '<meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>LottoGPT 회원앱</title>';
    echo '</head>';
    echo '<body>';
    echo '<main style="max-width:520px;margin:80px auto;padding:24px;font-family:sans-serif;text-align:center;">';
    echo '<h1>유료회원 전용 서비스입니다.</h1>';
    echo '<p>현재 회원등급으로는 회원앱을 이용할 수 없습니다.</p>';
    echo '<p><a href="' . htmlspecialchars(G5_URL, ENT_QUOTES, 'UTF-8') . '">홈페이지로 이동</a></p>';
    echo '</main>';
    echo '</body>';
    echo '</html>';

    exit;
}
