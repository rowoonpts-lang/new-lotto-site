<?php
include_once("_common.php");
include_once(G5_PATH . "/include/lotto_distribution.lib.php");

if (
    !isset($_SERVER['REQUEST_METHOD'])
    || $_SERVER['REQUEST_METHOD'] !== 'POST'
) {
    alert('올바른 요청이 아닙니다.');
    exit;
}

if (!lottoMemberTokenCheck()) {
    alert('올바른 요청이 아닙니다. 페이지를 새로고침한 후 다시 시도해주세요.');
    exit;
}

$action = isset($_POST['action'])
    ? trim((string) $_POST['action'])
    : '';
$mbId = isset($_POST['mb_id'])
    ? trim((string) $_POST['mb_id'])
    : '';

if ($mbId === '' || !in_array($action, array('assign', 'revoke'), true)) {
    alert('요청 정보를 확인해주세요.');
    exit;
}

$loginMbId = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';
$loginLevel = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if (!lottoCanViewMember($loginMbId, $loginLevel, $mbId)) {
    alert('접근 권한이 없는 회원입니다.');
    exit;
}

$mbIdSql = sql_real_escape_string($mbId);
$target = sql_fetch(
    "select
        a.mb_id,
        a.mb_name,
        a.mb_type,
        b.use_num,
        b.recent_turn,
        b.num_mon,
        b.num_tue,
        b.num_wed,
        b.num_thur,
        b.num_fri,
        b.num_sat
     from g5_member a
     inner join g5_member_etc b on b.mb_id = a.mb_id
     where a.mb_id = '{$mbIdSql}'
     limit 1",
    false
);

if (empty($target['mb_id'])) {
    alert('회원정보를 찾을 수 없습니다.');
    exit;
}

$paidMemberTypes = fnGetTypePre();
$memberType = trim((string) $target['mb_type']);

if (!in_array($memberType, $paidMemberTypes, true)) {
    alert('유료회원만 당회차 배분을 처리할 수 있습니다.');
    exit;
}

$currentRun = sql_fetch(
    "select draw_no
       from l_filter_run
      where status = 'filtered'
        and candidate_count > 0
      order by draw_no desc
      limit 1",
    false
);
$drawNo = isset($currentRun['draw_no'])
    ? (int) $currentRun['draw_no']
    : 0;

if ($drawNo < 1) {
    alert('당회차 배분 가능한 필터 결과가 없습니다.');
    exit;
}

$returnUrl = G5_LADMIN_URL
    . '/member/pop.success.php?mb_id='
    . urlencode(base64_encode($mbId))
    . '&turn=' . $drawNo;

if ($action === 'assign') {
    $distributionQty =
        (int) $target['num_mon']
        + (int) $target['num_tue']
        + (int) $target['num_wed']
        + (int) $target['num_thur']
        + (int) $target['num_fri'];

    if ($distributionQty < 1) {
        alert('회원정보의 로또 배분 수량을 확인해주세요.', $returnUrl);
        exit;
    }

    $result = lottoDistributionDistributeWeeklyMember(
        $drawNo,
        $mbId,
        $memberType,
        $distributionQty,
        $loginMbId
    );

    if (empty($result['success'])) {
        $message = isset($result['error'])
            ? (string) $result['error']
            : '당회차 조합 부여에 실패했습니다.';
        alert($message, $returnUrl);
        exit;
    }

    if (function_exists('fnSetLog')) {
        fnSetLog(
            $loginMbId,
            $mbId . ' 회원에게 ' . $drawNo . '회차 조합 '
            . $distributionQty . '개를 수동 부여하였습니다.'
        );
    }

    alert(
        $drawNo . '회차 조합 ' . $distributionQty . '개를 부여했습니다.',
        $returnUrl
    );
    exit;
}

$transactionStarted = false;

try {
    if (sql_query('start transaction', false) === false) {
        throw new RuntimeException('회수 트랜잭션을 시작하지 못했습니다.');
    }
    $transactionStarted = true;

    $rows = sql_fetch(
        "select
            count(*) as cnt,
            sum(case when sms_status = 'sent' then 1 else 0 end) as sent_cnt
         from l_member_combination
         where draw_no = '{$drawNo}'
           and mb_id = '{$mbIdSql}'
           and distribution_type = 'weekly'",
        false
    );

    $revokeCount = isset($rows['cnt']) ? (int) $rows['cnt'] : 0;
    $sentCount = isset($rows['sent_cnt']) ? (int) $rows['sent_cnt'] : 0;

    if ($revokeCount < 1) {
        throw new RuntimeException('회수할 당회차 배분 조합이 없습니다.');
    }

    if ($sentCount > 0) {
        throw new RuntimeException('이미 발송 완료된 문자가 있어 당회차 배분을 회수할 수 없습니다.');
    }

    if (sql_query(
        "delete from l_member_combination
         where draw_no = '{$drawNo}'
           and mb_id = '{$mbIdSql}'
           and distribution_type = 'weekly'",
        false
    ) === false) {
        throw new RuntimeException('당회차 배분 조합 회수에 실패했습니다.');
    }

    if (sql_query(
        "update g5_member_etc
         set use_num = case
                when recent_turn = '{$drawNo}' then greatest(0, use_num - '{$revokeCount}')
                else use_num
             end
         where mb_id = '{$mbIdSql}'",
        false
    ) === false) {
        throw new RuntimeException('회원 배분 사용량 복구에 실패했습니다.');
    }

    if (sql_query('commit', false) === false) {
        throw new RuntimeException('회수 트랜잭션을 완료하지 못했습니다.');
    }
    $transactionStarted = false;

    if (function_exists('fnSetLog')) {
        fnSetLog(
            $loginMbId,
            $mbId . ' 회원의 ' . $drawNo . '회차 배분 조합 '
            . $revokeCount . '개를 회수하였습니다.'
        );
    }

    alert(
        $drawNo . '회차 배분 조합 ' . $revokeCount . '개를 회수했습니다.',
        $returnUrl
    );
} catch (Throwable $e) {
    if ($transactionStarted) {
        sql_query('rollback', false);
    }

    alert($e->getMessage(), $returnUrl);
}
