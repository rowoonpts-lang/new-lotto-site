<?php

include_once(dirname(__DIR__)."/_common.php");

function lottoMemberTokenCreate()
{
    $token = get_session('ss_lotto_member_token');

    if ($token === '') {
        $token = get_random_token_string(16);
        set_session('ss_lotto_member_token', $token);
    }

    return $token;
}

function lottoMemberTokenCheck()
{
    $sessionToken = get_session('ss_lotto_member_token');
    $requestToken = isset($_POST['token'])
        ? (string) $_POST['token']
        : '';

    if (
        $sessionToken === ''
        || $requestToken === ''
        || !hash_equals($sessionToken, $requestToken)
    ) {
        return false;
    }

    return true;
}
?>
