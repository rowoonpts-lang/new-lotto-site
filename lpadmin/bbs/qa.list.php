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

$status = isset($_GET['status'])
    ? trim((string) $_GET['status'])
    : '';

if (!in_array($status, array('', 'waiting', 'answered'), true)) {
    $status = '';
}

$where = " where qa_type = 0 ";

if ($status === 'waiting') {
    $where .= " and qa_status = 0 ";
} elseif ($status === 'answered') {
    $where .= " and qa_status = 1 ";
}

$result = sql_query(
    "select
        qa_id,
        mb_id,
        qa_name,
        qa_category,
        qa_subject,
        qa_status,
        qa_datetime
     from {$g5['qa_content_table']}
     {$where}
     order by qa_id desc",
    false
);

$rows = array();

if ($result) {
    while ($row = sql_fetch_array($result)) {
        $rows[] = $row;
    }
}

include_once(G5_LADMIN_PATH."/head.php");

function lottoQaListHtml($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES
    );
}
?>

<style>
.lotto-qa-list {
    font-size:17px;
}

.lotto-qa-list .card-header {
    min-height:56px;
    padding:11px 15px;
}

.lotto-qa-list .card-title {
    margin:0;
    font-size:20px;
    line-height:34px;
    font-weight:700;
    color:#fff;
}

.lotto-qa-filter {
    margin-bottom:15px;
}

.lotto-qa-filter .btn {
    min-width:95px;
    min-height:44px;
    padding:9px 15px;
    font-size:16px;
    font-weight:700;
}

.lotto-qa-list table {
    font-size:17px;
}

.lotto-qa-list table thead th {
    padding:14px 10px;
    font-size:18px;
    font-weight:700;
    color:#222;
    background:#f5f6f7;
    vertical-align:middle;
}

.lotto-qa-list table tbody td {
    padding:15px 10px;
    font-size:17px;
    line-height:1.6;
    color:#333;
    vertical-align:middle;
}

.lotto-qa-list .qa-subject {
    font-size:17px;
    font-weight:700;
    color:#222;
}

.lotto-qa-list .qa-status {
    display:inline-block;
    min-width:82px;
    padding:6px 10px;
    border-radius:4px;
    font-size:15px;
    font-weight:700;
    text-align:center;
}

.lotto-qa-list .qa-waiting {
    background:#fff3cd;
    color:#7a4b00;
}

.lotto-qa-list .qa-answered {
    background:#d1e7dd;
    color:#0f5132;
}

.lotto-qa-list .empty-row {
    padding:35px 15px !important;
    font-size:17px !important;
    color:#444 !important;
}
</style>

<div class="row">
    <div class="col-12">

        <div class="lotto-qa-filter">
            <a
                href="qa.list.php"
                class="btn <?=$status === '' ? 'btn-primary' : 'btn-outline-primary'?>"
            >전체</a>

            <a
                href="qa.list.php?status=waiting"
                class="btn <?=$status === 'waiting' ? 'btn-warning' : 'btn-outline-secondary'?>"
            >답변대기</a>

            <a
                href="qa.list.php?status=answered"
                class="btn <?=$status === 'answered' ? 'btn-success' : 'btn-outline-secondary'?>"
            >답변완료</a>
        </div>

        <div class="card card-primary lotto-qa-list">

            <div class="card-header">
                <h3 class="card-title">1:1 상담</h3>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">

                    <table class="table table-bordered table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width:85px;">번호</th>
                                <th style="width:120px;">분류</th>
                                <th>제목</th>
                                <th style="width:150px;">회원</th>
                                <th style="width:180px;">등록일</th>
                                <th style="width:120px;">상태</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php if (count($rows) === 0) { ?>

                            <tr>
                                <td colspan="6" class="text-center empty-row">
                                    등록된 1:1 상담이 없습니다.
                                </td>
                            </tr>

                        <?php } else { ?>

                            <?php foreach ($rows as $row) { ?>

                                <tr>
                                    <td class="text-center">
                                        <?=(int) $row['qa_id']?>
                                    </td>

                                    <td class="text-center">
                                        <?=lottoQaListHtml($row['qa_category'])?>
                                    </td>

                                    <td>
                                        <a
                                            href="qa.view.php?qa_id=<?=(int) $row['qa_id']?>"
                                            class="qa-subject"
                                        >
                                            <?=lottoQaListHtml($row['qa_subject'])?>
                                        </a>
                                    </td>

                                    <td>
                                        <?=lottoQaListHtml(
                                            $row['mb_id'] !== ''
                                                ? $row['mb_id']
                                                : $row['qa_name']
                                        )?>
                                    </td>

                                    <td class="text-center">
                                        <?=lottoQaListHtml($row['qa_datetime'])?>
                                    </td>

                                    <td class="text-center">
                                        <?php if ((int) $row['qa_status'] === 1) { ?>
                                            <span class="qa-status qa-answered">
                                                답변완료
                                            </span>
                                        <?php } else { ?>
                                            <span class="qa-status qa-waiting">
                                                답변대기
                                            </span>
                                        <?php } ?>
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
