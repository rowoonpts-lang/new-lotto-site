<?php

include_once("_common.php");

header('Content-Type: application/json; charset=utf-8');

$loginMbId = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$loginLevel = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if (!lottoCanViewAllMembers($loginLevel)) {
    echo json_encode(array(
        'success' => false,
        'message' => '담당자 변경 권한이 없습니다.',
    ));
    exit;
}

if (
    !isset($_SERVER['REQUEST_METHOD'])
    || $_SERVER['REQUEST_METHOD'] !== 'POST'
) {
    echo json_encode(array(
        'success' => false,
        'message' => '잘못된 요청입니다.',
    ));
    exit;
}

$targetMbId = isset($_POST['mb_id'])
    ? trim((string) $_POST['mb_id'])
    : '';

$staffMbId = isset($_POST['staff_mb_id'])
    ? trim((string) $_POST['staff_mb_id'])
    : '';

if ($targetMbId === '') {
    echo json_encode(array(
        'success' => false,
        'message' => '회원 정보가 없습니다.',
    ));
    exit;
}

$targetMbIdSql = sql_real_escape_string($targetMbId);

$targetMember = sql_fetch(
    "select mb_id, mb_name, mb_level
       from g5_member
      where mb_id = '{$targetMbIdSql}'
        and mb_id != 'admin'
        and mb_level < " . LOTTO_ROLE_STAFF1 . "
      limit 1",
    false
);

if (empty($targetMember['mb_id'])) {
    echo json_encode(array(
        'success' => false,
        'message' => '배정할 회원을 찾을 수 없습니다.',
    ));
    exit;
}

if ($staffMbId === '') {
    sql_query(
        "delete from l_member_assignment
          where mb_id = '{$targetMbIdSql}'"
    );

    fnSetLog(
        $loginMbId,
        $targetMbId . '님의 담당자를 미배정으로 변경하였습니다.'
    );

    echo json_encode(array(
        'success' => true,
        'message' => '담당자를 미배정으로 변경했습니다.',
        'staff_mb_id' => '',
        'staff_name' => '-',
    ));
    exit;
}

$staffMbIdSql = sql_real_escape_string($staffMbId);

$staffMember = sql_fetch(
    "select mb_id, mb_name, mb_level
       from g5_member
      where mb_id = '{$staffMbIdSql}'
        and mb_level in (
            " . LOTTO_ROLE_STAFF1 . ",
            " . LOTTO_ROLE_STAFF2 . ",
            " . LOTTO_ROLE_TEAM_LEADER . "
        )
      limit 1",
    false
);

if (empty($staffMember['mb_id'])) {
    echo json_encode(array(
        'success' => false,
        'message' => '등록된 직원이 아닙니다.',
    ));
    exit;
}

sql_query(
    "insert into l_member_assignment (
        mb_id,
        staff_mb_id,
        assigned_by,
        assigned_at,
        updated_at
    ) values (
        '{$targetMbIdSql}',
        '{$staffMbIdSql}',
        '" . sql_real_escape_string($loginMbId) . "',
        now(),
        now()
    )
    on duplicate key update
        staff_mb_id = values(staff_mb_id),
        assigned_by = values(assigned_by),
        updated_at = now()"
);

fnSetLog(
    $loginMbId,
    $targetMbId
        . '님의 담당자를 '
        . (string) $staffMember['mb_name']
        . '('
        . $staffMbId
        . ')님으로 변경하였습니다.'
);

echo json_encode(array(
    'success' => true,
    'message' => '담당자가 변경되었습니다.',
    'staff_mb_id' => $staffMbId,
    'staff_name' => (string) $staffMember['mb_name'],
));
