<?php

if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * 관리자 접속 IP를 반환합니다.
 *
 * common.php에서 Cloudflare 사용 시 REMOTE_ADDR를 실제 접속자 IP로
 * 정규화하므로 관리자 보안도 REMOTE_ADDR를 기준으로 사용합니다.
 */
function lottoAdminGetClientIp()
{
    $remoteIp = isset($_SERVER['REMOTE_ADDR'])
        ? trim((string) $_SERVER['REMOTE_ADDR'])
        : '';

    $host = isset($_SERVER['HTTP_HOST'])
        ? strtolower(trim((string) $_SERVER['HTTP_HOST']))
        : '';

    $forwardedHost = isset($_SERVER['HTTP_X_FORWARDED_HOST'])
        ? strtolower(trim((string) $_SERVER['HTTP_X_FORWARDED_HOST']))
        : '';

    /*
     * GitHub Codespaces에서는 외부 요청이 localhost 프록시를 통해
     * PHP 서버로 전달됩니다.
     *
     * 이 환경에서는:
     * - REMOTE_ADDR = 127.0.0.1 또는 ::1
     * - HTTP_HOST = localhost:포트
     * - HTTP_X_FORWARDED_HOST = *.app.github.dev
     * - HTTP_X_FORWARDED_FOR = 실제 접속자 IP
     *
     * 일반 운영환경에서는 X-Forwarded-For를 신뢰하지 않습니다.
     */
    $isCodespacesForwardedHost = preg_match(
        '/(^|\.)app\.github\.dev(?::\d+)?$/',
        $forwardedHost
    ) === 1;

    $isCodespacesDirectHost = preg_match(
        '/(^|\.)app\.github\.dev(?::\d+)?$/',
        $host
    ) === 1;

    $isLocalProxy = in_array(
        $remoteIp,
        array('127.0.0.1', '::1'),
        true
    );

    if (
        $isLocalProxy
        && ($isCodespacesForwardedHost || $isCodespacesDirectHost)
    ) {
        $forwardedFor = isset($_SERVER['HTTP_X_FORWARDED_FOR'])
            ? trim((string) $_SERVER['HTTP_X_FORWARDED_FOR'])
            : '';

        if ($forwardedFor !== '') {
            $forwardedIps = explode(',', $forwardedFor);

            foreach ($forwardedIps as $forwardedIp) {
                $forwardedIp = trim((string) $forwardedIp);

                if (
                    $forwardedIp !== ''
                    && filter_var(
                        $forwardedIp,
                        FILTER_VALIDATE_IP
                    ) !== false
                ) {
                    return $forwardedIp;
                }
            }
        }
    }

    if (
        $remoteIp === ''
        || filter_var($remoteIp, FILTER_VALIDATE_IP) === false
    ) {
        return '';
    }

    return $remoteIp;
}

/**
 * | 구분 허용 IP 문자열을 유효한 IP 배열로 변환합니다.
 */
function lottoAdminParseAllowedIps($value)
{
    $value = trim((string) $value);

    if ($value === '') {
        return array();
    }

    $parts = explode('|', $value);
    $allowedIps = array();

    foreach ($parts as $part) {
        $ip = trim((string) $part);

        if ($ip === '') {
            continue;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
            continue;
        }

        $allowedIps[$ip] = $ip;
    }

    return array_values($allowedIps);
}

/**
 * 현재 IP가 허용 IP인지 확인합니다.
 */
function lottoAdminIsAllowedIp($ip, $allowedIpValue)
{
    $ip = trim((string) $ip);

    if ($ip === '') {
        return false;
    }

    return in_array(
        $ip,
        lottoAdminParseAllowedIps($allowedIpValue),
        true
    );
}

/**
 * 최고관리자 접속 IP를 기록합니다.
 */
function lottoAdminRecordSuperIp($mbId, $ip)
{
    $mbId = trim((string) $mbId);
    $ip = trim((string) $ip);

    if (
        $mbId === ''
        || $ip === ''
        || filter_var($ip, FILTER_VALIDATE_IP) === false
    ) {
        return false;
    }

    $mbIdSql = sql_real_escape_string($mbId);
    $ipSql = sql_real_escape_string($ip);

    return sql_query(
        "insert into l_super_admin_ip_log (
            mb_id,
            ip_address,
            first_access_at,
            last_access_at,
            access_count
        ) values (
            '{$mbIdSql}',
            '{$ipSql}',
            now(),
            now(),
            1
        )
        on duplicate key update
            last_access_at = now(),
            access_count = access_count + 1",
        false
    );
}
