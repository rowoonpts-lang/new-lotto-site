<?php

include_once(dirname(__DIR__)."/common.php");
include_once(G5_PATH."/include/lotto_admin_ip.lib.php");

$adminIpMbId = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$adminIpLevel = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

/*
 * 최고관리자를 제외한 모든 lpadmin 사용자는
 * 반드시 허용 IP에서만 관리자페이지를 이용할 수 있습니다.
 */
if (
    $adminIpMbId !== ''
    && $adminIpLevel >= LOTTO_ROLE_STAFF1
    && $adminIpLevel < LOTTO_ROLE_SUPER_ADMIN
) {
    $clientIp = lottoAdminGetClientIp();
    $allowedIpValue = isset($config['cf_ip'])
        ? trim((string) $config['cf_ip'])
        : '';

    if (
        $allowedIpValue === ''
        || $clientIp === ''
        || !lottoAdminIsAllowedIp($clientIp, $allowedIpValue)
    ) {
        set_session('ss_mb_id', '');
        set_session('ss_step2', '');

        alert(
            '허용되지 않은 IP에서는 관리자페이지에 접속할 수 없습니다.'
            .'\\n현재 접속 IP : '.$clientIp,
            G5_LADMIN_URL.'/login.php'
        );
        exit;
    }
}
?>
