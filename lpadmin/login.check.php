<?php

include_once("_common.php");
include_once(G5_PATH."/include/lotto_admin_ip.lib.php");

$mb_id = isset($_POST['mb_id'])
    ? trim((string) $_POST['mb_id'])
    : '';

$mb_password = isset($_POST['mb_password'])
    ? trim((string) $_POST['mb_password'])
    : '';

if ($mb_id === '' || $mb_password === '') {
    alert('회원아이디나 비밀번호가 공백이면 안됩니다.');
}

$mb = get_member($mb_id);

if (
    !(isset($mb['mb_id']) && $mb['mb_id'])
    || !login_password_check(
        $mb,
        $mb_password,
        $mb['mb_password']
    )
) {
    alert(
        '가입된 회원아이디가 아니거나 비밀번호가 틀립니다.'
        .'\\n비밀번호는 대소문자를 구분합니다.'
    );
}

$row = $mb;
$loginLevel = isset($row['mb_level'])
    ? (int) $row['mb_level']
    : 0;

if ($loginLevel < LOTTO_ROLE_STAFF1) {
    alert($config['cf_title'].' 직원만 접속이 가능합니다.');
}

if (!empty($row['mb_leave_date'])) {
    alert('탈퇴한 아이디 입니다.');
}

$clientIp = lottoAdminGetClientIp();

/*
 * 최고관리자를 제외한 관리자페이지 사용자는
 * 허용 IP가 반드시 존재하고 현재 IP와 일치해야 합니다.
 */
if ($loginLevel < LOTTO_ROLE_SUPER_ADMIN) {
    $allowedIpValue = isset($config['cf_ip'])
        ? trim((string) $config['cf_ip'])
        : '';

    if ($allowedIpValue === '') {
        alert(
            '허용 IP가 설정되어 있지 않아 관리자페이지에 접속할 수 없습니다.'
        );
    }

    if (
        $clientIp === ''
        || !lottoAdminIsAllowedIp($clientIp, $allowedIpValue)
    ) {
        alert(
            '허용되지 않은 IP에서는 관리자페이지에 접속할 수 없습니다.'
            .'\\n현재 접속 IP : '.$clientIp
        );
    }
}

/*
 * 최고관리자는 IP 제한 없이 접속하되
 * 성공한 로그인 IP를 별도 기록합니다.
 */
if ($loginLevel >= LOTTO_ROLE_SUPER_ADMIN) {
    lottoAdminRecordSuperIp(
        isset($mb['mb_id']) ? $mb['mb_id'] : '',
        $clientIp
    );
}

set_session('ss_mb_id', $mb['mb_id']);
generate_mb_key($mb);

if (function_exists('update_auth_session_token')) {
    update_auth_session_token($mb['mb_datetime']);
}

set_session('ss_step2', '');

goto_url(G5_LADMIN_URL);
?>
