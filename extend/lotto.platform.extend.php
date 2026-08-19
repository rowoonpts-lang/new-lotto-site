<?php

if (!defined('_GNUBOARD_')) {
    exit;
}

/**
 * GitHub Codespaces 프록시 환경에서는 HTTP_HOST가 localhost로 전달될 수 있으므로
 * G5_URL의 공개 호스트와 동일한 URL은 같은 사이트로 인정합니다.
 */
add_replace('check_same_url_host', 'lottoCodespacesCheckSameUrlHost', 10, 6);

function lottoCodespacesCheckSameUrlHost(
    $isDifferentHost,
    $parsedUrl,
    $host,
    $isHostCheck,
    $returnUrl,
    $isRedirect
) {
    if (
        getenv('CODESPACES') !== 'true'
        || !defined('G5_URL')
        || $isHostCheck
        || empty($parsedUrl['host'])
    ) {
        return $isDifferentHost;
    }

    $siteUrl = parse_url(G5_URL);
    $siteHost = $siteUrl['host'] ?? '';

    if ($siteHost !== '' && strcasecmp($parsedUrl['host'], $siteHost) === 0) {
        return false;
    }

    return $isDifferentHost;
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
        'Basic',
        'Pro',
        'Premium',
        'AI Premium',
    ];
}

/**
 * 약정 대상 회원 등급 목록을 반환합니다.
 */
function fnGetTypeYak()
{
    return [
        'Pro',
        'Premium',
        'AI Premium',
    ];
}

/**
 * 전체 회원 등급 목록을 반환합니다.
 */
function fnGetType()
{
    return [
        '무료회원',
        'Basic',
        'Pro',
        'Premium',
        'AI Premium',
    ];
}

/**
 * 회원 등급별 기본 가격을 반환합니다.
 */
function fnGetTypePrice($mbType)
{
    $prices = [
        '무료회원' => 0,
        'Basic' => 220000,
        'Pro' => 440000,
        'Premium' => null,
        'AI Premium' => null,
    ];

    return array_key_exists($mbType, $prices)
        ? $prices[$mbType]
        : null;
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

/**
 * Lotto 관리자 권한 레벨
 */
if (!defined('LOTTO_ROLE_STAFF1')) {
    define('LOTTO_ROLE_STAFF1', 5);
}

if (!defined('LOTTO_ROLE_STAFF2')) {
    define('LOTTO_ROLE_STAFF2', 6);
}

if (!defined('LOTTO_ROLE_TEAM_LEADER')) {
    define('LOTTO_ROLE_TEAM_LEADER', 7);
}

if (!defined('LOTTO_ROLE_ADMIN')) {
    define('LOTTO_ROLE_ADMIN', 9);
}

if (!defined('LOTTO_ROLE_SUPER_ADMIN')) {
    define('LOTTO_ROLE_SUPER_ADMIN', 10);
}

/**
 * Lotto 관리자 권한 이름을 반환합니다.
 */
function lottoGetAdminRoleName($mbLevel)
{
    switch ((int) $mbLevel) {
        case LOTTO_ROLE_STAFF1:
            return '직원1';

        case LOTTO_ROLE_STAFF2:
            return '직원2';

        case LOTTO_ROLE_TEAM_LEADER:
            return '팀장';

        case LOTTO_ROLE_ADMIN:
            return '관리자';

        case LOTTO_ROLE_SUPER_ADMIN:
            return '최고관리자';

        default:
            return '회원';
    }
}

/**
 * Lotto 관리자페이지를 사용하는 직원 권한인지 확인합니다.
 */
function lottoIsStaffLevel($mbLevel)
{
    return in_array(
        (int) $mbLevel,
        array(
            LOTTO_ROLE_STAFF1,
            LOTTO_ROLE_STAFF2,
            LOTTO_ROLE_TEAM_LEADER,
            LOTTO_ROLE_ADMIN,
            LOTTO_ROLE_SUPER_ADMIN,
        ),
        true
    );
}

/**
 * 전체 회원을 조회할 수 있는 권한인지 확인합니다.
 */
function lottoCanViewAllMembers($mbLevel)
{
    return in_array(
        (int) $mbLevel,
        array(
            LOTTO_ROLE_ADMIN,
            LOTTO_ROLE_SUPER_ADMIN,
        ),
        true
    );
}

/**
 * 직원 계정을 생성할 수 있는 권한인지 확인합니다.
 */
function lottoCanCreateStaff($mbLevel)
{
    return in_array(
        (int) $mbLevel,
        array(
            LOTTO_ROLE_ADMIN,
            LOTTO_ROLE_SUPER_ADMIN,
        ),
        true
    );
}

/**
 * 핵심 관리자 설정을 수정할 수 있는 권한인지 확인합니다.
 */
function lottoCanManageAdminSettings($mbLevel)
{
    return (int) $mbLevel === LOTTO_ROLE_SUPER_ADMIN;
}

/**
 * 전체 스케줄을 조회할 수 있는 권한인지 확인합니다.
 */
function lottoCanViewAllSchedules($mbLevel)
{
    return in_array(
        (int) $mbLevel,
        array(
            LOTTO_ROLE_ADMIN,
            LOTTO_ROLE_SUPER_ADMIN,
        ),
        true
    );
}

/**
 * 직원의 직접 상급자 아이디를 반환합니다.
 */
function lottoGetParentStaffId($childMbId)
{
    $childMbId = sql_real_escape_string(trim((string) $childMbId));

    if ($childMbId === '') {
        return '';
    }

    $row = sql_fetch(
        "select parent_mb_id
           from l_staff_relation
          where child_mb_id = '{$childMbId}'
          limit 1",
        false
    );

    return isset($row['parent_mb_id'])
        ? (string) $row['parent_mb_id']
        : '';
}

/**
 * 직원의 직접 하위 직원 아이디 목록을 반환합니다.
 */
function lottoGetDirectChildStaffIds($parentMbId)
{
    $parentMbId = sql_real_escape_string(trim((string) $parentMbId));
    $staffIds = array();

    if ($parentMbId === '') {
        return $staffIds;
    }

    $result = sql_query(
        "select child_mb_id
           from l_staff_relation
          where parent_mb_id = '{$parentMbId}'
          order by lsr_id asc",
        false
    );

    if ($result === false) {
        return $staffIds;
    }

    while ($row = sql_fetch_array($result)) {
        if (!empty($row['child_mb_id'])) {
            $staffIds[] = (string) $row['child_mb_id'];
        }
    }

    return $staffIds;
}

/**
 * 로그인 직원이 회원관리에서 접근할 수 있는 직원 아이디 목록을 반환합니다.
 *
 * 직원1: 본인
 * 직원2: 본인 + 직접 하위 직원
 * 팀장: 본인 + 모든 하위 직원
 */
function lottoGetAccessibleStaffIds($mbId, $mbLevel)
{
    $mbId = trim((string) $mbId);
    $mbLevel = (int) $mbLevel;
    $staffIds = array();

    if ($mbId === '') {
        return $staffIds;
    }

    $staffIds[] = $mbId;

    if ($mbLevel === LOTTO_ROLE_STAFF2) {
        $childStaffIds = lottoGetDirectChildStaffIds($mbId);

        foreach ($childStaffIds as $childStaffId) {
            $childStaffId = trim((string) $childStaffId);

            if ($childStaffId !== '') {
                $staffIds[] = $childStaffId;
            }
        }
    } elseif ($mbLevel === LOTTO_ROLE_TEAM_LEADER) {
        $pendingStaffIds = array($mbId);
        $processedStaffIds = array();

        while (count($pendingStaffIds) > 0) {
            $parentStaffId = array_shift($pendingStaffIds);

            if (isset($processedStaffIds[$parentStaffId])) {
                continue;
            }

            $processedStaffIds[$parentStaffId] = true;

            $childStaffIds =
                lottoGetDirectChildStaffIds($parentStaffId);

            foreach ($childStaffIds as $childStaffId) {
                $childStaffId = trim((string) $childStaffId);

                if ($childStaffId === '') {
                    continue;
                }

                $staffIds[] = $childStaffId;

                if (!isset($processedStaffIds[$childStaffId])) {
                    $pendingStaffIds[] = $childStaffId;
                }
            }
        }
    }

    return array_values(
        array_unique(
            array_filter($staffIds)
        )
    );
}

/**
 * 회원의 현재 담당 직원 아이디를 반환합니다.
 */
function lottoGetAssignedStaffId($mbId)
{
    $mbId = sql_real_escape_string(trim((string) $mbId));

    if ($mbId === '') {
        return '';
    }

    $row = sql_fetch(
        "select staff_mb_id
           from l_member_assignment
          where mb_id = '{$mbId}'
          limit 1",
        false
    );

    return isset($row['staff_mb_id'])
        ? (string) $row['staff_mb_id']
        : '';
}

/**
 * 직원이 직접 담당하는 회원 아이디 목록을 반환합니다.
 */
function lottoGetDirectAssignedMemberIds($staffMbId)
{
    $staffMbId = sql_real_escape_string(trim((string) $staffMbId));
    $memberIds = array();

    if ($staffMbId === '') {
        return $memberIds;
    }

    $result = sql_query(
        "select mb_id
           from l_member_assignment
          where staff_mb_id = '{$staffMbId}'
          order by lma_id asc",
        false
    );

    if ($result === false) {
        return $memberIds;
    }

    while ($row = sql_fetch_array($result)) {
        if (!empty($row['mb_id'])) {
            $memberIds[] = (string) $row['mb_id'];
        }
    }

    return $memberIds;
}

/**
 * 현재 직원이 특정 회원을 조회할 수 있는지 확인합니다.
 */
function lottoCanViewMember($viewerMbId, $viewerLevel, $targetMbId)
{
    $viewerMbId = trim((string) $viewerMbId);
    $targetMbId = trim((string) $targetMbId);
    $viewerLevel = (int) $viewerLevel;

    if ($viewerMbId === '' || $targetMbId === '') {
        return false;
    }

    if (lottoCanViewAllMembers($viewerLevel)) {
        return true;
    }

    $assignedStaffId = lottoGetAssignedStaffId($targetMbId);

    if ($assignedStaffId === '') {
        return false;
    }

    $accessibleStaffIds = lottoGetAccessibleStaffIds(
        $viewerMbId,
        $viewerLevel
    );

    return in_array(
        $assignedStaffId,
        $accessibleStaffIds,
        true
    );
}
