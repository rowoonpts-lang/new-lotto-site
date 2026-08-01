<?php

if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * 다음 회원 코드를 6자리 문자열로 생성합니다.
 */
function fnNewMbCode()
{
    $sql = "select mb_code
              from g5_member
             where 1=1
             order by mb_code * 1 desc
             limit 1";

    $row = sql_fetch($sql);

    $mbCode = '000001';

    if (!empty($row['mb_code'])) {
        $mbCode = str_pad(
            ((int) $row['mb_code']) + 1,
            6,
            '0',
            STR_PAD_LEFT
        );
    }

    return $mbCode;
}

/**
 * 회원 부가정보가 없으면 기본값으로 생성합니다.
 */
function setEtcInfo($mbId, $mbDb = '')
{
    $sql = "select count(mb_id) as cnt
              from g5_member_etc
             where mb_id = '{$mbId}'";

    $row = sql_fetch($sql);

    if ((int) $row['cnt'] > 0) {
        return;
    }

    $sql = "select mb_hp
              from g5_member
             where mb_id = '{$mbId}'";

    $memberRow = sql_fetch($sql);

    $mbHpEtc = str_replace('-', '', $memberRow['mb_hp'] ?? '');
    $freeNumDate = date('Y-m-d', strtotime('+1 month'));

    $sql = "insert into g5_member_etc set
                mb_id = '{$mbId}',
                mb_hp_etc = '{$mbHpEtc}',
                mb_db = '{$mbDb}',
                free_num_qty = '10',
                free_num_date = '{$freeNumDate}'";

    sql_query($sql);
}

/**
 * 관리자 작업 로그를 저장합니다.
 */
function fnSetLog($mbId, $log)
{
    $remoteAddr = $_SERVER['REMOTE_ADDR'] ?? '';

    $sql = "insert into l_log set
                ll_ip = '{$remoteAddr}',
                mb_id = '{$mbId}',
                ll_content = '{$log}',
                ll_datetime = now()";

    sql_query($sql);
}

/**
 * 회원 메모를 저장하고 최근 메모 정보를 갱신합니다.
 */
function fnSetMemo(
    $mbId,
    $fromMbId,
    $lmMemoType = '',
    $lmMemo = '',
    $misu = 0,
    $lmAlarmType = '',
    $lmAlarmDate = '',
    $lmPrice = ''
) {
    $addQuery = '';

    if ((int) $misu > 0) {
        $addQuery .= " , lm_misu = '{$misu}'";

        $sql = "update g5_member_etc
                   set recent_misu = '{$misu}'
                 where mb_id = '{$mbId}'";

        sql_query($sql);
    }

    if ($lmAlarmType !== '') {
        $addQuery .= " , lm_alarm_type = '{$lmAlarmType}'";
    }

    if ($lmAlarmDate !== '') {
        $addQuery .= " , lm_alarm_date = '{$lmAlarmDate}'";
    }

    if ($lmPrice !== '') {
        $addQuery .= " , lm_price = '{$lmPrice}'";
    }

    $sql = "insert into l_memo set
                mb_id = '{$mbId}',
                from_mb_id = '{$fromMbId}',
                lm_memo_type = '{$lmMemoType}',
                lm_memo = '{$lmMemo}',
                lm_datetime = now()
                {$addQuery}";

    sql_query($sql);

    $sql = "update g5_member_etc set
                recent_select = '{$lmMemoType}',
                recent_memo = '{$lmMemo}'
             where mb_id = '{$mbId}'";

    sql_query($sql);
}

/**
 * 회원 상담 메모 상태 목록을 반환합니다.
 */
function fnGetMemoStatus()
{
    return [
        '부재',
        '유력예약',
        '단순안내',
        '도입거절',
        '안내거절',
        '무통발송',
        '결제',
        '인계',
        '클래임',
        '단순인바',
        '주소',
        '불량디비',
        '대리가입',
    ];
}
