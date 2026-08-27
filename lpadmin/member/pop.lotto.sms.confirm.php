<?php
include_once("_common.php");
include_once(G5_LADMIN_PATH."/head.sub.php");
include_once G5_PATH . "/include/lotto_sms.lib.php";

$loginMbId = isset($member['mb_id']) ? trim((string) $member['mb_id']) : '';
$loginLevel = isset($member['mb_level']) ? (int) $member['mb_level'] : 0;

$batch = isset($_GET['batch'])
    ? trim((string) $_GET['batch'])
    : '';

$rows = array();
$errorMessage = '';

if (!lottoIsStaffLevel($loginLevel)) {
    $errorMessage = '접근 권한이 없습니다.';
}

if ($errorMessage === '' && $batch === '') {
    $errorMessage = '추가발송할 조합 정보가 없습니다.';
}

if ($errorMessage === '') {
    $batchSql = sql_real_escape_string($batch);

    $result = sql_query(
        "select
            a.draw_no,
            a.mb_id,
            a.num1,
            a.num2,
            a.num3,
            a.num4,
            a.num5,
            a.num6,
            a.distribution_seq,
            b.mb_name,
            b.mb_hp,
            b.mb_type,
            b.mb_leave_date
         from l_member_combination a
         inner join g5_member b
            on b.mb_id = a.mb_id
         where a.distribution_batch = '{$batchSql}'
           and a.distribution_type = 'manual'
         order by a.distribution_seq asc, a.lmc_id asc",
        false
    );

    if ($result !== false) {
        while ($row = sql_fetch_array($result)) {
            $rows[] = $row;
        }
    }

    if (empty($rows)) {
        $errorMessage = '추가발송할 조합을 찾을 수 없습니다.';
    }
}

if ($errorMessage === '' && !empty($rows)) {
    $targetMbId = trim((string) $rows[0]['mb_id']);

    if (!lottoCanViewMember($loginMbId, $loginLevel, $targetMbId)) {
        $errorMessage = '조회 권한이 없습니다.';
        $rows = array();
    }
}

if ($errorMessage === '' && !empty($rows)) {
    $paidMemberTypes = fnGetTypePre();
    $currentMemberType = trim((string) $rows[0]['mb_type']);

    if (!in_array($currentMemberType, $paidMemberTypes, true)) {
        $errorMessage = '유료회원만 추가조합 문자를 발송할 수 있습니다.';
        $rows = array();
    }
}

if ($errorMessage === '' && !empty($rows)) {
    if (trim((string) $rows[0]['mb_leave_date']) !== '') {
        $errorMessage = '탈퇴 회원에게는 추가조합 문자를 발송할 수 없습니다.';
        $rows = array();
    }
}

$drawNo = 0;
$mbName = '';
$mbHp = '';
$message = '';

if (!empty($rows)) {
    $drawNo = (int) $rows[0]['draw_no'];
    $mbName = (string) $rows[0]['mb_name'];
    $mbHp = (string) $rows[0]['mb_hp'];

    $message = lottoSmsBuildCombinationMessage($drawNo, '추천번호', $rows);
}
?>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">

            <div class="card-header">
                <h3 class="card-title">추가조합 문자 확인</h3>
            </div>

            <div class="card-body">

                <?php if ($errorMessage !== '') { ?>

                    <div class="alert alert-danger">
                        <?=htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8')?>
                    </div>

                <?php } else { ?>

                    <div class="form-group">
                        <label>회원</label>
                        <input
                            type="text"
                            class="form-control"
                            value="<?=htmlspecialchars($mbName, ENT_QUOTES, 'UTF-8')?>"
                            readonly
                        >
                    </div>

                    <div class="form-group">
                        <label>휴대폰번호</label>
                        <input
                            type="text"
                            class="form-control"
                            value="<?=htmlspecialchars($mbHp, ENT_QUOTES, 'UTF-8')?>"
                            readonly
                        >
                    </div>

                    <div class="form-group">
                        <label>문자내용</label>

                        <textarea
                            id="sms_content"
                            class="form-control"
                            rows="16"
                        ><?=htmlspecialchars($message, ENT_QUOTES, 'UTF-8')?></textarea>

                        <small class="form-text text-muted">
                            발송 전에 문자 내용을 직접 수정할 수 있습니다.
                        </small>
                    </div>

                <?php } ?>

            </div>

            <div class="card-footer">

                <?php if (!empty($rows)) { ?>

                    <button
                        type="button"
                        class="btn btn-primary"
                        onClick="fnSmsSend();"
                    >
                        발송
                    </button>

                <?php } ?>

                <button
                    type="button"
                    class="btn btn-secondary"
                    onClick="window.close();"
                >
                    닫기
                </button>

            </div>

        </div>
    </div>
</section>

<script>
function fnSmsSend(){
    var message = $("#sms_content").val();

    if($.trim(message) === ""){
        alert("문자 내용을 입력해주세요.");
        $("#sms_content").focus();
        return false;
    }

    $.ajax({
        url: "ajax.lotto.sms.prepare.php",
        type: "POST",
        dataType: "json",
        data: {
            batch: <?=json_encode($batch)?>,
            send_type: "manual",
            sms_content: message
        },
        success: function(result){
            if(!result || result.success !== true){
                alert(result && result.message ? result.message : "문자 발송에 실패했습니다.");
                return;
            }

            alert(result.message);
        },
        error: function(){
            alert("문자 발송 처리 중 오류가 발생했습니다.");
        }
    });

    return false;
}
</script>
