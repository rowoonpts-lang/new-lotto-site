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

$qaId = isset($_GET['qa_id'])
    ? (int) $_GET['qa_id']
    : 0;

if ($qaId < 1) {
    alert(
        '문의글을 선택해 주세요.',
        G5_LADMIN_URL.'/bbs/qa.list.php'
    );
    exit;
}

$question = sql_fetch(
    "select *
       from {$g5['qa_content_table']}
      where qa_id = '{$qaId}'
        and qa_type = 0
      limit 1",
    false
);

if (empty($question['qa_id'])) {
    alert(
        '문의글을 찾을 수 없습니다.',
        G5_LADMIN_URL.'/bbs/qa.list.php'
    );
    exit;
}

$answer = sql_fetch(
    "select *
       from {$g5['qa_content_table']}
      where qa_type = 1
        and qa_parent = '{$qaId}'
      order by qa_id asc
      limit 1",
    false
);

$qaconfig = get_qa_config();

$isDhtmlEditor = false;

if (
    $config['cf_editor']
    && !empty($qaconfig['qa_use_editor'])
) {
    $isDhtmlEditor = true;
}

$answerContent = '';

if (!empty($answer['qa_id'])) {
    $answerContent = get_text(
        html_purifier($answer['qa_content']),
        0
    );
}

$editorHtml = editor_html(
    'qa_content',
    $answerContent,
    $isDhtmlEditor
);

$editorJs = get_editor_js(
    'qa_content',
    $isDhtmlEditor
);

$editorJs .= chk_editor_js(
    'qa_content',
    $isDhtmlEditor
);

$token = lottoBbsTokenCreate();

include_once(G5_LADMIN_PATH."/head.php");

function lottoQaViewHtml($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES
    );
}
?>

<style>
.lotto-qa-view {
    font-size:17px;
}

.lotto-qa-view .card-header {
    padding:12px 15px;
}

.lotto-qa-view .card-title {
    margin:0;
    font-size:20px;
    font-weight:700;
    color:#fff;
}

.lotto-qa-view .qa-info-table th {
    width:150px;
    padding:13px 15px;
    font-size:17px;
    font-weight:700;
    color:#222;
    background:#f5f6f7;
}

.lotto-qa-view .qa-info-table td {
    padding:13px 15px;
    font-size:17px;
    color:#333;
}

.lotto-qa-view .qa-question-title {
    margin-bottom:15px;
    font-size:22px;
    line-height:1.5;
    font-weight:700;
    color:#222;
}

.lotto-qa-view .qa-content {
    min-height:180px;
    padding:20px;
    border:1px solid #ddd;
    background:#fff;
    font-size:17px;
    line-height:1.8;
    color:#222;
}

.lotto-qa-view .qa-content,
.lotto-qa-view .qa-content p,
.lotto-qa-view .qa-content div,
.lotto-qa-view .qa-content span,
.lotto-qa-view .qa-content li {
    font-size:17px !important;
    line-height:1.8;
    color:#222 !important;
}

.lotto-qa-answer label {
    font-size:17px;
    font-weight:700;
    color:#222;
}

.lotto-qa-answer .form-control {
    min-height:46px;
    font-size:17px;
    color:#222;
}

.lotto-qa-answer .btn {
    min-width:100px;
    min-height:46px;
    padding:9px 17px;
    font-size:16px;
    font-weight:700;
}
</style>

<div class="row">
    <div class="col-12">

        <div class="card card-primary lotto-qa-view">
            <div class="card-header">
                <h3 class="card-title">문의 내용</h3>
            </div>

            <div class="card-body">

                <table class="table table-bordered qa-info-table">
                    <tbody>
                        <tr>
                            <th>회원</th>
                            <td>
                                <?=lottoQaViewHtml($question['mb_id'])?>
                                <?php if ($question['qa_name'] !== '') { ?>
                                    (<?=lottoQaViewHtml($question['qa_name'])?>)
                                <?php } ?>
                            </td>

                            <th>분류</th>
                            <td>
                                <?=lottoQaViewHtml($question['qa_category'])?>
                            </td>
                        </tr>

                        <tr>
                            <th>등록일</th>
                            <td>
                                <?=lottoQaViewHtml($question['qa_datetime'])?>
                            </td>

                            <th>상태</th>
                            <td>
                                <?=(int) $question['qa_status'] === 1
                                    ? '답변완료'
                                    : '답변대기'?>
                            </td>
                        </tr>

                        <tr>
                            <th>이메일</th>
                            <td>
                                <?=lottoQaViewHtml($question['qa_email'])?>
                            </td>

                            <th>연락처</th>
                            <td>
                                <?=lottoQaViewHtml($question['qa_hp'])?>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="qa-question-title">
                    <?=lottoQaViewHtml($question['qa_subject'])?>
                </div>

                <div class="qa-content">
                    <?=conv_content(
                        $question['qa_content'],
                        (int) $question['qa_html']
                    )?>
                </div>

            </div>
        </div>


        <div class="card card-primary lotto-qa-answer">

            <div class="card-header">
                <h3
                    class="card-title"
                    style="font-size:20px;font-weight:700;"
                >
                    <?=empty($answer['qa_id'])
                        ? '답변 등록'
                        : '답변 수정'?>
                </h3>
            </div>

            <form
                method="post"
                action="qa.answer.update.php"
                onsubmit="return lottoQaAnswerSubmit(this);"
                autocomplete="off"
            >
                <input
                    type="hidden"
                    name="w"
                    value="<?=empty($answer['qa_id']) ? 'a' : 'u'?>"
                >

                <input
                    type="hidden"
                    name="qa_id"
                    value="<?=(int) $qaId?>"
                >

                <input
                    type="hidden"
                    name="answer_id"
                    value="<?=isset($answer['qa_id'])
                        ? (int) $answer['qa_id']
                        : 0?>"
                >

                <input
                    type="hidden"
                    name="token"
                    value="<?=lottoQaViewHtml($token)?>"
                >

                <input
                    type="hidden"
                    name="qa_html"
                    value="<?=$isDhtmlEditor ? 1 : 0?>"
                >

                <div class="card-body">

                    <div class="form-group">
                        <label for="qa_subject">답변 제목</label>

                        <input
                            type="text"
                            class="form-control"
                            id="qa_subject"
                            name="qa_subject"
                            maxlength="255"
                            required
                            value="<?=lottoQaViewHtml(
                                !empty($answer['qa_id'])
                                    ? $answer['qa_subject']
                                    : '답변: '.$question['qa_subject']
                            )?>"
                        >
                    </div>

                    <div class="form-group">
                        <label>답변 내용</label>
                        <?=$editorHtml?>
                    </div>

                </div>

                <div class="card-footer text-right">
                    <a
                        href="qa.list.php"
                        class="btn btn-secondary"
                    >
                        목록
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        <?=empty($answer['qa_id'])
                            ? '답변 등록'
                            : '답변 수정'?>
                    </button>
                </div>

            </form>
        </div>

        <div class="lotto-qa-delete text-right">
            <form
                method="post"
                action="qa.delete.php"
                style="display:inline-block;"
                onsubmit="return confirm('이 1:1 상담을 정말 삭제하시겠습니까?\\n\\n질문과 답변, 첨부파일이 함께 삭제되며 복구할 수 없습니다.');"
            >
                <input
                    type="hidden"
                    name="qa_id"
                    value="<?=(int) $qaId?>"
                >
                <input
                    type="hidden"
                    name="token"
                    value="<?=lottoQaViewHtml($token)?>"
                >
                <button
                    type="submit"
                    class="btn btn-danger"
                    style="min-width:110px;min-height:46px;font-size:17px;font-weight:700;"
                >
                    상담 삭제
                </button>
            </form>
        </div>

    </div>
</div>

<script>
function lottoQaAnswerSubmit(f)
{
    if (!f.qa_subject.value.trim()) {
        alert("답변 제목을 입력해 주세요.");
        f.qa_subject.focus();
        return false;
    }

    <?=$editorJs?>

    return true;
}
</script>

<?php
include_once(G5_LADMIN_PATH."/tail.php");
?>
