<?php

include_once("_common.php");

header('Content-Type: application/json; charset=utf-8');

function hardDeleteResponse($success, $message, $extra = array())
{
    echo json_encode(
        array_merge(
            array(
                'success' => (bool) $success,
                'message' => (string) $message,
            ),
            $extra
        ),
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

function hardDeleteQuery($sql, $errorMessage)
{
    if (sql_query($sql, false) === false) {
        throw new RuntimeException($errorMessage);
    }
}

function hardDeleteCount($table, $condition)
{
    $row = sql_fetch(
        "select count(*) as cnt
           from {$table}
          where {$condition}",
        false
    );

    return isset($row['cnt'])
        ? (int) $row['cnt']
        : 0;
}

$loginMbId = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$loginLevel = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if (
    $loginMbId === ''
    || !lottoCanManageAdminSettings($loginLevel)
) {
    hardDeleteResponse(
        false,
        '최고관리자만 회원 완전삭제를 사용할 수 있습니다.'
    );
}

if (
    !isset($_SERVER['REQUEST_METHOD'])
    || $_SERVER['REQUEST_METHOD'] !== 'POST'
) {
    hardDeleteResponse(false, '잘못된 요청입니다.');
}

if (!lottoMemberTokenCheck()) {
    hardDeleteResponse(
        false,
        '올바른 요청이 아닙니다. 페이지를 새로고침한 후 다시 시도해주세요.'
    );
}

$action = isset($_POST['action'])
    ? trim((string) $_POST['action'])
    : '';

if (!in_array($action, array('preview', 'delete'), true)) {
    hardDeleteResponse(false, '처리 유형이 올바르지 않습니다.');
}

$checkedIds = isset($_POST['chk']) && is_array($_POST['chk'])
    ? $_POST['chk']
    : array();

$memberIds = array();

foreach ($checkedIds as $checkedId) {
    $checkedId = trim((string) $checkedId);

    if (
        $checkedId === ''
        || strlen($checkedId) > 20
    ) {
        continue;
    }

    $memberIds[$checkedId] = $checkedId;
}

$memberIds = array_values($memberIds);

if (count($memberIds) < 1) {
    hardDeleteResponse(false, '삭제할 회원이 선택되지 않았습니다.');
}

if (count($memberIds) > 100) {
    hardDeleteResponse(
        false,
        '한 번에 최대 100명까지만 완전삭제할 수 있습니다.'
    );
}

$memberIdSqlList = array();

foreach ($memberIds as $memberId) {
    $memberIdSqlList[] =
        "'" . sql_real_escape_string($memberId) . "'";
}

$memberInSql = implode(',', $memberIdSqlList);

$targetResult = sql_query(
    "select
        mb_id,
        mb_name,
        mb_level,
        mb_hp,
        mb_recommend
     from g5_member
     where mb_id in ({$memberInSql})
     order by mb_id asc",
    false
);

if ($targetResult === false) {
    hardDeleteResponse(false, '삭제 대상 회원을 조회할 수 없습니다.');
}

$targets = array();

while ($target = sql_fetch_array($targetResult)) {
    $targetId = trim((string) $target['mb_id']);
    $targetLevel = (int) $target['mb_level'];

    if (
        $targetId === ''
        || $targetId === $loginMbId
        || $targetLevel >= LOTTO_ROLE_STAFF1
    ) {
        hardDeleteResponse(
            false,
            '직원·팀장·관리자 계정은 회원 완전삭제 기능으로 삭제할 수 없습니다.'
        );
    }

    $targets[$targetId] = $target;
}

if (count($targets) !== count($memberIds)) {
    hardDeleteResponse(
        false,
        '삭제 대상 중 존재하지 않거나 삭제할 수 없는 회원이 있습니다.'
    );
}

/*
 * 게시판 글은 첨부파일/댓글/답글 구조가 별도로 연결되므로
 * 단순 DELETE 하면 게시판 데이터가 깨질 수 있습니다.
 * 작성 게시물이 있으면 자동 완전삭제를 중단합니다.
 */
$boardContentTables = array(
    'g5_write_free',
    'g5_write_gallery',
    'g5_write_notice',
    'g5_write_qa',
    'g5_qa_content',
);

$boardContentCount = 0;

foreach ($boardContentTables as $boardTable) {
    $boardContentCount += hardDeleteCount(
        $boardTable,
        "mb_id in ({$memberInSql})"
    );
}

$paymentRequestCount = hardDeleteCount(
    'l_payment_request',
    "mb_id in ({$memberInSql})"
);

$salesCount = hardDeleteCount(
    'l_sales',
    "mb_id in ({$memberInSql})"
);

$salesCancelCount = hardDeleteCount(
    'l_sales_cancel',
    "mb_id in ({$memberInSql})"
);

$smsCount = hardDeleteCount(
    'l_sms_history',
    "mb_id in ({$memberInSql})
     or sender_mb_id in ({$memberInSql})"
);

$memoCount = hardDeleteCount(
    'l_memo',
    "mb_id in ({$memberInSql})
     or from_mb_id in ({$memberInSql})
     or staff_mb_id in ({$memberInSql})"
);

$lottoCount =
    hardDeleteCount(
        'l_member_combination',
        "mb_id in ({$memberInSql})"
    )
    + hardDeleteCount(
        'l_member_distribution_order',
        "mb_id in ({$memberInSql})"
    )
    + hardDeleteCount(
        'l_member_draw',
        "mb_id in ({$memberInSql})"
    );

if ($action === 'preview') {
    $names = array();

    foreach ($targets as $target) {
        $name = trim((string) $target['mb_name']);

        $names[] = $name !== ''
            ? $name . '(' . $target['mb_id'] . ')'
            : (string) $target['mb_id'];
    }

    hardDeleteResponse(
        true,
        '완전삭제 전에 연결 데이터를 확인했습니다.',
        array(
            'member_count' => count($targets),
            'members' => $names,
            'payment_request_count' => $paymentRequestCount,
            'sales_count' => $salesCount,
            'sales_cancel_count' => $salesCancelCount,
            'sms_count' => $smsCount,
            'memo_count' => $memoCount,
            'lotto_count' => $lottoCount,
            'board_content_count' => $boardContentCount,
        )
    );
}

$confirmText = isset($_POST['confirm_text'])
    ? trim((string) $_POST['confirm_text'])
    : '';

if ($confirmText !== '완전삭제') {
    hardDeleteResponse(
        false,
        '완전삭제 확인 문구가 올바르지 않습니다.'
    );
}

if ($boardContentCount > 0) {
    hardDeleteResponse(
        false,
        '선택한 회원이 작성한 게시글 또는 QA가 '
        . number_format($boardContentCount)
        . '건 있습니다. 게시판 데이터 손상을 막기 위해 자동 완전삭제를 중단했습니다.'
    );
}

if (sql_query('START TRANSACTION', false) === false) {
    hardDeleteResponse(
        false,
        '회원 완전삭제 작업을 시작할 수 없습니다.'
    );
}

try {
    $lockedResult = sql_query(
        "select mb_id, mb_level
           from g5_member
          where mb_id in ({$memberInSql})
          for update",
        false
    );

    if ($lockedResult === false) {
        throw new RuntimeException(
            '삭제 대상 회원을 잠글 수 없습니다.'
        );
    }

    $lockedCount = 0;

    while ($locked = sql_fetch_array($lockedResult)) {
        $lockedCount++;

        if (
            (int) $locked['mb_level'] >= LOTTO_ROLE_STAFF1
            || (string) $locked['mb_id'] === $loginMbId
        ) {
            throw new RuntimeException(
                '삭제 도중 회원 권한이 변경되어 작업을 중단했습니다.'
            );
        }
    }

    if ($lockedCount !== count($memberIds)) {
        throw new RuntimeException(
            '삭제 대상 회원 정보가 변경되어 작업을 중단했습니다.'
        );
    }

    /*
     * 결제 요청 ID 확보
     */
    $paymentRequestIds = array();

    $paymentResult = sql_query(
        "select lpr_id
           from l_payment_request
          where mb_id in ({$memberInSql})
          order by lpr_id asc",
        false
    );

    if ($paymentResult === false) {
        throw new RuntimeException(
            '결제 승인요청 연결정보를 조회할 수 없습니다.'
        );
    }

    while ($paymentRow = sql_fetch_array($paymentResult)) {
        $paymentRequestIds[] = (int) $paymentRow['lpr_id'];
    }

    $paymentRequestInSql = '';

    if (count($paymentRequestIds) > 0) {
        $paymentRequestInSql = implode(
            ',',
            array_map('intval', $paymentRequestIds)
        );
    }

    /*
     * 회원과 정확히 연결된 OShot 문자 ID 확보
     */
    $oshotMsgIds = array();

    $smsResult = sql_query(
        "select distinct oshot_msg_id
           from l_sms_history
          where (
                mb_id in ({$memberInSql})
                or sender_mb_id in ({$memberInSql})
          )
            and oshot_msg_id is not null
            and oshot_msg_id > 0",
        false
    );

    if ($smsResult === false) {
        throw new RuntimeException(
            '문자 발송 연결정보를 조회할 수 없습니다.'
        );
    }

    while ($smsRow = sql_fetch_array($smsResult)) {
        $oshotMsgIds[] = (int) $smsRow['oshot_msg_id'];
    }

    $oshotMsgIds = array_values(
        array_unique(
            array_filter($oshotMsgIds)
        )
    );

    /*
     * 결제 관련 데이터
     */
    if ($paymentRequestInSql !== '') {
        hardDeleteQuery(
            "delete from l_payment_card_secret
              where lpr_id in ({$paymentRequestInSql})",
            '카드 결제 민감정보 삭제에 실패했습니다.'
        );

        hardDeleteQuery(
            "delete from l_notification
              where reference_type = 'payment_request'
                and reference_id in ({$paymentRequestInSql})",
            '결제 알림 삭제에 실패했습니다.'
        );

        hardDeleteQuery(
            "delete from l_sales_cancel
              where lpr_id in ({$paymentRequestInSql})
                 or mb_id in ({$memberInSql})",
            '매출 취소이력 삭제에 실패했습니다.'
        );

        hardDeleteQuery(
            "delete from l_sales
              where lpr_id in ({$paymentRequestInSql})
                 or mb_id in ({$memberInSql})",
            '매출내역 삭제에 실패했습니다.'
        );

        hardDeleteQuery(
            "delete from l_payment_request
              where lpr_id in ({$paymentRequestInSql})",
            '결제 승인요청 삭제에 실패했습니다.'
        );
    } else {
        hardDeleteQuery(
            "delete from l_sales_cancel
              where mb_id in ({$memberInSql})",
            '매출 취소이력 삭제에 실패했습니다.'
        );

        hardDeleteQuery(
            "delete from l_sales
              where mb_id in ({$memberInSql})",
            '매출내역 삭제에 실패했습니다.'
        );
    }

    /*
     * 문자 관련 데이터
     */
    if (count($oshotMsgIds) > 0) {
        $oshotMsgInSql = implode(
            ',',
            array_map('intval', $oshotMsgIds)
        );

        hardDeleteQuery(
            "delete from OShotMSG
              where MsgID in ({$oshotMsgInSql})",
            'OShot 문자 데이터 삭제에 실패했습니다.'
        );
    }

    hardDeleteQuery(
        "delete from l_sms_history
          where mb_id in ({$memberInSql})
             or sender_mb_id in ({$memberInSql})",
        '문자 발송내역 삭제에 실패했습니다.'
    );

    /*
     * Lotto Platform 회원 연결 데이터
     */
    hardDeleteQuery(
        "delete from l_lotto_share_link
          where mb_id in ({$memberInSql})",
        '로또 공유링크 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from l_lotto_share_link_backup_20260907
          where mb_id in ({$memberInSql})",
        '로또 공유링크 백업정보 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from l_member_combination
          where mb_id in ({$memberInSql})",
        '회원 로또 조합 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from l_member_distribution_order
          where mb_id in ({$memberInSql})",
        '회원 배분순서 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from l_member_draw
          where mb_id in ({$memberInSql})",
        '회원 당첨결과 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from l_member_assignment
          where mb_id in ({$memberInSql})
             or staff_mb_id in ({$memberInSql})",
        '회원 담당자정보 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from l_memo
          where mb_id in ({$memberInSql})
             or from_mb_id in ({$memberInSql})
             or staff_mb_id in ({$memberInSql})",
        '회원 상담내역 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from l_notification
          where mb_id in ({$memberInSql})
             or recipient_mb_id in ({$memberInSql})",
        '회원 알림내역 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from l_staff_relation
          where parent_mb_id in ({$memberInSql})
             or child_mb_id in ({$memberInSql})",
        '직원 관계정보 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from l_super_admin_ip_log
          where mb_id in ({$memberInSql})",
        '관리자 IP 로그 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from l_log
          where mb_id in ({$memberInSql})",
        '회원 작업로그 삭제에 실패했습니다.'
    );

    /*
     * 그누보드 회원 연결 데이터
     */
    hardDeleteQuery(
        "delete from g5_auth
          where mb_id in ({$memberInSql})",
        '회원 권한정보 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from g5_autosave
          where mb_id in ({$memberInSql})",
        '자동저장 데이터 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from g5_board_good
          where mb_id in ({$memberInSql})",
        '게시물 추천정보 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from g5_board_new
          where mb_id in ({$memberInSql})",
        '새글 연결정보 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from g5_cert_history
          where mb_id in ({$memberInSql})",
        '본인인증 이력 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from g5_group_member
          where mb_id in ({$memberInSql})",
        '그룹회원정보 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from g5_login
          where mb_id in ({$memberInSql})",
        '로그인정보 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from g5_member_auto_login
          where mb_id in ({$memberInSql})",
        '자동로그인정보 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from g5_member_cert_history
          where mb_id in ({$memberInSql})",
        '회원 인증이력 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from g5_member_social_profiles
          where mb_id in ({$memberInSql})",
        '소셜로그인정보 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from g5_memo
          where me_recv_mb_id in ({$memberInSql})
             or me_send_mb_id in ({$memberInSql})",
        '쪽지정보 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from g5_point
          where mb_id in ({$memberInSql})",
        '포인트정보 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from g5_poll_etc
          where mb_id in ({$memberInSql})",
        '투표 부가정보 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from g5_scrap
          where mb_id in ({$memberInSql})",
        '스크랩정보 삭제에 실패했습니다.'
    );

    /*
     * 다른 회원이 삭제 대상 아이디를 추천인으로 가지고 있으면
     * 존재하지 않는 회원 아이디가 남지 않도록 연결만 제거합니다.
     */
    hardDeleteQuery(
        "update g5_member
            set mb_recommend = ''
          where mb_recommend in ({$memberInSql})",
        '추천인 연결정보 정리에 실패했습니다.'
    );

    /*
     * 회원 부가정보와 회원 본체는 마지막에 삭제합니다.
     */
    hardDeleteQuery(
        "delete from g5_member_etc
          where mb_id in ({$memberInSql})",
        '회원 부가정보 삭제에 실패했습니다.'
    );

    hardDeleteQuery(
        "delete from g5_member
          where mb_id in ({$memberInSql})
            and mb_level < " . LOTTO_ROLE_STAFF1,
        '회원 기본정보 삭제에 실패했습니다.'
    );

    if (sql_query('COMMIT', false) === false) {
        throw new RuntimeException(
            '회원 완전삭제 저장을 완료하지 못했습니다.'
        );
    }
} catch (Throwable $e) {
    sql_query('ROLLBACK', false);

    hardDeleteResponse(
        false,
        $e->getMessage()
    );
}

if (function_exists('fnSetLog')) {
    fnSetLog(
        $loginMbId,
        $loginMbId
        . '님께서 최고관리자 권한으로 회원 '
        . count($memberIds)
        . '명을 완전삭제하였습니다.'
    );
}

hardDeleteResponse(
    true,
    number_format(count($memberIds))
    . '명의 회원과 연결 데이터를 완전삭제했습니다.'
);
