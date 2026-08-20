<?php
include_once("_common.php");

$login_level = isset($member['mb_level']) ? (int) $member['mb_level'] : 0;

if ($login_level < LOTTO_ROLE_ADMIN) {
    alert('관리자 이상만 접근할 수 있습니다.', G5_LADMIN_URL);
    exit;
}

if (!isset($g5['new_win_table'])) {
    alert('팝업 테이블 설정을 찾을 수 없습니다.', G5_LADMIN_URL);
    exit;
}

$delete_token = lottoConfigTokenCreate();

$result = sql_query("
    select *
      from {$g5['new_win_table']}
     where nw_division in ('comm', 'both')
       and nw_device in ('pc', 'both')
     order by nw_id desc
", false);

$popup_list = array();

if ($result) {
    while ($row = sql_fetch_array($result)) {
        $popup_list[] = $row;
    }
}

include_once(G5_LADMIN_PATH."/head.php");

function popup_list_html($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES);
}
?>

<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title">중요공지 팝업</h3>

                <div class="ml-auto">
                    <a href="popup.form.php" class="btn btn-light btn-sm">
                        새 팝업 등록
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width:70px;">번호</th>
                                <th>제목</th>
                                <th style="width:100px;">상태</th>
                                <th style="width:170px;">시작일시</th>
                                <th style="width:170px;">종료일시</th>
                                <th style="width:130px;">다시 보지 않기</th>
                                <th style="width:150px;">관리</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (count($popup_list) === 0) { ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    등록된 중요공지 팝업이 없습니다.
                                </td>
                            </tr>
                        <?php } else { ?>
                            <?php foreach ($popup_list as $popup) { ?>
                                <?php
                                $status_text = '진행중';
                                $status_class = 'badge-success';

                                if (G5_TIME_YMDHIS < $popup['nw_begin_time']) {
                                    $status_text = '예약';
                                    $status_class = 'badge-info';
                                } elseif (G5_TIME_YMDHIS > $popup['nw_end_time']) {
                                    $status_text = '종료';
                                    $status_class = 'badge-secondary';
                                }
                                ?>
                                <tr>
                                    <td><?=number_format((int) $popup['nw_id'])?></td>
                                    <td><?=popup_list_html($popup['nw_subject'])?></td>
                                    <td>
                                        <span class="badge <?=$status_class?>">
                                            <?=$status_text?>
                                        </span>
                                    </td>
                                    <td><?=popup_list_html($popup['nw_begin_time'])?></td>
                                    <td><?=popup_list_html($popup['nw_end_time'])?></td>
                                    <td><?=number_format((int) $popup['nw_disable_hours'])?>시간</td>
                                    <td>
                                        <a
                                            href="popup.form.php?w=u&amp;nw_id=<?=(int) $popup['nw_id']?>"
                                            class="btn btn-sm btn-primary"
                                        >수정</a>

                                        <form
                                            method="post"
                                            action="popup.update.php"
                                            class="d-inline"
                                            onsubmit="return confirm('이 팝업을 삭제하시겠습니까?');"
                                        >
                                            <input type="hidden" name="w" value="d">
                                            <input type="hidden" name="nw_id" value="<?=(int) $popup['nw_id']?>">
                                            <input type="hidden" name="token" value="<?=popup_list_html($delete_token)?>">

                                            <button type="submit" class="btn btn-sm btn-danger">
                                                삭제
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once(G5_LADMIN_PATH."/tail.php");
?>
