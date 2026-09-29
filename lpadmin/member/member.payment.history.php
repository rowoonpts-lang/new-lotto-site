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
$payment_cancel_token = lottoMemberTokenCreate();

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
            coalesce(c.cancelled_amount, 0) as cancelled_amount,
            staff.mb_name as staff_name,
            approver.mb_name as approved_by_name
       from l_sales s
       left join l_payment_request p
         on p.lpr_id = s.lpr_id
       left join (
            select
                ls_id,
                sum(cancel_amount) as cancelled_amount
              from l_sales_cancel
             group by ls_id
       ) c
         on c.ls_id = s.ls_id
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
                <th class="text-right">취소금액</th>
                <th class="text-right">남은금액</th>
                <th>상태</th>
                <th>승인자</th>
                <th>내용</th>
                <th>취소처리</th>
            </tr>
            </thead>
            <tbody>

            <?php if ($payment_history_error) { ?>
            <tr>
                <td colspan="12" class="text-center text-danger">
                    결제내역을 불러오지 못했습니다.
                </td>
            </tr>

            <?php } elseif (count($payment_history_rows) < 1) { ?>
            <tr>
                <td colspan="12" class="text-center">
                    결제된 내역이 없습니다.
                </td>
            </tr>

            <?php } else { ?>

                <?php foreach ($payment_history_rows as $payment_history_row) {
                    $sale_amount = (int) $payment_history_row['sale_amount'];
                    $cancelled_amount =
                        (int) $payment_history_row['cancelled_amount'];

                    $remaining_amount =
                        max(0, $sale_amount - $cancelled_amount);

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

                    $can_cancel =
                        $remaining_amount > 0
                        && in_array(
                            $request_status,
                            array('승인완료', '부분취소'),
                            true
                        );
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
                        <?=number_format($sale_amount)?>원
                    </td>
                    <td class="text-right">
                        <?=number_format($cancelled_amount)?>원
                    </td>
                    <td class="text-right">
                        <?=number_format($remaining_amount)?>원
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
                    <td style="min-width:300px;">
                        <?php if ($can_cancel) { ?>
                        <form
                            method="post"
                            action="./member.payment.cancel.php"
                            onsubmit="return confirm('입력한 금액을 취소 처리하시겠습니까?');"
                        >
                            <input
                                type="hidden"
                                name="token"
                                value="<?=htmlspecialchars(
                                    $payment_cancel_token,
                                    ENT_QUOTES
                                )?>"
                            >
                            <input
                                type="hidden"
                                name="ls_id"
                                value="<?=(int) $payment_history_row['ls_id']?>"
                            >

                            <div class="input-group input-group-sm mb-1">
                                <input
                                    type="number"
                                    name="cancel_amount"
                                    class="form-control"
                                    min="1"
                                    max="<?=$remaining_amount?>"
                                    step="1"
                                    placeholder="취소금액"
                                    required
                                >
                                <div class="input-group-append">
                                    <span class="input-group-text">원</span>
                                </div>
                            </div>

                            <div class="input-group input-group-sm">
                                <input
                                    type="text"
                                    name="cancel_reason"
                                    class="form-control"
                                    maxlength="255"
                                    placeholder="취소사유"
                                    required
                                >
                                <div class="input-group-append">
                                    <button
                                        type="submit"
                                        class="btn btn-danger"
                                    >
                                        취소
                                    </button>
                                </div>
                            </div>
                        </form>
                        <?php } else { ?>
                            <span class="text-muted">취소완료</span>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>

            <?php } ?>

            </tbody>
        </table>
    </div>
</div>
