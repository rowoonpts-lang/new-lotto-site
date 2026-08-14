<?php
include_once("_common.php");

$login_level = isset($member['mb_level']) ? (int) $member['mb_level'] : 0;
if ($login_level < LOTTO_ROLE_ADMIN) {
    alert('관리자 이상만 접근할 수 있습니다.');
    exit;
}

$sch_status = isset($_GET['sch_status']) ? trim((string) $_GET['sch_status']) : '승인대기';
$sch_method = isset($_GET['sch_method']) ? trim((string) $_GET['sch_method']) : '';
$sch_text = isset($_GET['sch_text']) ? trim((string) $_GET['sch_text']) : '';
$start_date = isset($_GET['start_date']) ? trim((string) $_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? trim((string) $_GET['end_date']) : '';
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$rows = 30;

$where = array('1=1');

$allowed_status = array('', '승인대기', '승인완료', '승인거절');
if (!in_array($sch_status, $allowed_status, true)) {
    $sch_status = '승인대기';
}
if ($sch_status !== '') {
    $where[] = "a.request_status = '".sql_real_escape_string($sch_status)."'";
}

$allowed_methods = array('', '무통장', '신용카드');
if (!in_array($sch_method, $allowed_methods, true)) {
    $sch_method = '';
}
if ($sch_method !== '') {
    $where[] = "a.payment_method = '".sql_real_escape_string($sch_method)."'";
}

if ($sch_text !== '') {
    $text_sql = sql_real_escape_string($sch_text);
    $where[] = "(a.request_no like '%{$text_sql}%'
        or a.mb_id like '%{$text_sql}%'
        or m.mb_name like '%{$text_sql}%'
        or m.mb_code like '%{$text_sql}%'
        or a.staff_mb_id like '%{$text_sql}%'
        or s.mb_name like '%{$text_sql}%')";
}

if ($start_date !== '') {
    $start_sql = sql_real_escape_string($start_date);
    $where[] = "date(a.created_at) >= '{$start_sql}'";
}
if ($end_date !== '') {
    $end_sql = sql_real_escape_string($end_date);
    $where[] = "date(a.created_at) <= '{$end_sql}'";
}

$where_sql = implode(' and ', $where);

$count_row = sql_fetch(
    "select count(*) as cnt
       from l_payment_request a
       left join g5_member m on m.mb_id = a.mb_id
       left join g5_member s on s.mb_id = a.staff_mb_id
      where {$where_sql}"
);
$total_count = isset($count_row['cnt']) ? (int) $count_row['cnt'] : 0;
$total_page = $total_count > 0 ? (int) ceil($total_count / $rows) : 1;
if ($page > $total_page) {
    $page = $total_page;
}
$from_record = ($page - 1) * $rows;

$payment_result = sql_query(
    "select a.*,
            m.mb_name as member_name,
            m.mb_code as member_code,
            s.mb_name as staff_name,
            r.mb_name as requested_by_name,
            ap.mb_name as approved_by_name
       from l_payment_request a
       left join g5_member m on m.mb_id = a.mb_id
       left join g5_member s on s.mb_id = a.staff_mb_id
       left join g5_member r on r.mb_id = a.requested_by
       left join g5_member ap on ap.mb_id = a.approved_by
      where {$where_sql}
      order by a.created_at desc, a.lpr_id desc
      limit {$from_record}, {$rows}"
);

include_once(G5_LADMIN_PATH."/head.php");
?>

<div class="card card-default">
    <div class="card-body">
        <form method="get" autocomplete="off">
            <div class="row">
                <div class="col-md-2 mb-2">
                    <select name="sch_status" class="form-control">
                        <option value="">상태전체</option>
                        <?php foreach (array('승인대기', '승인완료', '승인거절') as $status) { ?>
                        <option value="<?=htmlspecialchars($status, ENT_QUOTES)?>"<?=$sch_status === $status ? ' selected' : ''?>><?=htmlspecialchars($status, ENT_QUOTES)?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select name="sch_method" class="form-control">
                        <option value="">결제수단전체</option>
                        <option value="무통장"<?=$sch_method === '무통장' ? ' selected' : ''?>>무통장</option>
                        <option value="신용카드"<?=$sch_method === '신용카드' ? ' selected' : ''?>>신용카드</option>
                    </select>
                </div>
                <div class="col-md-3 mb-2">
                    <input type="text" name="sch_text" class="form-control" value="<?=htmlspecialchars($sch_text, ENT_QUOTES)?>" placeholder="회원명/회원코드/아이디/담당자/요청번호">
                </div>
                <div class="col-md-2 mb-2">
                    <input type="date" name="start_date" class="form-control" value="<?=htmlspecialchars($start_date, ENT_QUOTES)?>">
                </div>
                <div class="col-md-2 mb-2">
                    <input type="date" name="end_date" class="form-control" value="<?=htmlspecialchars($end_date, ENT_QUOTES)?>">
                </div>
                <div class="col-md-1 mb-2">
                    <button type="submit" class="btn btn-danger btn-block">검색</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">결제 승인요청 <strong><?=number_format($total_count)?></strong>건</h3>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap text-sm mb-0">
            <thead>
            <tr>
                <th>요청일</th>
                <th>요청번호</th>
                <th>회원</th>
                <th>담당자</th>
                <th>요청자</th>
                <th>결제수단</th>
                <th>상품</th>
                <th class="text-right">요청금액</th>
                <th>상태</th>
                <th>입금자명</th>
                <th>입금계좌</th>
                <th>이용기간 / 승인</th>
                <th>승인자</th>
                <th>승인일</th>
            </tr>
            </thead>
            <tbody>
            <?php $list_count = 0; ?>
            <?php while ($row = sql_fetch_array($payment_result)) { $list_count++; ?>
            <tr>
                <td><?=htmlspecialchars((string) $row['created_at'], ENT_QUOTES)?></td>
                <td><?=htmlspecialchars((string) $row['request_no'], ENT_QUOTES)?></td>
                <td>
                    <?=htmlspecialchars((string) ($row['member_name'] ?: $row['mb_id']), ENT_QUOTES)?><br>
                    <small><?=htmlspecialchars((string) ($row['member_code'] ?: $row['mb_id']), ENT_QUOTES)?></small>
                </td>
                <td><?=htmlspecialchars((string) ($row['staff_name'] ?: $row['staff_mb_id']), ENT_QUOTES)?></td>
                <td><?=htmlspecialchars((string) ($row['requested_by_name'] ?: $row['requested_by']), ENT_QUOTES)?></td>
                <td><?=htmlspecialchars((string) $row['payment_method'], ENT_QUOTES)?></td>
                <td><?=htmlspecialchars((string) $row['product_type'], ENT_QUOTES)?></td>
                <td class="text-right"><?=number_format((int) $row['request_amount'])?>원</td>
                <td><?=htmlspecialchars((string) $row['request_status'], ENT_QUOTES)?></td>
                <td><?=htmlspecialchars((string) $row['depositor_name'], ENT_QUOTES)?></td>
                <td><?=htmlspecialchars((string) $row['bank_account'], ENT_QUOTES)?></td>
                <td style="min-width:290px;">
                    <?php if ($row['request_status'] === '승인대기' && $row['payment_method'] === '무통장') { ?>
                    <form method="post" action="<?=G5_LADMIN_URL?>/payment/payment.approval.update.php" class="mb-0" onsubmit="return confirmPaymentApproval(this);">
                        <input type="hidden" name="lpr_id" value="<?=(int) $row['lpr_id']?>">
                        <div class="d-flex align-items-center mb-1">
                            <input type="date" name="service_start_date" class="form-control form-control-sm mr-1" aria-label="이용 시작일" required>
                            <span class="mr-1">~</span>
                            <input type="date" name="service_end_date" class="form-control form-control-sm" aria-label="이용 종료일" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm btn-block">승인완료</button>
                    </form>
                    <?php } elseif (!empty($row['service_start_date']) || !empty($row['service_end_date'])) { ?>
                        <?=htmlspecialchars((string) $row['service_start_date'], ENT_QUOTES)?>
                        ~
                        <?=htmlspecialchars((string) $row['service_end_date'], ENT_QUOTES)?>
                    <?php } else { ?>
                        -
                    <?php } ?>
                </td>
                <td><?=htmlspecialchars((string) ($row['approved_by_name'] ?: $row['approved_by']), ENT_QUOTES)?></td>
                <td><?=htmlspecialchars((string) $row['approved_at'], ENT_QUOTES)?></td>
            </tr>
            <?php } ?>
            <?php if ($list_count < 1) { ?>
            <tr><td colspan="14" class="text-center">조회된 결제 승인요청이 없습니다.</td></tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php if ($total_count > $rows) { ?>
    <div class="card-footer">
        <?php
        $query = http_build_query(array(
            'sch_status' => $sch_status,
            'sch_method' => $sch_method,
            'sch_text' => $sch_text,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ));
        echo get_paging($config['cf_write_pages'], $page, $total_page, '?'.$query.'&amp;page=');
        ?>
    </div>
    <?php } ?>
</div>

<script>
function confirmPaymentApproval(form) {
    var startDate = form.service_start_date.value;
    var endDate = form.service_end_date.value;

    if (!startDate || !endDate) {
        alert('이용 시작일과 종료일을 모두 선택해주세요.');
        return false;
    }

    if (endDate < startDate) {
        alert('이용 종료일은 시작일보다 빠를 수 없습니다.');
        return false;
    }

    return confirm('입금을 확인하셨습니까?\n선택한 이용기간으로 승인완료 처리합니다.');
}
</script>

<?php include_once(G5_LADMIN_PATH."/tail.php"); ?>
