<?php
include_once("_common.php");
include_once(G5_LADMIN_PATH."/head.sub.php");

$loginLevel = isset($member['mb_level'])
        ? (int) $member['mb_level']
        : 0;

if (!lottoCanCreateStaff($loginLevel)) {
        alert('직원 계정 관리 권한이 없습니다.');
}

$encodedMbId = isset($_GET['mb_id'])
        ? trim((string) $_GET['mb_id'])
        : '';

$mb_id = $encodedMbId !== ''
        ? base64_decode($encodedMbId, true)
        : '';

if ($mb_id === false) {
        alert('잘못된 직원 정보입니다.');
}

$mb_id = (string) $mb_id;
$mbIdSql = sql_real_escape_string($mb_id);

$row = array();

if ($mb_id !== '') {
        $sql = "select *
                  from g5_member
                 where mb_id = '{$mbIdSql}'
                 limit 1";

        $row = sql_fetch($sql);

        if (empty($row['mb_id'])) {
                alert('직원 정보를 찾을 수 없습니다.');
        }

        if (
                $row['mb_id'] === 'rwadmin'
                || (int) $row['mb_level'] === LOTTO_ROLE_SUPER_ADMIN
        ) {
                alert('최고관리자 계정은 이 화면에서 수정할 수 없습니다.');
        }

        if (!lottoIsStaffLevel((int) $row['mb_level'])) {
                alert('직원 계정만 수정할 수 있습니다.');
        }
}

$isEdit = $mb_id !== '';

$roleOptions = array(
        LOTTO_ROLE_STAFF1 => '직원1',
        LOTTO_ROLE_STAFF2 => '직원2',
        LOTTO_ROLE_TEAM_LEADER => '팀장',
        LOTTO_ROLE_ADMIN => '관리자',
);

$currentLevel = isset($row['mb_level'])
        ? (int) $row['mb_level']
        : LOTTO_ROLE_STAFF1;
?>

<section class="content">
        <div class="container-fluid">
                <div class="row">
                        <div class="col-md-12 col-12">
                                <div class="card card-primary">
                                        <div class="card-header">
                                                <h3 class="card-title">
                                                        <?=$isEdit ? '직원 수정' : '직원 등록'?>
                                                </h3>
                                        </div>

                                        <form
                                                name="frm"
                                                id="frm"
                                                role="form"
                                                method="post"
                                                autocomplete="off"
                                                action="emp.save.php"
                                                onsubmit="return fnSubmit();"
                                        >
                                                <input
                                                        type="hidden"
                                                        name="mb_no"
                                                        value="<?=isset($row['mb_no']) ? (int) $row['mb_no'] : 0?>"
                                                >

                                                <div class="card-body">
                                                        <div class="form-group">
                                                                <label for="mb_id">아이디</label>
                                                                <input
                                                                        type="text"
                                                                        class="form-control"
                                                                        id="mb_id"
                                                                        name="mb_id"
                                                                        maxlength="20"
                                                                        value="<?=htmlspecialchars((string) ($row['mb_id'] ?? ''), ENT_QUOTES)?>"
                                                                        <?=$isEdit ? 'readonly' : ''?>
                                                                        required
                                                                >
                                                        </div>

                                                        <div class="form-group">
                                                                <label for="mb_password">패스워드</label>
                                                                <input
                                                                        type="text"
                                                                        class="form-control"
                                                                        id="mb_password"
                                                                        name="mb_password"
                                                                        value=""
                                                                        <?=$isEdit ? '' : 'required'?>
                                                                        placeholder="<?=$isEdit ? '변경할 때만 입력' : ''?>"
                                                                >
                                                        </div>

                                                        <div class="form-group">
                                                                <label for="mb_name">이름</label>
                                                                <input
                                                                        type="text"
                                                                        class="form-control"
                                                                        id="mb_name"
                                                                        name="mb_name"
                                                                        value="<?=htmlspecialchars((string) ($row['mb_name'] ?? ''), ENT_QUOTES)?>"
                                                                        required
                                                                >
                                                        </div>

                                                        <div class="form-group">
                                                                <label for="mb_hp">핸드폰번호</label>
                                                                <input
                                                                        type="text"
                                                                        class="form-control"
                                                                        id="mb_hp"
                                                                        name="mb_hp"
                                                                        maxlength="20"
                                                                        value="<?=htmlspecialchars((string) ($row['mb_hp'] ?? ''), ENT_QUOTES)?>"
                                                                        placeholder="01012345678"
                                                                        required
                                                                >
                                                        </div>

                                                        <div class="form-group">
                                                                <label for="mb_team">팀</label>
                                                                <select
                                                                        class="form-control"
                                                                        id="mb_team"
                                                                        name="mb_team"
                                                                >
                                                                        <option value="">미지정</option>
                                                                        <?php foreach (getTeamList() as $teamName) { ?>
                                                                        <option
                                                                                value="<?=htmlspecialchars($teamName, ENT_QUOTES)?>"
                                                                                <?=((string) ($row['mb_team'] ?? '') === $teamName) ? 'selected' : ''?>
                                                                        >
                                                                                <?=htmlspecialchars($teamName, ENT_QUOTES)?>
                                                                        </option>
                                                                        <?php } ?>
                                                                </select>
                                                                </div>

                                                        <div class="form-group">
                                                                <label for="mb_level">직원 권한</label>
                                                                <select
                                                                        class="form-control"
                                                                        id="mb_level"
                                                                        name="mb_level"
                                                                >
                                                                        <?php foreach ($roleOptions as $level => $label) { ?>
                                                                        <option
                                                                                value="<?=$level?>"
                                                                                <?=$currentLevel === $level ? 'selected' : ''?>
                                                                        >
                                                                                <?=$label?>
                                                                        </option>
                                                                        <?php } ?>
                                                                </select>
                                                        </div>
                                                </div>

                                                <div class="card-footer">
                                                        <button type="submit" class="btn btn-primary">
                                                                저장
                                                        </button>
                                                        <button
                                                                type="button"
                                                                class="btn btn-secondary"
                                                                onclick="window.close();"
                                                        >
                                                                취소
                                                        </button>
                                                </div>
                                        </form>
                                </div>
                        </div>
                </div>
        </div>
</section>

<script>
function fnSubmit()
{
        var mbId = $.trim($("#mb_id").val());
        var password = $("#mb_password").val();
        var mbName = $.trim($("#mb_name").val());
        var mbHp = $("#mb_hp").val().replace(/[^0-9]/g, '');

        $("#mb_id").val(mbId);
        $("#mb_name").val(mbName);
        $("#mb_hp").val(mbHp);

        if (mbId === '') {
                alert('아이디를 입력해주세요.');
                $("#mb_id").focus();
                return false;
        }

        if (!<?=$isEdit ? 'true' : 'false'?> && password === '') {
                alert('패스워드를 입력해주세요.');
                $("#mb_password").focus();
                return false;
        }

        if (mbName === '') {
                alert('이름을 입력해주세요.');
                $("#mb_name").focus();
                return false;
        }

        if (!/^01[0-9][0-9]{7,8}$/.test(mbHp)) {
                alert('핸드폰번호를 정확하게 입력해주세요.');
                $("#mb_hp").focus();
                return false;
        }

        return true;
}
</script>
