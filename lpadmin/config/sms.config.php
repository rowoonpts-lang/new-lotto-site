<?php
include_once("_common.php");

$login_level = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if ($login_level < LOTTO_ROLE_ADMIN) {
    alert('관리자 이상만 접근할 수 있습니다.', G5_LADMIN_URL);
    exit;
}

$sms_config = sql_fetch(
    "select *
       from l_sms_config
      where lsc_id = 1
      limit 1",
    false
);

if (empty($sms_config['lsc_id'])) {
    alert('SMS 설정 정보를 찾을 수 없습니다.', G5_LADMIN_URL);
    exit;
}

$combination_draw_row = sql_fetch(
    "select max(draw_no) as draw_no
       from l_filter_run",
    false
);

$combination_draw_no = isset($combination_draw_row['draw_no'])
    ? (int) $combination_draw_row['draw_no']
    : 0;

$winner_draw_no = $combination_draw_no;

$token = lottoConfigTokenCreate();

include_once(G5_LADMIN_PATH."/head.php");

function sms_config_html($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES);
}
?>

<style>
.sms-config-wrap {
    max-width: 1500px;
    margin: 0 auto;
}

.sms-setting-card {
    height: 100%;
}

.sms-setting-card .card-body {
    padding: 20px;
}

.sms-editor-preview {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 300px;
    gap: 24px;
    align-items: start;
}

.sms-editor textarea {
    resize: vertical;
    min-height: 95px;
}

.sms-auto-info {
    font-size: 13px;
    color: #6c757d;
    margin-top: 8px;
}

.phone-preview-wrap {
    display: flex;
    justify-content: center;
}

.phone-preview {
    width: 280px;
    border: 8px solid #2f3439;
    border-radius: 30px;
    background: #ffffff;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
}

.phone-preview-top {
    height: 28px;
    background: #2f3439;
    position: relative;
}

.phone-preview-top::after {
    content: "";
    position: absolute;
    width: 70px;
    height: 6px;
    border-radius: 10px;
    background: #666;
    left: 50%;
    top: 8px;
    transform: translateX(-50%);
}

.phone-preview-header {
    padding: 12px 15px;
    border-bottom: 1px solid #e9ecef;
    font-weight: 600;
    text-align: center;
    font-size: 14px;
}

.phone-preview-screen {
    min-height: 370px;
    padding: 18px 13px;
    background: #f5f6f8;
}

.message-bubble {
    max-width: 92%;
    background: #ffffff;
    padding: 12px 14px;
    border-radius: 5px 16px 16px 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    white-space: pre-wrap;
    word-break: break-word;
    font-size: 13px;
    line-height: 1.65;
}

.message-meta {
    font-size: 11px;
    color: #8a8f94;
    margin-top: 7px;
}

.sms-section-title {
    margin-bottom: 18px;
    font-size: 17px;
    font-weight: 600;
}

.sms-system-example {
    border: 1px dashed #ced4da;
    border-radius: 6px;
    background: #f8f9fa;
    padding: 10px 12px;
    font-size: 12px;
    color: #495057;
    line-height: 1.6;
}

.sms-save-bar {
    margin-top: 20px;
    text-align: right;
}

@media (max-width: 1200px) {
    .sms-editor-preview {
        grid-template-columns: 1fr;
    }

    .phone-preview {
        width: 100%;
        max-width: 320px;
    }
}

@media (max-width: 767px) {
    .sms-setting-card .card-body {
        padding: 15px;
    }

    .phone-preview-screen {
        min-height: 300px;
    }
}

/* SMS UI SIZE OVERRIDE START */
.sms-config-wrap {
    max-width: 1700px;
}

.sms-config-wrap h2,
.sms-config-wrap h3,
.sms-config-wrap h4,
.sms-config-wrap .card-title,
.sms-config-wrap .title {
    font-size: 28px !important;
    line-height: 1.4;
    font-weight: 700;
}

.sms-config-wrap label,
.sms-config-wrap strong,
.sms-config-wrap .control-label,
.sms-config-wrap .form-label {
    font-size: 18px !important;
    line-height: 1.5;
    font-weight: 700;
}

.sms-config-wrap input,
.sms-config-wrap select,
.sms-config-wrap textarea,
.sms-config-wrap .form-control {
    font-size: 17px !important;
    line-height: 1.6 !important;
}

.sms-config-wrap input,
.sms-config-wrap .form-control {
    min-height: 46px;
}

.sms-config-wrap textarea {
    min-height: 130px;
}

.sms-config-wrap .help-block,
.sms-config-wrap .text-muted,
.sms-config-wrap small,
.sms-config-wrap p {
    font-size: 15px !important;
    line-height: 1.7;
}

.sms-config-wrap .sms-setting-card .card-body {
    padding: 28px;
}

.sms-editor-preview {
    grid-template-columns: minmax(0, 1fr) 340px !important;
    gap: 28px !important;
}

.sms-config-wrap .sms-auto-preview,
.sms-config-wrap .sms-auto-box,
.sms-config-wrap .sms-preview-box,
.sms-config-wrap pre {
    font-size: 16px !important;
    line-height: 1.7 !important;
}

.sms-config-wrap .sms-phone,
.sms-config-wrap .sms-phone-preview,
.sms-config-wrap .phone-preview {
    width: 340px !important;
    min-height: 420px !important;
}

.sms-config-wrap .sms-phone *,
.sms-config-wrap .sms-phone-preview *,
.sms-config-wrap .phone-preview * {
    font-size: 16px !important;
    line-height: 1.65 !important;
}

.sms-config-wrap .sms-message-bubble,
.sms-config-wrap .sms-preview-bubble,
.sms-config-wrap .message-bubble {
    padding: 16px 18px !important;
    font-size: 16px !important;
    line-height: 1.7 !important;
}

.sms-config-wrap .btn,
.sms-config-wrap button {
    font-size: 16px !important;
    line-height: 1.4;
    padding: 10px 18px;
}
/* SMS UI SIZE OVERRIDE END */

</style>

<div class="sms-config-wrap">

    <form
        method="post"
        action="sms.config.update.php"
        autocomplete="off"
    >
        <input
            type="hidden"
            name="token"
            value="<?=sms_config_html($token)?>"
        >

        <div class="card card-primary mb-3">
            <div class="card-header">
                <h3 class="card-title">SMS 관리</h3>
            </div>

            <div class="card-body">

                <div class="alert alert-warning py-2 mb-3">
                    발신번호는 OShot에 등록된 번호만 사용할 수 있습니다.
                </div>

                <div class="form-group mb-0">
                    <label for="sender_phone">발신번호</label>

                    <div class="input-group">
                        <input
                            type="text"
                            class="form-control"
                            id="sender_phone"
                            name="sender_phone"
                            maxlength="20"
                            value="<?=sms_config_html($sms_config['sender_phone'])?>"
                            placeholder="예: 0212345678"
                        >

                        <div class="input-group-append">
                            <span class="input-group-text">
                                숫자 / 하이픈 입력 가능
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="row">

            <div class="col-xl-6 col-12 mb-3">
                <div class="card card-outline card-primary sms-setting-card">

                    <div class="card-header">
                        <h3 class="card-title">
                            회원 조합 발송 문구
                        </h3>
                    </div>

                    <div class="card-body">

                        <div class="sms-editor-preview">

                            <div class="sms-editor">

                                <div class="form-group">
                                    <label for="combination_header">
                                        상단 고정문구
                                    </label>

                                    <textarea
                                        class="form-control sms-preview-source"
                                        id="combination_header"
                                        name="combination_header"
                                        rows="4"
                                    ><?=sms_config_html($sms_config['combination_header'])?></textarea>
                                </div>

                                <div class="form-group">
                                    <label>자동 발송 내용</label>

                                    <div class="sms-system-example">
                                        <?=number_format($combination_draw_no)?>회 추천번호<br>
                                        1. 1, 5, 12, 20, 31, 42<br>
                                        2. 3, 8, 14, 22, 33, 41
                                    </div>

                                    <div class="sms-auto-info">
                                        회차와 추천번호는 자동 생성되며
                                        관리자가 수정할 수 없습니다.
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <label for="combination_footer">
                                        하단 고정문구
                                    </label>

                                    <textarea
                                        class="form-control sms-preview-source"
                                        id="combination_footer"
                                        name="combination_footer"
                                        rows="4"
                                    ><?=sms_config_html($sms_config['combination_footer'])?></textarea>
                                </div>

                            </div>

                            <div class="phone-preview-wrap">

                                <div class="phone-preview">
                                    <div class="phone-preview-top"></div>

                                    <div class="phone-preview-header">
                                        문자 메시지
                                    </div>

                                    <div class="phone-preview-screen">

                                        <div
                                            id="combination_preview"
                                            class="message-bubble"
                                        ></div>

                                        <div class="message-meta">
                                            발신 메시지 미리보기
                                        </div>

                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>
                </div>
            </div>

            <div class="col-xl-6 col-12 mb-3">
                <div class="card card-outline card-success sms-setting-card">

                    <div class="card-header">
                        <h3 class="card-title">
                            당첨 결과 발송 문구
                        </h3>
                    </div>

                    <div class="card-body">

                        <div class="sms-editor-preview">

                            <div class="sms-editor">

                                <div class="form-group">
                                    <label for="winner_header">
                                        상단 고정문구
                                    </label>

                                    <textarea
                                        class="form-control sms-preview-source"
                                        id="winner_header"
                                        name="winner_header"
                                        rows="4"
                                    ><?=sms_config_html($sms_config['winner_header'])?></textarea>
                                </div>

                                <div class="form-group">
                                    <label>자동 발송 내용</label>

                                    <div class="sms-system-example">
                                        <?=number_format($winner_draw_no)?>회 당첨 결과<br>
                                        최고 4등 당첨
                                    </div>

                                    <div class="sms-auto-info">
                                        회차와 당첨등수는 자동 생성되며
                                        관리자가 수정할 수 없습니다.
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <label for="winner_footer">
                                        하단 고정문구
                                    </label>

                                    <textarea
                                        class="form-control sms-preview-source"
                                        id="winner_footer"
                                        name="winner_footer"
                                        rows="4"
                                    ><?=sms_config_html($sms_config['winner_footer'])?></textarea>
                                </div>

                            </div>

                            <div class="phone-preview-wrap">

                                <div class="phone-preview">
                                    <div class="phone-preview-top"></div>

                                    <div class="phone-preview-header">
                                        문자 메시지
                                    </div>

                                    <div class="phone-preview-screen">

                                        <div
                                            id="winner_preview"
                                            class="message-bubble"
                                        ></div>

                                        <div class="message-meta">
                                            발신 메시지 미리보기
                                        </div>

                                    </div>
                                </div>

                            </div>

                        </div>

                    </div>
                </div>
            </div>

        </div>

        <div class="sms-save-bar">
            <button
                type="submit"
                class="btn btn-primary px-4"
            >
                설정 저장
            </button>
        </div>

    </form>

</div>

<script>
(function () {
    function getValue(id) {
        return document.getElementById(id).value.trim();
    }

    function createMessage(header, automaticText, footer) {
        var parts = [];

        if (header !== '') {
            parts.push(header);
        }

        parts.push(automaticText);

        if (footer !== '') {
            parts.push(footer);
        }

        return parts.join("\n\n");
    }

    function refreshPreview() {
        document.getElementById(
            'combination_preview'
        ).textContent = createMessage(
            getValue('combination_header'),
            <?=json_encode(
                $combination_draw_no . "회 추천번호\n"
                . "1. 1, 5, 12, 20, 31, 42\n"
                . "2. 3, 8, 14, 22, 33, 41",
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            )?>,
            getValue('combination_footer')
        );

        document.getElementById(
            'winner_preview'
        ).textContent = createMessage(
            getValue('winner_header'),
            <?=json_encode(
                $winner_draw_no . "회 당첨 결과\n최고 4등 당첨",
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            )?>,
            getValue('winner_footer')
        );
    }

    document.querySelectorAll(
        '.sms-preview-source'
    ).forEach(function (element) {
        element.addEventListener(
            'input',
            refreshPreview
        );
    });

    refreshPreview();
})();
</script>

<?php
include_once(G5_LADMIN_PATH."/tail.php");
?>
