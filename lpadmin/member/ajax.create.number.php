<?php
include_once("_common.php");
include_once(G5_PATH."/include/lotto_manual_distribution.lib.php");

header('Content-Type: application/json; charset=utf-8');

$mbId = isset($_POST['mb_id']) ? trim((string) $_POST['mb_id']) : '';
$count = isset($_POST['cnt']) ? (int) $_POST['cnt'] : 0;

if ($mbId === '' || $count < 1) {
    echo json_encode(array(
        'success' => false,
        'error' => '회원과 추가발송 수량을 확인해주세요.',
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

$mbIdSql = sql_real_escape_string($mbId);

$targetMember = sql_fetch(
    "select mb_id, mb_type, mb_leave_date
     from g5_member
     where mb_id = '{$mbIdSql}'
     limit 1",
    false
);

if (!isset($targetMember['mb_id']) || $targetMember['mb_id'] === '') {
    echo json_encode(array(
        'success' => false,
        'error' => '회원을 찾을 수 없습니다.',
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

if (
    isset($targetMember['mb_leave_date'])
    && trim((string) $targetMember['mb_leave_date']) !== ''
) {
    echo json_encode(array(
        'success' => false,
        'error' => '탈퇴 회원에게는 추가발송할 수 없습니다.',
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

$memberType = isset($targetMember['mb_type'])
    ? trim((string) $targetMember['mb_type'])
    : '';

if ($memberType === '') {
    echo json_encode(array(
        'success' => false,
        'error' => '회원 등급을 확인할 수 없습니다.',
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

$paidMemberTypes = fnGetTypePre();

if (!in_array($memberType, $paidMemberTypes, true)) {
    echo json_encode(array(
        'success' => false,
        'error' => '유료회원만 추가조합을 발송할 수 있습니다.',
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

$run = sql_fetch(
    "select draw_no
     from l_filter_run
     where status = 'filtered'
       and candidate_count > 0
     order by draw_no desc
     limit 1",
    false
);

$drawNo = isset($run['draw_no']) ? (int) $run['draw_no'] : 0;

if ($drawNo < 1) {
    echo json_encode(array(
        'success' => false,
        'error' => '추가발송 가능한 필터 회차가 없습니다.',
    ), JSON_UNESCAPED_UNICODE);
    exit;
}

$createdBy = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$result = lottoManualDistributionDistributeMember(
    $drawNo,
    $mbId,
    $memberType,
    $count,
    $createdBy
);

echo json_encode($result, JSON_UNESCAPED_UNICODE);
exit;
