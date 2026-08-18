<?php
include_once("_common.php");
include_once(G5_LADMIN_PATH."/head.sub.php");

$batch = isset($_GET['batch'])
    ? trim((string) $_GET['batch'])
    : '';

$rows = array();

if ($batch !== '') {
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
            b.mb_hp
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
}

$drawNo = 0;
$mbName = '';
$mbHp = '';
$message = '';

if (!empty($rows)) {
    $drawNo = (int) $rows[0]['draw_no'];
    $mbName = (string) $rows[0]['mb_name'];
    $mbHp = (string) $rows[0]['mb_hp'];

    $message .= $drawNo . "회 추가조합\n";

    foreach ($rows as $index => $row) {
        $message .= ($index + 1) . ". ";
        $message .= (int) $row['num1'] . ",";
        $message .= (int) $row['num2'] . ",";
        $message .= (int) $row['num3'] . ",";
        $message .= (int) $row['num4'] . ",";
        $message .= (int) $row['num5'] . ",";
        $message .= (int) $row['num6'];

        if ($index < count($rows) - 1) {
            $message .= "\n";
        }
    }
}
?>

<section class="content">
    <div class="container-fluid">
        <div class="card card-primary">

            <div class="card-header">
                <h3 class="card-title">추가조합 문자 확인</h3>
            </div>

            <div class="card-body">

                <?php if (empty($rows)) { ?>

                    <div class="alert alert-danger">
                        추가발송 조합을 찾을 수 없습니다.
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

    /*
     * SMS 업체 API 연결 전 개발 단계.
     * 실제 문자는 발송하지 않는다.
     */
    alert(
        "문자 내용 확인 단계까지 정상 처리되었습니다.\n"
        + "현재 개발환경에서는 실제 SMS를 발송하지 않습니다."
    );

    return false;
}
</script>
