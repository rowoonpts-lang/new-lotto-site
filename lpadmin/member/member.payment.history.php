<?php
if (
    !isset($row['mb_id'])
    || !isset($login_level)
    || $login_level < LOTTO_ROLE_ADMIN
) {
    return;
}

$target_mb_id = trim((string) $row['mb_id']);
$target_mb_id_sql = sql_real_escape_string($target_mb_id);

$payment_history_rows = array();
$payment_history_error = false;

$payment_history_result = sql_query(
    "select
            s.ls_id,
            s.lpr_id,
            s.staff_mb_id,
            s.payment_method,
            s.product_type,
            s.sale_amount,
            s.approved_by,
            s.approved_at,
            p.request_no,
            p.request_status,
            p.request_note,
            staff.mb_name as staff_name,
            approver.mb_name as approved_by_name
       from l_sales s
       left join l_payment_request p
         on p.lpr_id = s.lpr_id
       left join g5_member staff
         on staff.mb_id = s.staff_mb_id
       left join g5_member approver
         on approver.mb_id = s.approved_by
      where s.mb_id = '{$target_mb_id_sql}'
      order by s.approved_at desc, s.ls_id desc",
    false
);

if (!$payment_history_result) {
    $payment_history_error = true;
} else {
    while (
        $payment_history_row =
            sql_fetch_array($payment_history_result)
    ) {
        $payment_history_rows[] = $payment_history_row;
    }
}
?>

<div class="card card-info card-outline mt-3 mb-0">
    <div class="card-header">
        <h3 class="card-title">
            결제내역
            <strong><?=number_format(count($payment_history_rows))?></strong>건
        </h3>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap text-sm mb-0">
            <thead>
            <tr>
                <th>승인일</th>
                <th>요청번호</th>
                <th>담당자</th>
                <th>결제수단</th>
                <th>상품</th>
                <th class="text-right">결제금액</th>
                <th>상태</th>
                <th>승인자</th>
                <th>내용</th>
            </tr>
            </thead>
            <tbody>

            <?php if ($payment_history_error) { ?>
            <tr>
                <td colspan="9" class="text-center text-danger">
                    결제내역을 불러오지 못했습니다.
                </td>
            </tr>

            <?php } elseif (count($payment_history_rows) < 1) { ?>
            <tr>
                <td colspan="9" class="text-center">
                    결제된 내역이 없습니다.
                </td>
            </tr>

            <?php } else { ?>

                <?php foreach ($payment_history_rows as $payment_history_row) {
                    $request_status = trim(
                        (string) $payment_history_row['request_status']
                    );

                    if ($request_status === '') {
                        $request_status = '승인완료';
                    }

                    $staff_name = trim(
                        (string) $payment_history_row['staff_name']
                    );

                    if ($staff_name === '') {
                        $staff_name =
                            (string) $payment_history_row['staff_mb_id'];
                    }

                    $approved_by_name = trim(
                        (string) $payment_history_row['approved_by_name']
                    );

                    if ($approved_by_name === '') {
                        $approved_by_name =
                            (string) $payment_history_row['approved_by'];
                    }
                ?>
                <tr>
                    <td>
                        <?=htmlspecialchars(
                            (string) $payment_history_row['approved_at'],
                            ENT_QUOTES
                        )?>
                    </td>
                    <td>
                        <?=htmlspecialchars(
                            (string) $payment_history_row['request_no'],
                            ENT_QUOTES
                        )?>
                    </td>
                    <td>
                        <?=htmlspecialchars($staff_name, ENT_QUOTES)?>
                    </td>
                    <td>
                        <?=htmlspecialchars(
                            (string) $payment_history_row['payment_method'],
                            ENT_QUOTES
                        )?>
                    </td>
                    <td>
                        <?=htmlspecialchars(
                            (string) $payment_history_row['product_type'],
                            ENT_QUOTES
                        )?>
                    </td>
                    <td class="text-right">
                        <?=number_format(
                            (int) $payment_history_row['sale_amount']
                        )?>원
                    </td>
                    <td>
                        <?=htmlspecialchars($request_status, ENT_QUOTES)?>
                    </td>
                    <td>
                        <?=htmlspecialchars($approved_by_name, ENT_QUOTES)?>
                    </td>
                    <td>
                        <?=htmlspecialchars(
                            (string) $payment_history_row['request_note'],
                            ENT_QUOTES
                        )?>
                    </td>
                </tr>
                <?php } ?>

            <?php } ?>

            </tbody>
        </table>
    </div>
</div>
