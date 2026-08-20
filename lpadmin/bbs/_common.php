<?php

include_once(dirname(__DIR__)."/_common.php");

function lottoBbsTokenCreate()
{
    $token = get_random_token_string(16);
    set_session('ss_lotto_bbs_token', $token);

    return $token;
}

function lottoBbsTokenCheck()
{
    $sessionToken = get_session('ss_lotto_bbs_token');
    $requestToken = isset($_POST['token'])
        ? (string) $_POST['token']
        : '';

    set_session('ss_lotto_bbs_token', '');

    if (
        $sessionToken === ''
        || $requestToken === ''
        || !hash_equals($sessionToken, $requestToken)
    ) {
        alert(
            '올바른 방법으로 이용해 주십시오.',
            G5_LADMIN_URL
        );
        exit;
    }

    return true;
}
?>
