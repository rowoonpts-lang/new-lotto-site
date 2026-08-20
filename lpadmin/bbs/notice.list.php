<?php

include_once("_common.php");

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

$writeTable = $g5['write_prefix'].'notice';

$result = sql_query(
    "select
        wr_id,
        wr_subject,
        wr_name,
        wr_datetime
     from {$writeTable}
     where wr_is_comment = 0
     order by wr_num asc, wr_reply asc",
    false
);

$rows = array();

if ($result) {
    while ($row = sql_fetch_array($result)) {
        $rows[] = $row;
    }
}

$token = lottoBbsTokenCreate();

include_once(G5_LADMIN_PATH."/head.php");

function noticeListHtml($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES
    );
}
?>

<style>
.lotto-admin-list .card-header {
    min-height: 52px;
    padding: 10px 14px;
}

.lotto-admin-list .card-title {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    line-height: 32px;
    color: #ffffff;
}

.lotto-admin-list .lotto-admin-add-btn {
    min-width: 120px;
    padding: 7px 14px;
    font-size: 14px;
    font-weight: 700;
    color: #212529 !important;
}

.lotto-admin-list table {
    font-size: 14px;
}

.lotto-admin-list table thead th {
    padding: 11px 10px;
    font-size: 14px;
    font-weight: 700;
    color: #212529;
    vertical-align: middle;
    background: #f8f9fa;
}

.lotto-admin-list table tbody td {
    padding: 11px 10px;
    font-size: 14px;
    color: #343a40;
    vertical-align: middle;
}

.lotto-admin-list table tbody td a:not(.btn) {
    font-weight: 600;
}

.lotto-admin-list .btn-sm {
    padding: 5px 10px;
    font-size: 13px;
    font-weight: 600;
}
</style>

<div class="row">
    <div class="col-12">
        <div class="card card-primary lotto-admin-list">

            <div class="card-header d-flex align-items-center">
                <h3 class="card-title">공지사항</h3>

                <div class="ml-auto">
                    <a
                        href="notice.form.php"
                        class="btn btn-warning btn-sm font-weight-bold lotto-admin-add-btn"
                    >
                        공지사항 등록
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">

                    <table class="table table-bordered table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width:80px;">번호</th>
                                <th>제목</th>
                                <th style="width:140px;">작성자</th>
                                <th style="width:180px;">등록일</th>
                                <th style="width:160px;">관리</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php if (count($rows) === 0) { ?>

                            <tr>
                                <td
                                    colspan="5"
                                    class="text-center text-muted py-4"
                                >
                                    등록된 공지사항이 없습니다.
                                </td>
                            </tr>

                        <?php } else { ?>

                            <?php foreach ($rows as $row) { ?>

                                <tr>
                                    <td>
                                        <?=number_format((int) $row['wr_id'])?>
                                    </td>

                                    <td>
                                        <a
                                            href="<?=G5_BBS_URL?>/board.php?bo_table=notice&amp;wr_id=<?=(int) $row['wr_id']?>"
                                            target="_blank"
                                        >
                                            <?=noticeListHtml($row['wr_subject'])?>
                                        </a>
                                    </td>

                                    <td>
                                        <?=noticeListHtml($row['wr_name'])?>
                                    </td>

                                    <td>
                                        <?=noticeListHtml($row['wr_datetime'])?>
                                    </td>

                                    <td>
                                        <a
                                            href="notice.form.php?w=u&amp;wr_id=<?=(int) $row['wr_id']?>"
                                            class="btn btn-sm btn-primary"
                                        >
                                            수정
                                        </a>

                                        <form
                                            method="post"
                                            action="notice.update.php"
                                            class="d-inline"
                                            onsubmit="return confirm('이 공지사항을 삭제하시겠습니까?');"
                                        >
                                            <input type="hidden" name="w" value="d">

                                            <input
                                                type="hidden"
                                                name="wr_id"
                                                value="<?=(int) $row['wr_id']?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="token"
                                                value="<?=noticeListHtml($token)?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-danger"
                                            >
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
