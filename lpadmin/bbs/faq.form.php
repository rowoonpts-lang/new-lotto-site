<?php

include_once("_common.php");
include_once(G5_EDITOR_LIB);

$loginLevel = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if ($loginLevel < LOTTO_ROLE_TEAM_LEADER) {
    alert(
        '팀장 이상만 접근할 수 있습니다.',
        G5_LADMIN_URL
    );
    exit;
}

$fmId = 1;

$w = isset($_GET['w'])
    ? trim((string) $_GET['w'])
    : '';

$faId = isset($_GET['fa_id'])
    ? (int) $_GET['fa_id']
    : 0;

if ($w !== '' && $w !== 'u') {
    alert(
        '올바른 방법으로 이용해 주십시오.',
        G5_LADMIN_URL.'/bbs/faq.list.php'
    );
    exit;
}

$faqMaster = sql_fetch(
    "select fm_id, fm_subject
       from {$g5['faq_master_table']}
      where fm_id = '{$fmId}'
      limit 1",
    false
);

if (empty($faqMaster['fm_id'])) {
    alert(
        'FAQ 기본 그룹을 찾을 수 없습니다.',
        G5_LADMIN_URL.'/bbs/faq.list.php'
    );
    exit;
}

$faq = array(
    'fa_id' => 0,
    'fa_subject' => '',
    'fa_content' => '',
    'fa_order' => 0,
);

if ($w === 'u') {
    $faq = sql_fetch(
        "select *
           from {$g5['faq_table']}
          where fa_id = '{$faId}'
            and fm_id = '{$fmId}'
          limit 1",
        false
    );

    if (empty($faq['fa_id'])) {
        alert(
            'FAQ 항목을 찾을 수 없습니다.',
            G5_LADMIN_URL.'/bbs/faq.list.php'
        );
        exit;
    }
}

$token = lottoBbsTokenCreate();

$subjectEditor = editor_html(
    'fa_subject',
    get_text(
        html_purifier($faq['fa_subject']),
        0
    )
);

$contentEditor = editor_html(
    'fa_content',
    get_text(
        html_purifier($faq['fa_content']),
        0
    )
);

$editorJs = get_editor_js('fa_subject');
$editorJs .= get_editor_js('fa_content');

include_once(G5_LADMIN_PATH."/head.php");

function lottoFaqFormHtml($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES
    );
}
?>

<style>
.lotto-faq-form label {
    font-size: 16px;
    font-weight: 700;
    color: #222;
}

.lotto-faq-form .form-control {
    min-height: 44px;
    font-size: 16px;
    color: #222;
}

.lotto-faq-form .form-text {
    font-size: 14px;
    color: #555;
}

.lotto-faq-form .btn {
    min-width: 90px;
    padding: 8px 16px;
    font-size: 15px;
    font-weight: 700;
}
</style>

<div class="row">
    <div class="col-12">
        <div class="card card-primary lotto-faq-form">

            <div class="card-header">
                <h3
                    class="card-title"
                    style="font-size:18px;font-weight:700;"
                >
                    <?=$w === 'u'
                        ? 'FAQ 수정'
                        : 'FAQ 등록'?>
                </h3>
            </div>

            <form
                method="post"
                action="faq.update.php"
                onsubmit="return lottoFaqSubmit(this);"
                autocomplete="off"
            >
                <input type="hidden" name="w" value="<?=lottoFaqFormHtml($w)?>">
                <input type="hidden" name="fm_id" value="<?=$fmId?>">
                <input
                    type="hidden"
                    name="fa_id"
                    value="<?=(int) $faId?>"
                >
                <input
                    type="hidden"
                    name="token"
                    value="<?=lottoFaqFormHtml($token)?>"
                >

                <div class="card-body">

                    <div class="form-group">
                        <label for="fa_order">출력순서</label>

                        <input
                            type="number"
                            class="form-control"
                            id="fa_order"
                            name="fa_order"
                            value="<?=(int) $faq['fa_order']?>"
                            style="max-width:180px;"
                        >

                        <small class="form-text">
                            숫자가 작을수록 먼저 표시됩니다.
                        </small>
                    </div>

                    <div class="form-group">
                        <label>질문</label>
                        <?=$subjectEditor?>
                    </div>

                    <div class="form-group">
                        <label>답변</label>
                        <?=$contentEditor?>
                    </div>

                </div>

                <div class="card-footer text-right">
                    <a
                        href="faq.list.php"
                        class="btn btn-secondary"
                    >
                        목록
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        저장
                    </button>
                </div>

            </form>

        </div>
    </div>
</div>

<script>
function lottoFaqSubmit(f)
{
    <?=$editorJs?>

    return true;
}
</script>

<?php
include_once(G5_LADMIN_PATH."/tail.php");
?>
