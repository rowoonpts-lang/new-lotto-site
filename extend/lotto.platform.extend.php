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

/**
 * 유료 회원 등급 목록을 반환합니다.
 */
function fnGetTypePre()
{
    return [
        '프로',
        '탑클래스',
        '퍼펙트',
        '퍼스트',
    ];
}

/**
 * 약정 대상 회원 등급 목록을 반환합니다.
 */
function fnGetTypeYak()
{
    return [
        '탑클래스',
        '퍼펙트',
        '퍼스트',
    ];
}

/**
 * 전체 회원 등급 목록을 반환합니다.
 */
function fnGetType()
{
    return [
        '무료회원',
        '프로',
        '탑클래스',
        '퍼펙트',
        '퍼스트',
    ];
}

/**
 * 회원 등급별 기본 가격을 반환합니다.
 */
function fnGetTypePrice($mbType)
{
    $prices = [
        '무료회원' => 0,
        '프로' => 121000,
        '탑클래스' => 880000,
        '퍼펙트' => 2200000,
        '퍼스트' => 6500000,
    ];

    return $prices[$mbType] ?? 0;
}

/**
 * 080 수신거부 번호 목록을 반환합니다.
 */
function fnGetSpan()
{
    $sql = "SELECT `Phone_No` FROM `Msg_Spam`";
    $result = sql_query($sql);
    $list = [];

    while ($row = sql_fetch_array($result)) {
        $list[] = $row['Phone_No'];
    }

    return $list;
}

/**
 * 관리자 팀 목록을 반환합니다.
 */
function getTeamList()
{
    return [
        'TM1',
        'TM2',
        'TM3',
        'TM4',
        '1차',
        'CS',
    ];
}

/**
 * 결제 담당 팀 목록을 반환합니다.
 */
function getTeamList2()
{
    return [
        'SU',
    ];
}

/**
 * 추가 팀 목록을 반환합니다.
 */
function getTeamList3()
{
    return [
        'SU',
    ];
}

/**
 * 관리자 권한 목록을 반환합니다.
 */
function getLevelList()
{
    return [
        'LEVEL 1 : 검색전용/알람리스트',
        'LEVEL 2 : 검색전용/알람리스트/무통장신청/무통장입금/카드승인/컨택통계',
        'LEVEL 3 : 전체회원/검색전용/알람리스트/결제리스트/무통장신청/무통장입금/카드승인/상담요청/결제시도/문자디비/광고디비리스트/직원등록/컨택통계/당첨리스트/당첨배출현황',
    ];
}

/**
 * 관리자 권한 이름을 출력합니다.
 */
function getLevelText($mbLevel)
{
    switch ((string) $mbLevel) {
        case '5':
            echo 'LEVEL 1';
            break;

        case '6':
            echo 'LEVEL 2';
            break;

        case '7':
            echo 'LEVEL 3';
            break;
    }
}

/**
 * 관리자 권한별 접근 메뉴 설명을 출력합니다.
 */
function getLevelPage($mbLevel)
{
    switch ((string) $mbLevel) {
        case '5':
            echo '검색전용/알람리스트';
            break;

        case '6':
            echo '검색전용/알람리스트/무통장신청/무통장입금/카드승인/컨택통계';
            break;

        case '7':
            echo '전체회원/검색전용/알람리스트/결제리스트/무통장신청/무통장입금/카드승인/상담요청/결제시도/문자디비/광고디비리스트/직원등록/컨택통계/당첨리스트/당첨배출현황';
            break;
    }
}


/**
 * 로또 번호를 번호 구간별 공 색상 태그로 변환합니다.
 */
function getBallStyle2($number, $ver = 1)
{
    $values = is_array($number)
        ? $number
        : explode(',', str_replace(' ', '', (string) $number));

    $tags = array();
    $validNumbers = array();

    foreach ($values as $value) {
        if ($value === '') {
            continue;
        }

        $numberValue = (int) $value;

        if ($numberValue < 1 || $numberValue > 45) {
            continue;
        }

        $validNumbers[] = $numberValue;
    }

    foreach ($validNumbers as $index => $numberValue) {
        if ($numberValue <= 10) {
            $classes = 'yellow bg_yellow bgc_y';
        } elseif ($numberValue <= 20) {
            $classes = 'blue bg_sky bgc_s';
        } elseif ($numberValue <= 30) {
            $classes = 'red bg_red bgc_r';
        } elseif ($numberValue <= 40) {
            $classes = 'gray bg_gray bgc_gr';
        } else {
            $classes = 'green bg_green bgc_g';
        }

        $tags[] = "<p class=\"l_ball {$classes}\">{$numberValue}</p>";

        if ($index === 5 && (string) $ver === '2' && count($validNumbers) > 6) {
            $tags[] = '<p class="l_ball l_plus">+</p>';
        }
    }

    return implode('', $tags);
}

/**
 * 관리자에 등록된 회차별 당첨번호를 반환합니다.
 *
 * 외부 서비스에 의존하지 않고 l_lucky_custom 테이블만 조회합니다.
 */
function getLuckyNum($turn)
{
    $turn = (int) $turn;

    $emptyResult = array(
        'returnValue' => 'fail',
        'drwtNo1' => 0,
        'drwtNo2' => 0,
        'drwtNo3' => 0,
        'drwtNo4' => 0,
        'drwtNo5' => 0,
        'drwtNo6' => 0,
        'bnusNo' => 0,
        'drwNoDate' => '',
    );

    if ($turn < 1) {
        return $emptyResult;
    }

    $tableCheck = sql_fetch(
        "SHOW TABLES LIKE 'l_lucky_custom'",
        false
    );

    if (!is_array($tableCheck) || count($tableCheck) < 1) {
        return $emptyResult;
    }

    $row = sql_fetch(
        "select turn,
                num1,
                num2,
                num3,
                num4,
                num5,
                num6,
                num7,
                lc_datetime
           from l_lucky_custom
          where turn = '{$turn}'
          order by lc_datetime desc, lc_id desc
          limit 1",
        false
    );

    if (!is_array($row) || empty($row['turn'])) {
        return $emptyResult;
    }

    $numbers = array();

    for ($index = 1; $index <= 7; $index++) {
        $numberValue = isset($row['num'.$index])
            ? (int) $row['num'.$index]
            : 0;

        if ($numberValue < 1 || $numberValue > 45) {
            return $emptyResult;
        }

        $numbers[$index] = $numberValue;
    }

    return array(
        'returnValue' => 'success',
        'drwtNo1' => $numbers[1],
        'drwtNo2' => $numbers[2],
        'drwtNo3' => $numbers[3],
        'drwtNo4' => $numbers[4],
        'drwtNo5' => $numbers[5],
        'drwtNo6' => $numbers[6],
        'bnusNo' => $numbers[7],
        'drwNoDate' => date(
            'Y-m-d',
            strtotime((string) $row['lc_datetime'])
        ),
    );
}

/**
 * 설정된 기준 회차와 기준일로 현재 로또 회차를 계산합니다.
 */
function getTurn()
{
    global $config;

    $baseTurn = isset($config['cf_1']) ? (int) $config['cf_1'] : 0;
    $baseDate = isset($config['cf_2']) ? trim((string) $config['cf_2']) : '';

    if ($baseTurn < 1 || $baseDate === '' || strtotime($baseDate) === false) {
        return 0;
    }

    $baseTimestamp = strtotime($baseDate);
    $todayTimestamp = strtotime(date('Y-m-d'));

    if ($todayTimestamp < $baseTimestamp) {
        return $baseTurn;
    }

    $elapsedWeeks = (int) floor(
        ($todayTimestamp - $baseTimestamp) / 604800
    );

    return $baseTurn + $elapsedWeeks;
}
