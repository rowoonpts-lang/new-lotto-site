<?php
include_once("_common.php");

$login_level = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if ($login_level < LOTTO_ROLE_ADMIN) {
    alert('관리자 이상만 접근할 수 있습니다.', G5_LADMIN_URL);
    exit;
}

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;

if ($page < 1) {
    $page = 1;
}

$rows = 30;

$count_row = sql_fetch(
    "select count(*) as cnt
       from l_memo",
    false
);

$total_count = isset($count_row['cnt'])
    ? (int) $count_row['cnt']
    : 0;

$total_page = $total_count > 0
    ? (int) ceil($total_count / $rows)
    : 1;

if ($page > $total_page) {
    $page = $total_page;
}

$from_record = ($page - 1) * $rows;

$result = sql_query(
    "select
        m.*,
        target.mb_name as member_name,
        target.mb_hp as member_phone,
        writer.mb_name as writer_name
       from l_memo m
       left join g5_member target
         on target.mb_id = m.mb_id
       left join g5_member writer
         on writer.mb_id = m.from_mb_id
      order by m.lm_datetime desc, m.lm_id desc
      limit {$from_record}, {$rows}",
    false
);

$consultation_list = array();

if ($result) {
    while ($row = sql_fetch_array($result)) {
        $consultation_list[] = $row;
    }
}

include_once(G5_LADMIN_PATH."/head.php");

function consultation_history_html($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<style>
.consultation-history-list table {
    font-size: 15px;
}

.consultation-history-list table thead th {
    white-space: nowrap;
    vertical-align: middle;
    background: #f8f9fa;
}

.consultation-history-list table tbody td {
    vertical-align: middle;
}

.consultation-history-content {
    min-width: 320px;
    max-width: 520px;
    white-space: normal;
    word-break: break-word;
    line-height: 1.5;
}

.consultation-history-date,
.consultation-history-phone {
    white-space: nowrap;
}
</style>

<div class="row">
    <div class="col-12">
        <div class="card card-primary consultation-history-list">
            <div class="card-header">
                <h3 class="card-title">상담내역</h3>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width:70px;">번호</th>
                                <th>회원</th>
                                <th>연락처</th>
                                <th>담당</th>
                                <th>상태</th>
                                <th>상담내용</th>
                                <th>입력시간</th>
                                <th>알림시간</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php if (count($consultation_list) === 0) { ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    등록된 상담내역이 없습니다.
                                </td>
                            </tr>
                        <?php } else { ?>
                            <?php foreach ($consultation_list as $index => $history) { ?>
                                <?php
                                $row_number =
                                    $total_count
                                    - (($page - 1) * $rows)
                                    - $index;

                                $member_label =
                                    trim((string) $history['member_name']);

                                if ($member_label === '') {
                                    $member_label =
                                        (string) $history['mb_id'];
                                }

                                $writer_label =
                                    trim((string) $history['writer_name']);

                                if ($writer_label === '') {
                                    $writer_label =
                                        (string) $history['from_mb_id'];
                                }

                                $alarm_date = '';

                                if (
                                    isset($history['lm_alarm_view'])
                                    && (string) $history['lm_alarm_view'] === '0'
                                ) {
                                    $alarm_date = str_replace(
                                        '0000-00-00 00:00:00',
                                        '',
                                        (string) $history['lm_alarm_date']
                                    );
                                }
                                ?>
                                <tr>
                                    <td><?=number_format($row_number)?></td>

                                    <td>
                                        <?=consultation_history_html($member_label)?>
                                        <?php if (!empty($history['mb_id'])) { ?>
                                            <div class="text-muted small">
                                                <?=consultation_history_html($history['mb_id'])?>
                                            </div>
                                        <?php } ?>
                                    </td>

                                    <td class="consultation-history-phone">
                                        <?=consultation_history_html(
                                            $history['member_phone']
                                        )?>
                                    </td>

                                    <td>
                                        <?=consultation_history_html($writer_label)?>
                                    </td>

                                    <td>
                                        <?=consultation_history_html(
                                            $history['lm_memo_type']
                                        )?>
                                    </td>

                                    <td class="consultation-history-content">
                                        <?=nl2br(
                                            consultation_history_html(
                                                $history['lm_memo']
                                            )
                                        )?>
                                    </td>

                                    <td class="consultation-history-date">
                                        <?=consultation_history_html(
                                            $history['lm_datetime']
                                        )?>
                                    </td>

                                    <td class="consultation-history-date">
                                        <?=$alarm_date !== ''
                                            ? consultation_history_html($alarm_date)
                                            : '-'?>
                                    </td>
                                </tr>
                            <?php } ?>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($total_count > 0) { ?>
                <div class="card-footer">
                    <?=get_paging(
                        G5_IS_MOBILE
                            ? $config['cf_mobile_pages']
                            : $config['cf_write_pages'],
                        $page,
                        $total_page,
                        '?page='
                    )?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<?php
include_once(G5_LADMIN_PATH."/tail.php");
?>
