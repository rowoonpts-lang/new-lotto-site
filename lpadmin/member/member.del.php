<?php
include_once("_common.php");

$loginLevel = isset($member['mb_level'])
        ? (int) $member['mb_level']
        : 0;

if ($loginLevel < LOTTO_ROLE_ADMIN) {
        alert('접근 권한이 없습니다.', '../');
}

$encodedMbId = isset($_GET['mb_id'])
        ? trim((string) $_GET['mb_id'])
        : '';

if ($encodedMbId === '') {
        alert('삭제할 직원 정보가 없습니다.');
}

$mb_id = base64_decode($encodedMbId, true);

if ($mb_id === false || $mb_id === '') {
        alert('잘못된 직원 정보입니다.');
}

$mb_id = (string) $mb_id;

if ($mb_id === 'rwadmin') {
        alert('최고관리자 계정은 삭제할 수 없습니다.');
}

$mbIdSql = sql_real_escape_string($mb_id);

$targetMember = sql_fetch(
        "select mb_id, mb_level, mb_type
           from g5_member
          where mb_id = '{$mbIdSql}'
          limit 1"
);

if (empty($targetMember['mb_id'])) {
        alert('삭제할 직원 정보를 찾을 수 없습니다.');
}

$targetLevel = (int) $targetMember['mb_level'];

if (
        $targetLevel === LOTTO_ROLE_SUPER_ADMIN
        || !lottoIsStaffLevel($targetLevel)
) {
        alert('삭제할 수 없는 계정입니다.');
}

$sql = "delete from g5_member
         where mb_id = '{$mbIdSql}'";
sql_query($sql);

$sql = "delete from g5_member_etc
         where mb_id = '{$mbIdSql}'";
sql_query($sql);

$loginMbId = isset($member['mb_id'])
        ? (string) $member['mb_id']
        : '';

fnSetLog(
        $loginMbId,
        $loginMbId.'님께서 '.$mb_id.' 직원을 삭제하셨습니다.'
);

alert('정상적으로 삭제되었습니다.');
?>
