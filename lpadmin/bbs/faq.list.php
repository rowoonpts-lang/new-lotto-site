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

$fmId = 1;

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
        G5_LADMIN_URL
    );
    exit;
}

$result = sql_query(
    "select
        fa_id,
        fa_subject,
        fa_order
     from {$g5['faq_table']}
     where fm_id = '{$fmId}'
     order by fa_order asc, fa_id asc",
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

function lottoFaqListHtml($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES
    );
}
?>

<style>
.lotto-faq-list .card-header {
    min-height: 52px;
    padding: 10px 14px;
}

.lotto-faq-list .card-title {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    line-height: 32px;
    color: #fff;
}

.lotto-faq-list .lotto-admin-add-btn {
    min-width: 120px;
    padding: 7px 14px;
    font-size: 15px;
    font-weight: 700;
    color: #212529 !important;
}

.lotto-faq-list table {
    font-size: 16px;
}

.lotto-faq-list table thead th {
    padding: 12px 10px;
    font-size: 16px;
    font-weight: 700;
    color: #222;
    background: #f8f9fa;
    vertical-align: middle;
}

.lotto-faq-list table tbody td {
    padding: 13px 10px;
    font-size: 16px;
    color: #333;
    vertical-align: middle;
}

.lotto-faq-list .faq-question {
    font-size: 16px;
    font-weight: 700;
    color: #222;
}

.lotto-faq-list .btn-sm {
    padding: 6px 11px;
    font-size: 14px;
    font-weight: 700;
}
</style>

<div class="row">
    <div class="col-12">
        <div class="card card-primary lotto-faq-list">

            <div class="card-header d-flex align-items-center">
                <h3 class="card-title">자주묻는 질문</h3>

                <div class="ml-auto">
                    <a
                        href="faq.form.php"
                        class="btn btn-warning lotto-admin-add-btn"
                    >
                        FAQ 등록
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">

                    <table class="table table-bordered table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width:90px;">번호</th>
                                <th>질문</th>
                                <th style="width:120px;">출력순서</th>
                                <th style="width:170px;">관리</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php if (count($rows) === 0) { ?>

                            <tr>
                                <td
                                    colspan="4"
                                    class="text-center text-muted py-4"
                                >
                                    등록된 자주묻는 질문이 없습니다.
                                </td>
                            </tr>

                        <?php } else { ?>

                            <?php foreach ($rows as $row) { ?>

                                <tr>
                                    <td class="text-center">
                                        <?=(int) $row['fa_id']?>
                                    </td>

                                    <td>
                                        <span class="faq-question">
                                            <?=lottoFaqListHtml(
                                                strip_tags($row['fa_subject'])
                                            )?>
                                        </span>
                                    </td>

                                    <td class="text-center">
                                        <?=(int) $row['fa_order']?>
                                    </td>

                                    <td class="text-center">
                                        <a
                                            href="faq.form.php?w=u&amp;fa_id=<?=(int) $row['fa_id']?>"
                                            class="btn btn-primary btn-sm"
                                        >
                                            수정
                                        </a>

                                        <form
                                            method="post"
                                            action="faq.update.php"
                                            class="d-inline"
                                            onsubmit="return confirm('이 FAQ를 삭제하시겠습니까?');"
                                        >
                                            <input type="hidden" name="w" value="d">
                                            <input type="hidden" name="fm_id" value="<?=$fmId?>">
                                            <input
                                                type="hidden"
                                                name="fa_id"
                                                value="<?=(int) $row['fa_id']?>"
                                            >
                                            <input
                                                type="hidden"
                                                name="token"
                                                value="<?=lottoFaqListHtml($token)?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-danger btn-sm"
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
