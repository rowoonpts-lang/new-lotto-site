<?php
include_once("_common.php");

$login_mb_id = isset($member['mb_id']) ? trim((string) $member['mb_id']) : '';
$login_level = isset($member['mb_level']) ? (int) $member['mb_level'] : 0;

if ($login_mb_id === '') {
    alert('로그인이 필요합니다.');
    exit;
}

if (!lottoIsStaffLevel($login_level)) {
    alert('매출내역을 조회할 권한이 없습니다.', G5_LADMIN_URL);
    exit;
}

$can_view_all_sales = lottoCanViewAllMembers($login_level);

$sales_staff_ids = array();

if (!$can_view_all_sales) {
    $sales_staff_ids[] = $login_mb_id;

    if (
        $login_level === LOTTO_ROLE_STAFF2
        || $login_level === LOTTO_ROLE_TEAM_LEADER
    ) {
        $child_staff_ids = lottoGetDirectChildStaffIds($login_mb_id);

        foreach ($child_staff_ids as $child_staff_id) {
            $sales_staff_ids[] = $child_staff_id;
        }
    }

    $sales_staff_ids = array_values(
        array_unique(
            array_filter($sales_staff_ids)
        )
    );
}

$sch_method = isset($_GET['sch_method']) ? trim((string) $_GET['sch_method']) : '';
$sch_product = isset($_GET['sch_product']) ? trim((string) $_GET['sch_product']) : '';
$sch_staff = isset($_GET['sch_staff']) ? trim((string) $_GET['sch_staff']) : '';
$sch_text = isset($_GET['sch_text']) ? trim((string) $_GET['sch_text']) : '';
$min_amount_raw = isset($_GET['min_amount']) ? preg_replace('/[^0-9]/', '', (string) $_GET['min_amount']) : '';
$max_amount_raw = isset($_GET['max_amount']) ? preg_replace('/[^0-9]/', '', (string) $_GET['max_amount']) : '';
$start_date = isset($_GET['start_date']) ? trim((string) $_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? trim((string) $_GET['end_date']) : '';
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$rows = 30;

if (!$can_view_all_sales) {
    if (
        $sch_staff === ''
        || !in_array($sch_staff, $sales_staff_ids, true)
    ) {
        $sch_staff = '';
    }
}

$where = array('1=1');

if (!$can_view_all_sales) {
    $sales_staff_ids_sql = array();

    foreach ($sales_staff_ids as $sales_staff_id) {
        $sales_staff_ids_sql[] =
            "'" . sql_real_escape_string($sales_staff_id) . "'";
    }

    if (count($sales_staff_ids_sql) > 0) {
        $where[] = 'a.staff_mb_id in ('.
            implode(',', $sales_staff_ids_sql).
            ')';
    } else {
        $where[] = '1=0';
    }
}

$allowed_methods = array('', '무통장', '신용카드');
if (!in_array($sch_method, $allowed_methods, true)) {
    $sch_method = '';
}
if ($sch_method !== '') {
    $where[] = "a.payment_method = '".sql_real_escape_string($sch_method)."'";
}

if ($sch_product !== '') {
    $where[] = "a.product_type = '".sql_real_escape_string($sch_product)."'";
}
if ($sch_staff !== '') {
    $where[] = "a.staff_mb_id = '".sql_real_escape_string($sch_staff)."'";
}
if ($sch_text !== '') {
    $text_sql = sql_real_escape_string($sch_text);
    $where[] = "(a.mb_id like '%{$text_sql}%'
        or m.mb_name like '%{$text_sql}%'
        or m.mb_code like '%{$text_sql}%'
        or a.staff_mb_id like '%{$text_sql}%'
        or s.mb_name like '%{$text_sql}%')";
}

if ($min_amount_raw !== '') {
    $where[] = 'a.sale_amount >= '.(int) $min_amount_raw;
}
if ($max_amount_raw !== '') {
    $where[] = 'a.sale_amount <= '.(int) $max_amount_raw;
}
if ($start_date !== '') {
    $where[] = "date(a.approved_at) >= '".sql_real_escape_string($start_date)."'";
}
if ($end_date !== '') {
    $where[] = "date(a.approved_at) <= '".sql_real_escape_string($end_date)."'";
}

$where_sql = implode(' and ', $where);

$product_result = sql_query("select distinct product_type from l_sales where product_type <> '' order by product_type asc");
$product_list = array();
while ($product_row = sql_fetch_array($product_result)) {
    $product_list[] = (string) $product_row['product_type'];
}

$staff_list = array();

if ($can_view_all_sales) {
    $staff_result = sql_query(
        "select distinct a.staff_mb_id, m.mb_name
           from l_sales a
           left join g5_member m on m.mb_id = a.staff_mb_id
          where a.staff_mb_id <> ''
          order by m.mb_name asc, a.staff_mb_id asc"
    );

    while ($staff_row = sql_fetch_array($staff_result)) {
        $staff_list[] = $staff_row;
    }
} elseif (
    $login_level === LOTTO_ROLE_STAFF2
    || $login_level === LOTTO_ROLE_TEAM_LEADER
) {
    foreach ($sales_staff_ids as $sales_staff_id) {
        $sales_staff_id_sql = sql_real_escape_string($sales_staff_id);

        $staff_row = sql_fetch(
            "select mb_id as staff_mb_id, mb_name
               from g5_member
              where mb_id = '{$sales_staff_id_sql}'
              limit 1",
            false
        );

        if (!empty($staff_row['staff_mb_id'])) {
            $staff_list[] = $staff_row;
        }
    }
}

$count_row = sql_fetch(
    "select count(*) as cnt, coalesce(sum(a.sale_amount), 0) as total_amount
       from l_sales a
       left join g5_member m on m.mb_id = a.mb_id
       left join g5_member s on s.mb_id = a.staff_mb_id
      where {$where_sql}"
);
$total_count = isset($count_row['cnt']) ? (int) $count_row['cnt'] : 0;
$total_amount = isset($count_row['total_amount']) ? (int) $count_row['total_amount'] : 0;
$total_page = $total_count > 0 ? (int) ceil($total_count / $rows) : 1;
if ($page > $total_page) {
    $page = $total_page;
}
$from_record = ($page - 1) * $rows;

$sales_result = sql_query(
    "select a.*,
            m.mb_name as member_name,
            m.mb_code as member_code,
            s.mb_name as staff_name,
            ap.mb_name as approved_by_name,
            p.request_no
       from l_sales a
       left join g5_member m on m.mb_id = a.mb_id
       left join g5_member s on s.mb_id = a.staff_mb_id
       left join g5_member ap on ap.mb_id = a.approved_by
       left join l_payment_request p on p.lpr_id = a.lpr_id
      where {$where_sql}
      order by a.approved_at desc, a.ls_id desc
      limit {$from_record}, {$rows}"
);

include_once(G5_LADMIN_PATH."/head.php");
?>

<div class="card card-default">
    <div class="card-body">
        <form method="get" autocomplete="off">
            <div class="row">
                <div class="col-md-2 mb-2">
                    <select name="sch_method" class="form-control">
                        <option value="">결제수단전체</option>
                        <option value="무통장"<?=$sch_method === '무통장' ? ' selected' : ''?>>무통장</option>
                        <option value="신용카드"<?=$sch_method === '신용카드' ? ' selected' : ''?>>신용카드</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <select name="sch_product" class="form-control">
                        <option value="">상품전체</option>
                        <?php foreach ($product_list as $product_type) { ?>
                        <option value="<?=htmlspecialchars($product_type, ENT_QUOTES)?>"<?=$sch_product === $product_type ? ' selected' : ''?>><?=htmlspecialchars($product_type, ENT_QUOTES)?></option>
                        <?php } ?>
                    </select>
                </div>
                <?php if (
                    $can_view_all_sales
                    || $login_level === LOTTO_ROLE_STAFF2
                    || $login_level === LOTTO_ROLE_TEAM_LEADER
                ) { ?>
                <div class="col-md-2 mb-2">
                    <select name="sch_staff" class="form-control">
                        <option value="">담당자전체</option>
                        <?php foreach ($staff_list as $staff_row) {
                            $staff_id = (string) $staff_row['staff_mb_id'];
                            $staff_name = trim((string) $staff_row['mb_name']);
                            $staff_label = $staff_name !== '' ? $staff_name.' ('.$staff_id.')' : $staff_id;
                        ?>
                        <option value="<?=htmlspecialchars($staff_id, ENT_QUOTES)?>"<?=$sch_staff === $staff_id ? ' selected' : ''?>><?=htmlspecialchars($staff_label, ENT_QUOTES)?></option>
                        <?php } ?>
                    </select>
                </div>
                <?php } ?>
                <div class="col-md-3 mb-2">
                    <input type="text" name="sch_text" class="form-control" value="<?=htmlspecialchars($sch_text, ENT_QUOTES)?>" placeholder="회원명/회원코드/아이디/담당자">
                </div>
                <div class="col-md-3 mb-2">
                    <div class="row">
                        <div class="col-6 pr-1"><input type="text" name="min_amount" class="form-control" inputmode="numeric" value="<?=htmlspecialchars($min_amount_raw, ENT_QUOTES)?>" placeholder="최소금액"></div>
                        <div class="col-6 pl-1"><input type="text" name="max_amount" class="form-control" inputmode="numeric" value="<?=htmlspecialchars($max_amount_raw, ENT_QUOTES)?>" placeholder="최대금액"></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-2 mb-2"><input type="date" name="start_date" class="form-control" value="<?=htmlspecialchars($start_date, ENT_QUOTES)?>"></div>
                <div class="col-md-2 mb-2"><input type="date" name="end_date" class="form-control" value="<?=htmlspecialchars($end_date, ENT_QUOTES)?>"></div>
                <div class="col-md-1 mb-2"><button type="submit" class="btn btn-danger btn-block">검색</button></div>
                <div class="col-md-1 mb-2"><a href="./sales.list.php" class="btn btn-secondary btn-block">초기화</a></div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="small-box bg-info">
            <div class="inner">
                <h3><?=number_format($total_count)?></h3>
                <p>조회 매출건수</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="small-box bg-success">
            <div class="inner">
                <h3><?=number_format($total_amount)?>원</h3>
                <p>조회 매출합계</p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="card-title">매출내역</h3></div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap text-sm mb-0">
            <thead>
            <tr>
                <th>승인일</th>
                <th>요청번호</th>
                <th>회원</th>
                <th>담당자</th>
                <th>결제수단</th>
                <th>상품</th>
                <th class="text-right">매출금액</th>
                <th>승인자</th>
            </tr>
            </thead>
            <tbody>
            <?php $list_count = 0; ?>
            <?php while ($row = sql_fetch_array($sales_result)) { $list_count++; ?>
            <tr>
                <td><?=htmlspecialchars((string) $row['approved_at'], ENT_QUOTES)?></td>
                <td><?=htmlspecialchars((string) $row['request_no'], ENT_QUOTES)?></td>
                <td>
                    <?=htmlspecialchars((string) ($row['member_name'] ?: $row['mb_id']), ENT_QUOTES)?><br>
                    <small><?=htmlspecialchars((string) ($row['member_code'] ?: $row['mb_id']), ENT_QUOTES)?></small>
                </td>
                <td><?=htmlspecialchars((string) ($row['staff_name'] ?: $row['staff_mb_id']), ENT_QUOTES)?></td>
                <td><?=htmlspecialchars((string) $row['payment_method'], ENT_QUOTES)?></td>
                <td><?=htmlspecialchars((string) $row['product_type'], ENT_QUOTES)?></td>
                <td class="text-right"><?=number_format((int) $row['sale_amount'])?>원</td>
                <td><?=htmlspecialchars((string) ($row['approved_by_name'] ?: $row['approved_by']), ENT_QUOTES)?></td>
            </tr>
            <?php } ?>
            <?php if ($list_count < 1) { ?>
            <tr><td colspan="8" class="text-center">조회된 매출내역이 없습니다.</td></tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
    <?php if ($total_count > $rows) { ?>
    <div class="card-footer">
        <?php
        $query = http_build_query(array(
            'sch_method' => $sch_method,
            'sch_product' => $sch_product,
            'sch_staff' => $sch_staff,
            'sch_text' => $sch_text,
            'min_amount' => $min_amount_raw,
            'max_amount' => $max_amount_raw,
            'start_date' => $start_date,
            'end_date' => $end_date,
        ));
        echo get_paging($config['cf_write_pages'], $page, $total_page, '?'.$query.'&amp;page=');
        ?>
    </div>
    <?php } ?>
</div>

<?php include_once(G5_LADMIN_PATH."/tail.php"); ?>
