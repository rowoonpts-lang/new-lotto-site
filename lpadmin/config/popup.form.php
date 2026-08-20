<?php
include_once("_common.php");
require_once G5_EDITOR_LIB;

$login_level = isset($member['mb_level']) ? (int) $member['mb_level'] : 0;

if ($login_level < LOTTO_ROLE_ADMIN) {
    alert('관리자 이상만 접근할 수 있습니다.', G5_LADMIN_URL);
    exit;
}

$w = isset($_GET['w']) ? trim((string) $_GET['w']) : '';
$nw_id = isset($_GET['nw_id']) ? (int) $_GET['nw_id'] : 0;

$popup = array(
    'nw_id' => 0,
    'nw_begin_time' => date('Y-m-d 00:00:00', G5_SERVER_TIME),
    'nw_end_time' => date('Y-m-d 23:59:59', G5_SERVER_TIME + (86400 * 7)),
    'nw_disable_hours' => 24,
    'nw_left' => 10,
    'nw_top' => 10,
    'nw_width' => 450,
    'nw_height' => 500,
    'nw_subject' => '',
    'nw_content' => ''
);

if ($w === 'u') {
    if ($nw_id < 1) {
        alert('잘못된 팝업 번호입니다.', 'popup.list.php');
        exit;
    }

    $popup = sql_fetch("
        select *
          from {$g5['new_win_table']}
         where nw_id = {$nw_id}
           and nw_division in ('comm', 'both')
           and nw_device in ('pc', 'both')
         limit 1
    ", false);

    if (empty($popup['nw_id'])) {
        alert('등록된 팝업을 찾을 수 없습니다.', 'popup.list.php');
        exit;
    }
} else {
    $w = '';
}

$token = lottoConfigTokenCreate();

include_once(G5_LADMIN_PATH."/head.php");

function popup_form_html($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES);
}
?>

<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <?=$w === 'u' ? '중요공지 팝업 수정' : '중요공지 팝업 등록'?>
                </h3>
            </div>

            <form
                method="post"
                action="popup.update.php"
                onsubmit="return popup_form_check(this);"
            >
                <input type="hidden" name="w" value="<?=popup_form_html($w)?>">
                <input type="hidden" name="nw_id" value="<?=(int) $nw_id?>">
                <input type="hidden" name="token" value="<?=popup_form_html($token)?>">

                <div class="card-body">
                    <div class="form-group">
                        <label for="nw_subject">팝업 제목</label>
                        <input
                            type="text"
                            class="form-control"
                            id="nw_subject"
                            name="nw_subject"
                            maxlength="255"
                            value="<?=popup_form_html($popup['nw_subject'])?>"
                            required
                        >
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nw_begin_time">시작일시</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="nw_begin_time"
                                    name="nw_begin_time"
                                    maxlength="19"
                                    value="<?=popup_form_html($popup['nw_begin_time'])?>"
                                    placeholder="YYYY-MM-DD HH:MM:SS"
                                    required
                                >
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nw_end_time">종료일시</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    id="nw_end_time"
                                    name="nw_end_time"
                                    maxlength="19"
                                    value="<?=popup_form_html($popup['nw_end_time'])?>"
                                    placeholder="YYYY-MM-DD HH:MM:SS"
                                    required
                                >
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nw_disable_hours">다시 보지 않기</label>
                        <div class="input-group" style="max-width:220px;">
                            <input
                                type="number"
                                class="form-control"
                                id="nw_disable_hours"
                                name="nw_disable_hours"
                                min="0"
                                value="<?=(int) $popup['nw_disable_hours']?>"
                                required
                            >
                            <div class="input-group-append">
                                <span class="input-group-text">시간</span>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">팝업 위치 및 크기</h5>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="nw_left">왼쪽 위치</label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        class="form-control"
                                        id="nw_left"
                                        name="nw_left"
                                        min="0"
                                        value="<?=(int) $popup['nw_left']?>"
                                        required
                                    >
                                    <div class="input-group-append">
                                        <span class="input-group-text">px</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="nw_top">상단 위치</label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        class="form-control"
                                        id="nw_top"
                                        name="nw_top"
                                        min="0"
                                        value="<?=(int) $popup['nw_top']?>"
                                        required
                                    >
                                    <div class="input-group-append">
                                        <span class="input-group-text">px</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="nw_width">가로 크기</label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        class="form-control"
                                        id="nw_width"
                                        name="nw_width"
                                        min="1"
                                        value="<?=(int) $popup['nw_width']?>"
                                        required
                                    >
                                    <div class="input-group-append">
                                        <span class="input-group-text">px</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="nw_height">세로 크기</label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        class="form-control"
                                        id="nw_height"
                                        name="nw_height"
                                        min="1"
                                        value="<?=(int) $popup['nw_height']?>"
                                        required
                                    >
                                    <div class="input-group-append">
                                        <span class="input-group-text">px</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="form-group">
                        <label>팝업 내용</label>
                        <?=editor_html(
                            'nw_content',
                            get_text(html_purifier($popup['nw_content']), 0)
                        )?>
                    </div>
                </div>

                <div class="card-footer text-right">
                    <a href="popup.list.php" class="btn btn-secondary">
                        목록
                    </a>

                    <button type="submit" class="btn btn-primary">
                        저장
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function popup_form_check(f)
{
    <?=get_editor_js('nw_content')?>

    if (!f.nw_subject.value.trim()) {
        alert('팝업 제목을 입력하세요.');
        f.nw_subject.focus();
        return false;
    }

    return true;
}
</script>

<?php
include_once(G5_LADMIN_PATH."/tail.php");
?>
