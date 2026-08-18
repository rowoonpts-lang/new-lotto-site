<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once '_common.php';
include_once G5_PATH . '/include/lotto_distribution.lib.php';

$basename = basename($_SERVER['PHP_SELF']);
$w = (int) date('w');

$todayCols = array(
    1 => 'num_mon',
    2 => 'num_tue',
    3 => 'num_wed',
    4 => 'num_thur',
    5 => 'num_fri',
    6 => 'num_sat',
);

/*
 * 일요일에는 자동 배분하지 않는다.
 */
if (!isset($todayCols[$w])) {
    $msg = '01 Process Skip - Sunday';

    echo "<script>parent.fnSetBoard("
        . json_encode($msg, JSON_UNESCAPED_UNICODE)
        . ");</script>";

    exit;
}

$todayCol = $todayCols[$w];

/*
 * 현재 배분 가능한 최신 필터 회차를 사용한다.
 * getTurn()은 개발 DB에서 설정값(cf_1/cf_2)이 없어 0을 반환하므로
 * 주간 자동배분의 회차 기준으로 사용하지 않는다.
 */
$filterRun = sql_fetch(
    "select
        lfr_id,
        draw_no,
        candidate_count
     from l_filter_run
     where status = 'filtered'
       and candidate_count > 0
     order by draw_no desc
     limit 1",
    false
);

$drawNo = isset($filterRun['draw_no'])
    ? (int) $filterRun['draw_no']
    : 0;

if ($drawNo < 1) {
    $msg = '01 Process Error - 배분 가능한 필터 회차가 없습니다.';

    echo "<script>parent.fnSetBoard("
        . json_encode($msg, JSON_UNESCAPED_UNICODE)
        . ");</script>";

    exit;
}

/*
 * 오늘 배분 대상 회원 조건.
 *
 * 이미 오늘 배분한 회원은 제외한다.
 * 주간 전체 사용량을 모두 소진한 회원도 제외한다.
 */
$sqlCommon = "
    and a.mb_id = b.mb_id
    and a.start_date <= substr(now(), 1, 10)
    and a.end_date >= substr(now(), 1, 10)
    and a.{$todayCol} > 0
    and (
        a.recent_auto_date is null
        or a.recent_auto_date != '" . date('Y-m-d') . "'
    )
    and (
        (
            coalesce(a.num_mon, 0)
            + coalesce(a.num_tue, 0)
            + coalesce(a.num_wed, 0)
            + coalesce(a.num_thur, 0)
            + coalesce(a.num_fri, 0)
            + coalesce(a.num_sat, 0)
        ) - coalesce(a.use_num, 0)
    ) > 0
";

/*
 * 한 번에 회원 한 명씩 처리한다.
 * 기존 process.01.php의 순차 실행 구조를 유지한다.
 */
$sql = "
    select
        a.*,
        b.mb_type,
        b.mb_hp
    from g5_member_etc a,
         g5_member b
    where 1 = 1
        {$sqlCommon}
    limit 1
";

$row = sql_fetch($sql, false);

if (
    !isset($row['mb_id'])
    || trim((string) $row['mb_id']) === ''
) {
    $msg = '===================== 01 Process End '
        . date('Y-m-d H:i:s')
        . ' =====================';

    sql_query(
        "update g5_config
         set
            cf_auto1_date = '" . date('Y-m-d') . "',
            cf_auto1_ing = '2'",
        false
    );

    echo "<script>parent.fnSetBoard("
        . json_encode($msg, JSON_UNESCAPED_UNICODE)
        . ");</script>";

    exit;
}

$numCnt = isset($row[$todayCol])
    ? (int) $row[$todayCol]
    : 0;

$weekNum =
    (int) $row['num_mon']
    + (int) $row['num_tue']
    + (int) $row['num_wed']
    + (int) $row['num_thur']
    + (int) $row['num_fri']
    + (int) $row['num_sat'];

$useNum = isset($row['use_num'])
    ? (int) $row['use_num']
    : 0;

$leftNum = max(0, $weekNum - $useNum);

if ($numCnt > $leftNum) {
    $numCnt = $leftNum;
}

if ($numCnt < 1) {
    $msg = '01 Process Error - 배분 수량이 0입니다. member='
        . $row['mb_id'];

    echo "<script>parent.fnSetBoard("
        . json_encode($msg, JSON_UNESCAPED_UNICODE)
        . ");</script>";

    exit;
}

/*
 * 새 필터 배분 엔진에서 실제 번호를 배분한다.
 *
 * 이 함수 내부에서:
 * - l_member_combination 저장
 * - l_distribution_cursor 이동
 * - g5_member_etc.use_num 증가
 * - recent_auto_date / datetime / recent_turn 갱신
 * - transaction commit
 *
 * 을 처리한다.
 */
$result = lottoDistributionDistributeMember(
    $drawNo,
    $row['mb_id'],
    $row['mb_type'],
    $numCnt,
    $w,
    'auto/process.01.php'
);

if (
    !isset($result['success'])
    || $result['success'] !== true
) {
    $error = isset($result['error'])
        ? $result['error']
        : '알 수 없는 배분 오류';

    $msg = '01 Process Error - '
        . $row['mb_id']
        . ' - '
        . $error;

    echo "<script>parent.fnSetBoard("
        . json_encode($msg, JSON_UNESCAPED_UNICODE)
        . ");</script>";

    /*
     * 오류 상태에서 자동 새로고침하지 않는다.
     * 같은 오류를 무한 반복하는 것을 방지한다.
     */
    exit;
}

/*
 * SMS는 기존 개발 환경에 fnSendOneshot() 구현이 없으므로
 * 함수가 실제 존재하는 환경에서만 기존 형식으로 발송한다.
 *
 * SMS 성공 여부는 현재 코드에서 검증할 수 없으므로
 * l_member_combination.sms_status는 변경하지 않는다.
 */
if (
    function_exists('fnSendOneshot')
    && !empty($row['mb_hp'])
    && !empty($result['distributed'])
) {
    global $config;

    $smsMsg = $drawNo . '회차';

    foreach ($result['distributed'] as $index => $item) {
        if (
            !isset($item['numbers'])
            || count($item['numbers']) !== 6
        ) {
            continue;
        }

        $smsMsg .= "\n"
            . ($index + 1)
            . ') '
            . implode(',', $item['numbers']);

        if (
            (($index + 1) % 5) === 0
            && count($result['distributed']) !== ($index + 1)
        ) {
            $smsMsg .= "\n";
        }
    }

    fnSendOneshot(
        $config['cf_oneshot_tel'],
        $row['mb_hp'],
        $smsMsg,
        $config['cf_oneshot_080'],
        false,
        '',
        false
    );
}

/*
 * 남아 있는 오늘 대상 회원 수 확인.
 */
$countRow = sql_fetch(
    "select count(a.mb_id) as cnt
     from g5_member_etc a,
          g5_member b
     where 1 = 1
        {$sqlCommon}",
    false
);

$leftCount = isset($countRow['cnt'])
    ? (int) $countRow['cnt']
    : 0;

$msg = 'process Ing - Draw : '
    . $drawNo
    . ' / Member : '
    . $row['mb_id']
    . ' / Distributed : '
    . (int) $result['count']
    . ' / Rank : '
    . (int) $result['start_rank_no']
    . '-'
    . (int) $result['end_rank_no']
    . ' / Left Count : '
    . $leftCount;

echo "<script>parent.fnSetBoard("
    . json_encode($msg, JSON_UNESCAPED_UNICODE)
    . ");</script>";

if ($leftCount < 1) {
    $msg = '===================== 01 Process End '
        . date('Y-m-d H:i:s')
        . ' =====================';

    sql_query(
        "update g5_config
         set
            cf_auto1_date = '" . date('Y-m-d') . "',
            cf_auto1_ing = '2'",
        false
    );

    echo "<script>parent.fnSetBoard("
        . json_encode($msg, JSON_UNESCAPED_UNICODE)
        . ");</script>";

    exit;
}

/*
 * 기존 방식과 동일하게 다음 회원을 순차 처리한다.
 */
echo "<script>
setTimeout(function () {
    location.href = './"
    . $basename
    . "';
}, 1000);
</script>";
?>
