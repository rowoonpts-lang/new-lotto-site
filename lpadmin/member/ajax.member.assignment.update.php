<?php

include_once("_common.php");

header('Content-Type: application/json; charset=utf-8');

$loginMbId = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$loginLevel = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

$canViewAll = lottoCanViewAllMembers($loginLevel);

$canHierarchyAssign = in_array(
    $loginLevel,
    array(
        LOTTO_ROLE_STAFF2,
        LOTTO_ROLE_TEAM_LEADER,
    ),
    true
);

if (!$canViewAll && !$canHierarchyAssign) {
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

if (!lottoMemberTokenCheck()) {
    echo json_encode(array(
        'success' => false,
        'message' => '올바른 요청이 아닙니다. 페이지를 새로고침한 후 다시 시도해주세요.',
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

if (
    !$canViewAll
    && !lottoCanViewMember(
        $loginMbId,
        $loginLevel,
        $targetMbId
    )
) {
    echo json_encode(array(
        'success' => false,
        'message' => '접근 권한이 없는 회원입니다.',
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

$currentAssignment = sql_fetch(
    "select a.staff_mb_id,
            s.mb_name as staff_name,
            s.mb_level as staff_level
       from l_member_assignment a
       left join g5_member s
         on s.mb_id = a.staff_mb_id
      where a.mb_id = '{$targetMbIdSql}'
      limit 1",
    false
);

$currentStaffMbId = isset($currentAssignment['staff_mb_id'])
    ? trim((string) $currentAssignment['staff_mb_id'])
    : '';

$currentStaffName = isset($currentAssignment['staff_name'])
    ? trim((string) $currentAssignment['staff_name'])
    : '';

$currentStaffLevel = isset($currentAssignment['staff_level'])
    ? (int) $currentAssignment['staff_level']
    : 0;

if ($staffMbId === '') {
    if (!$canViewAll) {
        echo json_encode(array(
            'success' => false,
            'message' => '담당자를 미배정으로 변경할 권한이 없습니다.',
        ));
        exit;
    }

    sql_query(
        "delete from l_member_assignment
          where mb_id = '{$targetMbIdSql}'"
    );

    $changeMemo = (
        ($currentStaffName !== '' ? $currentStaffName : '-')
        . ' → 미배정'
    );

    sql_query(
        "insert into l_memo set
            mb_id = '{$targetMbIdSql}',
            from_mb_id = '" . sql_real_escape_string($loginMbId) . "',
            staff_mb_id = '',
            lm_memo_type = '담당자변경',
            lm_memo = '" . sql_real_escape_string($changeMemo) . "',
            lm_misu = '',
            lm_alarm_type = '',
            lm_alarm_date = '',
            lm_datetime = now()",
        false
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

if (!$canViewAll) {
    $requestedStaffMbIdSql =
        sql_real_escape_string($staffMbId);

    $requestedStaff = sql_fetch(
        "select mb_id,
                mb_level
           from g5_member
          where mb_id = '{$requestedStaffMbIdSql}'
          limit 1",
        false
    );

    $requestedLevel = isset($requestedStaff['mb_level'])
        ? (int) $requestedStaff['mb_level']
        : 0;

    $allowedStaff = false;

    if ($loginLevel === LOTTO_ROLE_TEAM_LEADER) {
        if (
            $staffMbId === $loginMbId
            && $requestedLevel === LOTTO_ROLE_TEAM_LEADER
        ) {
            $allowedStaff = true;
        } elseif (
            in_array(
                $requestedLevel,
                array(
                    LOTTO_ROLE_STAFF1,
                    LOTTO_ROLE_STAFF2,
                ),
                true
            )
        ) {
            $allowedStaff = true;
        }
    }

    if ($loginLevel === LOTTO_ROLE_STAFF2) {
        if (
            $staffMbId === $loginMbId
            && $requestedLevel === LOTTO_ROLE_STAFF2
        ) {
            $allowedStaff = true;
        } elseif (
            $requestedLevel === LOTTO_ROLE_STAFF1
        ) {
            $allowedStaff = true;
        }
    }

    if (
        empty($requestedStaff['mb_id'])
        || !$allowedStaff
    ) {
        echo json_encode(array(
            'success' => false,
            'message' => '지정할 수 없는 담당자입니다.',
        ));
        exit;
    }
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

$newStaffName = (string) $staffMember['mb_name'];

if ($currentStaffMbId !== $staffMbId) {
    $changeMemo = (
        ($currentStaffName !== '' ? $currentStaffName : '-')
        . ' → '
        . $newStaffName
    );

    sql_query(
        "insert into l_memo set
            mb_id = '{$targetMbIdSql}',
            from_mb_id = '" . sql_real_escape_string($loginMbId) . "',
            staff_mb_id = '" . sql_real_escape_string($staffMbId) . "',
            lm_memo_type = '담당자변경',
            lm_memo = '" . sql_real_escape_string($changeMemo) . "',
            lm_misu = '',
            lm_alarm_type = '',
            lm_alarm_date = '',
            lm_datetime = now()",
        false
    );
}

fnSetLog(
    $loginMbId,
    $targetMbId
        . '님의 담당자를 '
        . $newStaffName
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
