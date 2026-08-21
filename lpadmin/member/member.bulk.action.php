<?php

include_once("_common.php");

header('Content-Type: application/json; charset=utf-8');

$loginMbId = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$loginLevel = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

function bulkResponse($success, $message)
{
    echo json_encode(array(
        'success' => (bool) $success,
        'message' => (string) $message,
    ));
    exit;
}

if ($loginLevel < LOTTO_ROLE_ADMIN) {
    bulkResponse(false, '접근 권한이 없습니다.');
}

if (
    !isset($_SERVER['REQUEST_METHOD'])
    || $_SERVER['REQUEST_METHOD'] !== 'POST'
) {
    bulkResponse(false, '잘못된 요청입니다.');
}

if (!lottoMemberTokenCheck()) {
    bulkResponse(
        false,
        '올바른 요청이 아닙니다. 페이지를 새로고침한 후 다시 시도해주세요.'
    );
}

$action = isset($_POST['action'])
    ? trim((string) $_POST['action'])
    : '';

$checkedIds = isset($_POST['chk']) && is_array($_POST['chk'])
    ? $_POST['chk']
    : array();

$memberIds = array();

foreach ($checkedIds as $checkedId) {
    $checkedId = trim((string) $checkedId);

    if ($checkedId === '') {
        continue;
    }

    $memberIds[$checkedId] = $checkedId;
}

$memberIds = array_values($memberIds);

if (count($memberIds) < 1) {
    bulkResponse(false, '선택된 회원이 없습니다.');
}

if ($action === 'assign') {
    $staffMbId = isset($_POST['staff_mb_id'])
        ? trim((string) $_POST['staff_mb_id'])
        : '';

    if ($staffMbId === '') {
        bulkResponse(false, '담당자를 선택해주세요.');
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
        bulkResponse(false, '등록된 담당자가 아닙니다.');
    }

    $assignedCount = 0;

    foreach ($memberIds as $targetMbId) {
        $targetMbIdSql = sql_real_escape_string($targetMbId);

        $targetMember = sql_fetch(
            "select mb_id, mb_name, mb_level
               from g5_member
              where mb_id = '{$targetMbIdSql}'
                and mb_id != 'admin'
                and mb_leave_date = ''
                and mb_level < " . LOTTO_ROLE_STAFF1 . "
              limit 1",
            false
        );

        if (empty($targetMember['mb_id'])) {
            continue;
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

        $assignedCount++;
    }

    fnSetLog(
        $loginMbId,
        $loginMbId
            . '님께서 '
            . $assignedCount
            . '명의 회원 담당자를 '
            . (string) $staffMember['mb_name']
            . '('
            . $staffMbId
            . ')님으로 일괄 지정하였습니다.'
    );

    bulkResponse(
        true,
        number_format($assignedCount) . '명의 담당자를 지정했습니다.'
    );
}

if ($action === 'leave') {
    $leaveDate = date('Ymd', G5_SERVER_TIME);
    $leftCount = 0;

    foreach ($memberIds as $targetMbId) {
        $targetMbIdSql = sql_real_escape_string($targetMbId);

        $targetMember = sql_fetch(
            "select *
               from g5_member
              where mb_id = '{$targetMbIdSql}'
                and mb_id != 'admin'
                and mb_leave_date = ''
                and mb_level < " . LOTTO_ROLE_STAFF1 . "
              limit 1",
            false
        );

        if (empty($targetMember['mb_id'])) {
            continue;
        }

        $oldMemo = isset($targetMember['mb_memo'])
            ? (string) $targetMember['mb_memo']
            : '';

        $newMemo = $leaveDate
            . " 관리자 일괄탈퇴 처리\n"
            . $oldMemo;

        $newMemoSql = sql_real_escape_string($newMemo);

        sql_query(
            "update g5_member
                set mb_leave_date = '{$leaveDate}',
                    mb_memo = '{$newMemoSql}'
              where mb_id = '{$targetMbIdSql}'"
        );

        /*
         * 탈퇴 후에도 기존 담당자 이력을 확인할 수 있도록
         * l_member_assignment 정보는 유지합니다.
         */

        /*
         * 탈퇴 취소가 가능하도록 계정 관련 데이터는 삭제하지 않고
         * mb_leave_date를 이용한 소프트 탈퇴 상태만 유지합니다.
         */
$leftCount++;
    }

    fnSetLog(
        $loginMbId,
        $loginMbId
            . '님께서 '
            . $leftCount
            . '명의 회원을 일괄 탈퇴 처리하였습니다.'
    );

    bulkResponse(
        true,
        number_format($leftCount) . '명의 회원을 탈퇴 처리했습니다.'
    );
}

bulkResponse(false, '지원하지 않는 작업입니다.');
