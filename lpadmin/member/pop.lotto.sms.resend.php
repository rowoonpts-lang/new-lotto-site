<?php

include_once("_common.php");
include_once(G5_LADMIN_PATH."/head.sub.php");
include_once G5_PATH . "/include/lotto_sms.lib.php";

$loginMbId = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$loginLevel = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

$targetMbId = isset($_GET['mb_id'])
    ? trim((string) $_GET['mb_id'])
    : '';

$drawNo = isset($_GET['draw_no'])
    ? (int) $_GET['draw_no']
    : 0;

$rows = array();
$errorMessage = '';

if (!lottoIsStaffLevel($loginLevel)) {
    $errorMessage = '접근 권한이 없습니다.';
}

if (
    $errorMessage === ''
    && ($targetMbId === '' || $drawNo < 1)
) {
    $errorMessage = '재발송할 회원과 회차를 확인해주세요.';
}

if (
    $errorMessage === ''
    && !lottoCanViewMember(
        $loginMbId,
        $loginLevel,
        $targetMbId
    )
) {
    $errorMessage = '조회 권한이 없습니다.';
}

if ($errorMessage === '') {
    $targetMbIdSql = sql_real_escape_string($targetMbId);

    $result = sql_query(
        "select
            a.lmc_id,
            a.draw_no,
            a.mb_id,
            a.num1,
            a.num2,
            a.num3,
            a.num4,
            a.num5,
            a.num6,
            b.mb_name,
            b.mb_hp,
            b.mb_type,
            b.mb_leave_date
         from l_member_combination a
         inner join g5_member b
            on b.mb_id = a.mb_id
         where a.mb_id = '{$targetMbIdSql}'
           and a.draw_no = '{$drawNo}'
         order by a.lmc_id asc",
        false
    );

    if ($result !== false) {
        while ($row = sql_fetch_array($result)) {
            $rows[] = $row;
        }
    }

    if (empty($rows)) {
        $errorMessage = '재발송할 조합을 찾을 수 없습니다.';
    }
}

if ($errorMessage === '' && !empty($rows)) {
    $paidMemberTypes = fnGetTypePre();
    $currentMemberType = trim(
        (string) $rows[0]['mb_type']
    );

    if (!in_array(
        $currentMemberType,
        $paidMemberTypes,
        true
    )) {
        $errorMessage =
            '유료회원만 조합 문자를 재발송할 수 있습니다.';
        $rows = array();
    }
}

if ($errorMessage === '' && !empty($rows)) {
    if (trim((string) $rows[0]['mb_leave_date']) !== '') {
        $errorMessage =
            '탈퇴 회원에게는 조합 문자를 재발송할 수 없습니다.';
        $rows = array();
    }
}

$mbName = '';
$mbHp = '';
$message = '';

if (!empty($rows)) {
    $mbName = (string) $rows[0]['mb_name'];
    $mbHp = (string) $rows[0]['mb_hp'];

    $message = lottoSmsBuildCombinationMessage(
        $drawNo,
        '로또조합',
        $rows
    );
}

?>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">

            <div class="card-header">
                <h3 class="card-title">
                    로또 전체 조합 재발송 확인
                </h3>
            </div>

            <div class="card-body">

                <?php if ($errorMessage !== '') { ?>

                    <div class="alert alert-danger">
                        <?=htmlspecialchars(
                            $errorMessage,
                            ENT_QUOTES,
                            'UTF-8'
                        )?>
                    </div>

                <?php } else { ?>

                    <div class="form-group">
                        <label>회원</label>
                        <input
                            type="text"
                            class="form-control"
                            value="<?=htmlspecialchars(
                                $mbName,
                                ENT_QUOTES,
                                'UTF-8'
                            )?>"
                            readonly
                        >
                    </div>

                    <div class="form-group">
                        <label>회차</label>
                        <input
                            type="text"
                            class="form-control"
                            value="<?=(int) $drawNo?> 회차"
                            readonly
                        >
                    </div>

                    <div class="form-group">
                        <label>전체 조합 수</label>
                        <input
                            type="text"
                            class="form-control"
                            value="<?=count($rows)?> 조합"
                            readonly
                        >
                    </div>

                    <div class="form-group">
                        <label>휴대폰번호</label>
                        <input
                            type="text"
                            class="form-control"
                            value="<?=htmlspecialchars(
                                $mbHp,
                                ENT_QUOTES,
                                'UTF-8'
                            )?>"
                            readonly
                        >
                    </div>

                    <div class="form-group">
                        <label>문자내용</label>

                        <textarea
                            id="sms_content"
                            class="form-control"
                            rows="16"
                        ><?=htmlspecialchars(
                            $message,
                            ENT_QUOTES,
                            'UTF-8'
                        )?></textarea>

                        <small class="form-text text-muted">
                            선택한 회차의 전체 배분번호입니다.
                            기존 배분번호는 변경하지 않습니다.
                        </small>
                    </div>

                <?php } ?>

            </div>

            <div class="card-footer">

                <?php if (
                    $errorMessage === ''
                    && !empty($rows)
                ) { ?>

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
            mb_id: <?=json_encode($targetMbId)?>,
            draw_no: <?=json_encode($drawNo)?>,
            send_type: "resend",
            sms_content: message
        },
        success: function(result){
            if(!result || result.success !== true){
                alert(
                    result && result.message
                        ? result.message
                        : "문자 재발송 전 검증에 실패했습니다."
                );
                return;
            }

            alert(
                result.message
                + "\n현재 개발환경에서는 실제 SMS를 발송하지 않습니다."
            );
        },
        error: function(){
            alert(
                "문자 재발송 전 서버 검증 중 오류가 발생했습니다."
            );
        }
    });

    return false;
}
</script>
