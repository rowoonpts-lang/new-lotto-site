<?php
include_once("_common.php");
include_once(G5_PATH."/include/lotto_manual_distribution.lib.php");

header('Content-Type: application/json; charset=utf-8');

function lottoManualResponse($success, $error, $extra = array())
{
    $response = array(
        'success' => (bool) $success,
    );

    if ($error !== '') {
        $response['error'] = (string) $error;
    }

    foreach ($extra as $key => $value) {
        $response[$key] = $value;
    }

    echo json_encode(
        $response,
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

$loginMbId = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$loginLevel = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if (!lottoIsStaffLevel($loginLevel)) {
    lottoManualResponse(
        false,
        '접근 권한이 없습니다.'
    );
}

if (
    !isset($_SERVER['REQUEST_METHOD'])
    || $_SERVER['REQUEST_METHOD'] !== 'POST'
) {
    lottoManualResponse(
        false,
        '잘못된 요청입니다.'
    );
}

$mbId = isset($_POST['mb_id'])
    ? trim((string) $_POST['mb_id'])
    : '';

$drawNo = isset($_POST['draw_no'])
    ? (int) $_POST['draw_no']
    : 0;

$count = isset($_POST['cnt'])
    ? (int) $_POST['cnt']
    : 0;

if (
    $mbId === ''
    || $drawNo < 1
    || $count < 1
) {
    lottoManualResponse(
        false,
        '회원, 회차, 추가발송 수량을 확인해주세요.'
    );
}

if (!lottoCanViewMember(
    $loginMbId,
    $loginLevel,
    $mbId
)) {
    lottoManualResponse(
        false,
        '조회 권한이 없습니다.'
    );
}

$mbIdSql = sql_real_escape_string($mbId);

$targetMember = sql_fetch(
    "select
        mb_id,
        mb_type,
        mb_leave_date
     from g5_member
     where mb_id = '{$mbIdSql}'
     limit 1",
    false
);

if (
    !isset($targetMember['mb_id'])
    || $targetMember['mb_id'] === ''
) {
    lottoManualResponse(
        false,
        '회원을 찾을 수 없습니다.'
    );
}

if (
    isset($targetMember['mb_leave_date'])
    && trim((string) $targetMember['mb_leave_date']) !== ''
) {
    lottoManualResponse(
        false,
        '탈퇴 회원에게는 추가발송할 수 없습니다.'
    );
}

$memberType = isset($targetMember['mb_type'])
    ? trim((string) $targetMember['mb_type'])
    : '';

if ($memberType === '') {
    lottoManualResponse(
        false,
        '회원 등급을 확인할 수 없습니다.'
    );
}

$paidMemberTypes = fnGetTypePre();

if (!in_array(
    $memberType,
    $paidMemberTypes,
    true
)) {
    lottoManualResponse(
        false,
        '유료회원만 추가조합을 발송할 수 있습니다.'
    );
}

$run = sql_fetch(
    "select
        lfr_id,
        draw_no,
        status,
        candidate_count
     from l_filter_run
     where draw_no = '{$drawNo}'
     limit 1",
    false
);

if (
    !isset($run['draw_no'])
    || (int) $run['draw_no'] !== $drawNo
) {
    lottoManualResponse(
        false,
        $drawNo . '회차 필터 결과가 없습니다.'
    );
}

if (
    !isset($run['status'])
    || $run['status'] !== 'filtered'
    || !isset($run['candidate_count'])
    || (int) $run['candidate_count'] < 1
) {
    lottoManualResponse(
        false,
        $drawNo . '회차는 추가발송 가능한 상태가 아닙니다.'
    );
}

$officialResult = sql_fetch(
    "select draw_no
     from g5_lotto_result
     where draw_no = '{$drawNo}'
     limit 1",
    false
);

if (
    isset($officialResult['draw_no'])
    && (int) $officialResult['draw_no'] === $drawNo
) {
    lottoManualResponse(
        false,
        $drawNo . '회차는 이미 추첨이 완료되어 추가발송할 수 없습니다.'
    );
}

/*
 * 추가발송은 기존 배분이 존재하는 회원/회차에만 허용한다.
 * 회차 오선택이나 최초배분 우회를 막기 위한 서버 검증이다.
 */
$existing = sql_fetch(
    "select count(*) as cnt
     from l_member_combination
     where mb_id = '{$mbIdSql}'
       and draw_no = '{$drawNo}'",
    false
);

$existingCount = isset($existing['cnt'])
    ? (int) $existing['cnt']
    : 0;

if ($existingCount < 1) {
    lottoManualResponse(
        false,
        '해당 회차에 기존 배분 조합이 없습니다.'
    );
}

$createdBy = $loginMbId;

$result = lottoManualDistributionDistributeMember(
    $drawNo,
    $mbId,
    $memberType,
    $count,
    $createdBy
);

echo json_encode(
    $result,
    JSON_UNESCAPED_UNICODE
);
exit;
