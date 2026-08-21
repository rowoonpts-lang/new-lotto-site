<?php
include_once(dirname(__DIR__)."/_common.php");

function lottoConfigTokenCreate()
{
    $token = get_random_token_string(16);
    set_session('ss_lotto_config_token', $token);

    return $token;
}

function lottoConfigTokenCheck()
{
    $session_token = get_session('ss_lotto_config_token');
    $request_token = isset($_POST['token']) ? (string) $_POST['token'] : '';

    set_session('ss_lotto_config_token', '');

    if (
        $session_token === '' ||
        $request_token === '' ||
        !hash_equals($session_token, $request_token)
    ) {
        alert('올바른 방법으로 이용해 주십시오.', G5_LADMIN_URL);
        exit;
    }

    return true;
}
?>
