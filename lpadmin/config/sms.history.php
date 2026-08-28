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
       from l_sms_history",
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
        h.*,
        receiver.mb_name as receiver_name,
        sender.mb_name as sender_name,
        o.SendResult as oshot_send_result,
        o.ResultMsg as oshot_result_message,
        o.SendDT as oshot_send_dt
       from l_sms_history h
       left join g5_member receiver
         on receiver.mb_id = h.mb_id
       left join g5_member sender
         on sender.mb_id = h.sender_mb_id
       left join OShotMSG o
         on o.MsgID = h.oshot_msg_id
      order by h.queued_at desc, h.lsh_id desc
      limit {$from_record}, {$rows}",
    false
);

$sms_history_list = array();

if ($result) {
    while ($row = sql_fetch_array($result)) {
        $sms_history_list[] = $row;
    }
}

include_once(G5_LADMIN_PATH."/head.php");

function sms_history_html($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function sms_history_status_text($row)
{
    $history_status = isset($row['send_status'])
        ? trim((string) $row['send_status'])
        : '';

    $send_result = isset($row['oshot_send_result'])
        && $row['oshot_send_result'] !== ''
        ? (int) $row['oshot_send_result']
        : null;

    if ($send_result === 6) {
        return '발송완료';
    }

    if ($send_result === 1) {
        return '발송요청완료';
    }

    if (
        $send_result !== null
        && $send_result >= 95
        && $send_result <= 99
    ) {
        return '결과확인중';
    }

    if ($send_result !== null && $send_result > 1) {
        return '발송실패';
    }

    if ($history_status === 'sent') {
        return '발송완료';
    }

    if ($history_status === 'failed') {
        return '발송실패';
    }

    return '발송대기';
}

function sms_history_category_text($category)
{
    switch ((string) $category) {
        case 'consultation':
            return '상담문자';
        case 'combination':
            return '추천번호';
        case 'winner':
            return '결과안내';
        default:
            return (string) $category;
    }
}
?>

<style>
.sms-history-list table {
    font-size: 15px;
}

.sms-history-list table thead th {
    white-space: nowrap;
    vertical-align: middle;
    background: #f8f9fa;
}

.sms-history-list table tbody td {
    vertical-align: middle;
}

.sms-history-message {
    min-width: 280px;
    max-width: 460px;
    white-space: normal;
    word-break: break-word;
    line-height: 1.5;
}

.sms-history-message-toggle {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
    cursor: pointer;
}

.sms-history-message-toggle.is-expanded {
    display: block;
    overflow: visible;
    -webkit-line-clamp: unset;
}

.sms-history-phone,
.sms-history-date {
    white-space: nowrap;
}
</style>

<div class="row">
    <div class="col-12">
        <div class="card card-primary sms-history-list">
            <div class="card-header">
                <h3 class="card-title">SMS 발송내역</h3>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width:70px;">번호</th>
                                <th>회원</th>
                                <th>수신번호</th>
                                <th>발신자</th>
                                <th>구분</th>
                                <th>타입</th>
                                <th>발송내용</th>
                                <th>입력시간</th>
                                <th>발송시간</th>
                                <th>상태</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php if (count($sms_history_list) === 0) { ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    SMS 발송내역이 없습니다.
                                </td>
                            </tr>
                        <?php } else { ?>
                            <?php foreach ($sms_history_list as $index => $history) { ?>
                                <?php
                                $row_number =
                                    $total_count
                                    - (($page - 1) * $rows)
                                    - $index;

                                $receiver_label =
                                    trim((string) $history['receiver_name']);

                                if ($receiver_label === '') {
                                    $receiver_label =
                                        (string) $history['mb_id'];
                                }

                                $sender_label =
                                    trim((string) $history['sender_name']);

                                if ($sender_label === '') {
                                    $sender_label =
                                        (string) $history['sender_mb_id'];
                                }

                                $send_time =
                                    trim((string) $history['oshot_send_dt']);

                                if ($send_time === '') {
                                    $send_time =
                                        trim((string) $history['sent_at']);
                                }
                                ?>
                                <tr>
                                    <td><?=number_format($row_number)?></td>

                                    <td>
                                        <?=sms_history_html($receiver_label)?>
                                        <?php if (!empty($history['mb_id'])) { ?>
                                            <div class="text-muted small">
                                                <?=sms_history_html($history['mb_id'])?>
                                            </div>
                                        <?php } ?>
                                    </td>

                                    <td class="sms-history-phone">
                                        <?=sms_history_html($history['receiver_phone'])?>
                                    </td>

                                    <td>
                                        <?=sms_history_html($sender_label)?>
                                    </td>

                                    <td>
                                        <?=sms_history_html(
                                            sms_history_category_text(
                                                $history['send_category']
                                            )
                                        )?>
                                    </td>

                                    <td>
                                        <?=sms_history_html($history['send_type'])?>
                                    </td>

                                    <td class="sms-history-message">
                                        <div
                                            class="sms-history-message-toggle"
                                            onclick="this.classList.toggle('is-expanded')"
                                            title="클릭하여 전체 내용 보기"
                                        >
                                            <?=nl2br(
                                                sms_history_html(
                                                    $history['message']
                                                )
                                            )?>
                                        </div>
                                    </td>

                                    <td class="sms-history-date">
                                        <?=sms_history_html($history['queued_at'])?>
                                    </td>

                                    <td class="sms-history-date">
                                        <?=$send_time !== ''
                                            ? sms_history_html($send_time)
                                            : '-'?>
                                    </td>

                                    <td>
                                        <?=sms_history_html(
                                            sms_history_status_text($history)
                                        )?>
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
