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

function lottoAppAccessCookieName()
{
    return 'lotto_app_access';
}

function lottoAppSetAccessCookie($token)
{
    $token = trim((string) $token);

    if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
        return false;
    }

    return setcookie(
        lottoAppAccessCookieName(),
        $token,
        array(
            'expires' => time() + (86400 * 31),
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax',
        )
    );
}

function lottoAppResolvePaidMember()
{
    global $is_member, $member;

    $cookieName = lottoAppAccessCookieName();

    $token = isset($_COOKIE[$cookieName])
        ? trim((string) $_COOKIE[$cookieName])
        : '';

    /*
     * 문자로 받은 회원 전용 링크를 통해 앱 인증 쿠키가 만들어졌다면
     * 일반 홈페이지 로그인 세션보다 앱 인증 회원을 우선합니다.
     */
    if ($token !== '') {
        if (!preg_match('/^[a-f0-9]{32}$/', $token)) {
            return array();
        }

        $tokenHash = hash('sha256', $token);
        $tokenHashSql = sql_real_escape_string($tokenHash);

        $resolvedMember = sql_fetch(
            "select m.*
               from l_lotto_share_link l
               inner join g5_member m
                 on m.mb_id = l.mb_id
              where l.token_hash = '{$tokenHashSql}'
              limit 1",
            false
        );

        if (
            !is_array($resolvedMember)
            || empty($resolvedMember['mb_id'])
        ) {
            return array();
        }

        $leaveDate = isset($resolvedMember['mb_leave_date'])
            ? trim((string) $resolvedMember['mb_leave_date'])
            : '';

        if ($leaveDate !== '') {
            return array();
        }

        $interceptDate = isset($resolvedMember['mb_intercept_date'])
            ? trim((string) $resolvedMember['mb_intercept_date'])
            : '';

        if (
            $interceptDate !== ''
            && $interceptDate <= date('Ymd', G5_SERVER_TIME)
        ) {
            return array();
        }

        if (!lottoAppIsPaidMember($resolvedMember)) {
            return array();
        }

        $member = $resolvedMember;
        $is_member = true;

        return $member;
    }

    /*
     * 앱 전용 인증이 아직 없는 경우에만
     * 기존 홈페이지 로그인 회원을 사용합니다.
     */
    if (
        $is_member
        && isset($member['mb_id'])
        && trim((string) $member['mb_id']) !== ''
        && lottoAppIsPaidMember($member)
    ) {
        return $member;
    }

    return array();
}

function lottoAppRequirePaidMember()
{
    global $is_member, $member;

    $resolvedMember = lottoAppResolvePaidMember();

    if (!empty($resolvedMember['mb_id'])) {
        return;
    }

    if (!$is_member) {
        http_response_code(401);

        echo '<!doctype html>';
        echo '<html lang="ko">';
        echo '<head>';
        echo '<meta charset="utf-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<title>LottoGPT 회원앱</title>';
        echo '</head>';
        echo '<body>';
        echo '<main style="max-width:520px;margin:80px auto;padding:24px;font-family:sans-serif;text-align:center;">';
        echo '<h1>앱 인증이 필요합니다.</h1>';
        echo '<p>문자로 받은 추천번호 링크를 먼저 열어주세요.</p>';
        echo '</main>';
        echo '</body>';
        echo '</html>';

        exit;
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
