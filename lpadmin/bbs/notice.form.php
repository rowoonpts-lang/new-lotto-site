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

$w = isset($_GET['w'])
    ? trim((string) $_GET['w'])
    : '';

$wrId = isset($_GET['wr_id'])
    ? (int) $_GET['wr_id']
    : 0;

if ($w !== '' && $w !== 'u') {
    alert(
        '올바른 방법으로 이용해 주십시오.',
        G5_LADMIN_URL.'/bbs/notice.list.php'
    );
    exit;
}

$write = array(
    'wr_id' => 0,
    'wr_subject' => '',
    'wr_content' => '',
);

if ($w === 'u') {
    $writeTable = $g5['write_prefix'].'notice';

    $write = sql_fetch(
        "select *
           from {$writeTable}
          where wr_id = '{$wrId}'
            and wr_is_comment = 0
          limit 1",
        false
    );

    if (empty($write['wr_id'])) {
        alert(
            '공지사항을 찾을 수 없습니다.',
            G5_LADMIN_URL.'/bbs/notice.list.php'
        );
        exit;
    }
}

$token = lottoBbsTokenCreate();

$isDhtmlEditor = true;

$content = get_text(
    html_purifier(
        isset($write['wr_content'])
            ? $write['wr_content']
            : ''
    ),
    0
);

$editorHtml = editor_html(
    'wr_content',
    $content,
    $isDhtmlEditor
);

$editorJs = get_editor_js(
    'wr_content',
    $isDhtmlEditor
);

$editorJs .= chk_editor_js(
    'wr_content',
    $isDhtmlEditor
);

include_once(G5_LADMIN_PATH."/head.php");

function noticeFormHtml($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES
    );
}
?>

<div class="row">
    <div class="col-12">
        <div class="card card-primary">

            <div class="card-header">
                <h3 class="card-title">
                    <?=$w === 'u'
                        ? '공지사항 수정'
                        : '공지사항 등록'?>
                </h3>
            </div>

            <form
                method="post"
                action="notice.update.php"
                onsubmit="return noticeFormSubmit(this);"
                autocomplete="off"
            >
                <input type="hidden" name="w" value="<?=noticeFormHtml($w)?>">

                <input
                    type="hidden"
                    name="wr_id"
                    value="<?=(int) $wrId?>"
                >

                <input
                    type="hidden"
                    name="token"
                    value="<?=noticeFormHtml($token)?>"
                >

                <div class="card-body">

                    <div class="form-group">
                        <label for="wr_subject">제목</label>

                        <input
                            type="text"
                            class="form-control"
                            id="wr_subject"
                            name="wr_subject"
                            maxlength="255"
                            value="<?=noticeFormHtml($write['wr_subject'])?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="wr_content">내용</label>

                        <?=$editorHtml?>
                    </div>

                </div>

                <div class="card-footer text-right">
                    <a
                        href="notice.list.php"
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
function noticeFormSubmit(f)
{
    if (!f.wr_subject.value.trim()) {
        alert("제목을 입력해주세요.");
        f.wr_subject.focus();
        return false;
    }

    <?=$editorJs?>

    return true;
}
</script>

<?php
include_once(G5_LADMIN_PATH."/tail.php");
?>
