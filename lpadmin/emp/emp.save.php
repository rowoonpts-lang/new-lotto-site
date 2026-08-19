<?php
include_once("_common.php");
include_once(G5_LADMIN_PATH."/head.sub.php");

$loginLevel = isset($member['mb_level'])
        ? (int) $member['mb_level']
        : 0;

if (!lottoCanCreateStaff($loginLevel)) {
        alert('직원 계정을 저장할 권한이 없습니다.');
}

$allowedStaffLevels = array(
        LOTTO_ROLE_STAFF1,
        LOTTO_ROLE_STAFF2,
        LOTTO_ROLE_TEAM_LEADER,
        LOTTO_ROLE_ADMIN,
);

$mb_no = isset($_POST['mb_no'])
        ? (int) $_POST['mb_no']
        : 0;

$mb_id = isset($_POST['mb_id'])
        ? trim((string) $_POST['mb_id'])
        : '';

$mb_password = isset($_POST['mb_password'])
        ? (string) $_POST['mb_password']
        : '';

$mb_name = isset($_POST['mb_name'])
        ? trim((string) $_POST['mb_name'])
        : '';

$mb_hp = isset($_POST['mb_hp'])
        ? preg_replace('/[^0-9]/', '', (string) $_POST['mb_hp'])
        : '';

$mb_team = isset($_POST['mb_team'])
        ? trim((string) $_POST['mb_team'])
        : '';

$requestedLevel = isset($_POST['mb_level'])
        ? (int) $_POST['mb_level']
        : 0;

$childMbIds = array();

if (isset($_POST['child_mb_ids']) && is_array($_POST['child_mb_ids'])) {
        foreach ($_POST['child_mb_ids'] as $childMbId) {
                $childMbId = trim((string) $childMbId);

                if ($childMbId !== '') {
                        $childMbIds[] = $childMbId;
                }
        }

        $childMbIds = array_values(array_unique($childMbIds));
}

if (!in_array($requestedLevel, $allowedStaffLevels, true)) {
        alert('허용되지 않은 직원 권한입니다.');
}

$allowedTeams = getTeamList();

if ($mb_team !== '' && !in_array($mb_team, $allowedTeams, true)) {
        alert('허용되지 않은 팀입니다.');
}

if ($mb_id === '') {
        alert('아이디를 입력해주세요.');
}

if ($mb_name === '') {
        alert('이름을 입력해주세요.');
}

if (!preg_match('/^01[0-9][0-9]{7,8}$/', $mb_hp)) {
        alert('핸드폰번호를 정확하게 입력해주세요.');
}

if ($mb_id === 'rwadmin') {
        alert('최고관리자 계정은 이 화면에서 수정할 수 없습니다.');
}

$mb_level = $requestedLevel;

$allowedChildLevels = array();

if ($mb_level === LOTTO_ROLE_TEAM_LEADER) {
        $allowedChildLevels = array(
                LOTTO_ROLE_STAFF1,
                LOTTO_ROLE_STAFF2,
        );
} elseif ($mb_level === LOTTO_ROLE_STAFF2) {
        $allowedChildLevels = array(
                LOTTO_ROLE_STAFF1,
        );
} else {
        $childMbIds = array();
}

$validatedChildMbIds = array();

foreach ($childMbIds as $childMbId) {
        if ($childMbId === $mb_id) {
                alert('자기 자신을 하위 직원으로 선택할 수 없습니다.');
        }

        $childMbIdSql = sql_real_escape_string($childMbId);

        $childStaff = sql_fetch(
                "select mb_id, mb_level
                   from g5_member
                  where mb_id = '{$childMbIdSql}'
                  limit 1"
        );

        if (empty($childStaff['mb_id'])) {
                alert('선택한 하위 직원 정보를 찾을 수 없습니다.');
        }

        if (!in_array((int) $childStaff['mb_level'], $allowedChildLevels, true)) {
                alert('선택할 수 없는 권한의 하위 직원이 포함되어 있습니다.');
        }

        $validatedChildMbIds[] = $childMbId;
}

$mbIdSql = sql_real_escape_string($mb_id);
$mbNameSql = sql_real_escape_string($mb_name);
$mbHpSql = sql_real_escape_string($mb_hp);
$mbTeamSql = sql_real_escape_string($mb_team);
$mbPasswordSql = sql_real_escape_string($mb_password);

if ($mb_no > 0) {
        $existingStaff = sql_fetch(
                "select mb_id, mb_level
                   from g5_member
                  where mb_no = '{$mb_no}'
                  limit 1"
        );

        if (empty($existingStaff['mb_id'])) {
                alert('수정할 직원 정보를 찾을 수 없습니다.');
        }

        if (!lottoIsStaffLevel((int) $existingStaff['mb_level'])) {
                alert('직원 계정만 수정할 수 있습니다.');
        }

        if (
                $existingStaff['mb_id'] === 'rwadmin'
                || (int) $existingStaff['mb_level'] === LOTTO_ROLE_SUPER_ADMIN
        ) {
                alert('최고관리자 계정은 이 화면에서 수정할 수 없습니다.');
        }

        $mb_id = (string) $existingStaff['mb_id'];
        $mbIdSql = sql_real_escape_string($mb_id);

        $duplicateHp = sql_fetch(
                "select mb_id
                   from g5_member
                  where mb_hp = '{$mbHpSql}'
                    and mb_no != '{$mb_no}'
                  limit 1"
        );

        if (!empty($duplicateHp['mb_id'])) {
                alert('이미 사용 중인 핸드폰번호입니다.');
        }

        $passwordSql = '';

        if ($mb_password !== '') {
                $encryptedPassword = get_encrypt_string($mb_password);
                $encryptedPasswordSql = sql_real_escape_string($encryptedPassword);
                $passwordSql = ", mb_password = '{$encryptedPasswordSql}'";
        }

        $sql = "update g5_member
                   set mb_name = '{$mbNameSql}',
                       mb_hp = '{$mbHpSql}',
                       mb_tel = '{$mbHpSql}',
                       mb_team = '{$mbTeamSql}',
                       mb_level = '{$mb_level}'
                       {$passwordSql}
                 where mb_id = '{$mbIdSql}'";

        sql_query($sql);
} else {
        if ($mb_password === '') {
                alert('패스워드를 입력해주세요.');
        }

        if (strlen($mb_id) > 20) {
                alert('아이디는 20자 이하로 입력해주세요.');
        }

        $duplicateId = sql_fetch(
                "select mb_id
                   from g5_member
                  where mb_id = '{$mbIdSql}'
                  limit 1"
        );

        if (!empty($duplicateId['mb_id'])) {
                alert('이미 사용 중인 아이디입니다.');
        }

        $duplicateHp = sql_fetch(
                "select mb_id
                   from g5_member
                  where mb_hp = '{$mbHpSql}'
                  limit 1"
        );

        if (!empty($duplicateHp['mb_id'])) {
                alert('이미 사용 중인 핸드폰번호입니다.');
        }

        $mb_code = fnNewMbCode();
        $mbCodeSql = sql_real_escape_string($mb_code);

        $encryptedPassword = get_encrypt_string($mb_password);
        $encryptedPasswordSql = sql_real_escape_string($encryptedPassword);

        $mbNick = 'nick'.date('YmdHisB');
        $mbNickSql = sql_real_escape_string($mbNick);

        $mbEmail = date('YmdHisB').'@kookminlotto.co.kr';
        $mbEmailSql = sql_real_escape_string($mbEmail);

        $remoteAddr = isset($_SERVER['REMOTE_ADDR'])
                ? (string) $_SERVER['REMOTE_ADDR']
                : '';

        $remoteAddrSql = sql_real_escape_string($remoteAddr);

        $sql = "insert into {$g5['member_table']}
                   set mb_id = '{$mbIdSql}',
                       mb_code = '{$mbCodeSql}',
                       mb_password = '{$encryptedPasswordSql}',
                       mb_name = '{$mbNameSql}',
                       mb_nick = '{$mbNickSql}',
                       mb_nick_date = '".G5_TIME_YMD."',
                       mb_email = '{$mbEmailSql}',
                       mb_tel = '{$mbHpSql}',
                       mb_hp = '{$mbHpSql}',
                       mb_team = '{$mbTeamSql}',
                       mb_datetime = '".G5_TIME_YMDHIS."',
                       mb_ip = '{$remoteAddrSql}',
                       mb_level = '{$mb_level}',
                       mb_login_ip = '{$remoteAddrSql}',
                       mb_open_date = '".G5_TIME_YMD."',
                       mb_type = '직원'";

        sql_query($sql);

        $mb_no = sql_insert_id();

        if (!$mb_no) {
                alert('직원 계정을 저장하지 못했습니다.');
        }

        setEtcInfo($mb_id);
}

if ($mb_no > 0) {
        $parentMbIdSql = sql_real_escape_string($mb_id);
        $createdBy = isset($member['mb_id'])
                ? trim((string) $member['mb_id'])
                : '';
        $createdBySql = sql_real_escape_string($createdBy);

        if (!sql_query('START TRANSACTION', false)) {
                alert('직원 관계 저장을 시작할 수 없습니다.');
        }

        if (!sql_query(
                "delete from l_staff_relation
                  where parent_mb_id = '{$parentMbIdSql}'",
                false
        )) {
                sql_query('ROLLBACK', false);
                alert('기존 하위 직원 관계를 정리하지 못했습니다.');
        }

        foreach ($validatedChildMbIds as $childMbId) {
                $childMbIdSql = sql_real_escape_string($childMbId);

                if (!sql_query(
                        "insert into l_staff_relation set
                                parent_mb_id = '{$parentMbIdSql}',
                                child_mb_id = '{$childMbIdSql}',
                                created_by = '{$createdBySql}',
                                created_at = now(),
                                updated_at = now()
                         on duplicate key update
                                parent_mb_id = values(parent_mb_id),
                                created_by = values(created_by),
                                updated_at = now()",
                        false
                )) {
                        sql_query('ROLLBACK', false);
                        alert('하위 직원 관계를 저장하지 못했습니다.');
                }
        }

        if (!sql_query('COMMIT', false)) {
                sql_query('ROLLBACK', false);
                alert('직원 관계 저장에 실패했습니다.');
        }
}
?>

<script>
$(function(){
        alert("정상적으로 저장되었습니다.");
        window.opener.location.reload();
        window.close();
});
</script>
