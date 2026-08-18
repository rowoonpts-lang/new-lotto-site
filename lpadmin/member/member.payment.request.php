<?php
if (!isset($row['mb_id']) || !isset($login_level) || $login_level >= LOTTO_ROLE_ADMIN) {
    return;
}

$bank_accounts = array();
$bank_account_result = sql_query(
    "select lpba_id, bank_name, account_number, account_holder
       from l_payment_bank_account
      where is_active = 1
      order by sort_order asc, lpba_id asc"
);
while ($bank_account_row = sql_fetch_array($bank_account_result)) {
    $bank_accounts[] = $bank_account_row;
}

$product_list = fnGetTypePre();

$login_mb_id = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$payment_request_rows = array();

if ($login_mb_id !== '' && !empty($row['mb_id'])) {
    $target_mb_id_sql = sql_real_escape_string((string) $row['mb_id']);
    $login_mb_id_sql = sql_real_escape_string($login_mb_id);

    $payment_request_result = sql_query(
        "select lpr_id,
                request_no,
                payment_method,
                product_type,
                request_amount,
                request_status,
                reject_reason,
                created_at,
                updated_at
           from l_payment_request
          where mb_id = '{$target_mb_id_sql}'
            and requested_by = '{$login_mb_id_sql}'
          order by created_at desc, lpr_id desc
          limit 10",
        false
    );

    if ($payment_request_result) {
        while ($payment_request_row = sql_fetch_array($payment_request_result)) {
            $payment_request_rows[] = $payment_request_row;
        }
    }
}
?>
<div class="card card-success card-outline mt-3 mb-0">
    <div class="card-header">
        <h3 class="card-title">무통장 승인요청</h3>
    </div>
    <form id="frm_bank_payment_request" method="post" action="pop.member.payment.request.php" autocomplete="off" onsubmit="return validateBankPaymentRequest();">
        <input type="hidden" name="mb_id" value="<?=htmlspecialchars((string) $row['mb_id'], ENT_QUOTES)?>">
        <div class="card-body">
            <div class="row">
                <div class="col-md-2 col-sm-6 mb-2">
                    <label for="bank_depositor_name">입금자명</label>
                    <input type="text" class="form-control" id="bank_depositor_name" name="depositor_name" value="<?=htmlspecialchars((string) $row['mb_name'], ENT_QUOTES)?>">
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <label for="bank_account_id">입금계좌</label>
                    <select class="form-control" id="bank_account_id" name="bank_account_id" <?=count($bank_accounts) < 1 ? 'disabled' : ''?>>
                        <option value=""><?=count($bank_accounts) < 1 ? '등록된 입금계좌 없음' : '선택'?></option>
                        <?php foreach ($bank_accounts as $bank_account) {
                            $bank_account_text = trim(
                                (string) $bank_account['bank_name'].' '.
                                (string) $bank_account['account_number'].' '.
                                (string) $bank_account['account_holder']
                            );
                        ?>
                        <option value="<?=htmlspecialchars((string) $bank_account['lpba_id'], ENT_QUOTES)?>"><?=htmlspecialchars($bank_account_text, ENT_QUOTES)?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <label for="bank_product_type">상품</label>
                    <select class="form-control" id="bank_product_type" name="product_type" onchange="loadBankProductPrice(this.value);">
                        <option value="">선택</option>
                        <?php if (is_array($product_list)) { foreach ($product_list as $product_type) { ?>
                        <option value="<?=htmlspecialchars((string) $product_type, ENT_QUOTES)?>"><?=htmlspecialchars((string) $product_type, ENT_QUOTES)?></option>
                        <?php } } ?>
                    </select>
                </div>
                <div class="col-md-2 col-sm-6 mb-2">
                    <label for="bank_request_amount">입금 예정금액</label>
                    <input type="text" class="form-control" id="bank_request_amount" name="request_amount" inputmode="numeric" placeholder="0">
                </div>
                <div class="col-md-2 col-sm-6 mb-2">
                    <label>입금안내 문자</label>
                    <div class="form-control d-flex align-items-center">
                        <label class="mb-0 mr-3"><input type="radio" name="sms_send" value="1"> 발송</label>
                        <label class="mb-0"><input type="radio" name="sms_send" value="0" checked> 미발송</label>
                    </div>
                </div>
            </div>
            <?php if (count($bank_accounts) < 1) { ?>
            <div class="text-danger">사용 가능한 입금계좌가 등록되어 있지 않습니다. 관리자에게 계좌 등록을 요청하세요.</div>
            <?php } ?>
        </div>
        <div class="card-footer text-right">
            <button type="submit" class="btn btn-success" <?=count($bank_accounts) < 1 ? 'disabled' : ''?>>무통장 승인요청</button>
        </div>
    </form>
</div>


<div class="card card-primary card-outline mt-3 mb-0">
    <div class="card-header">
        <h3 class="card-title">카드 승인요청</h3>
    </div>

    <form id="frm_card_payment_request"
          method="post"
          action="pop.member.card.payment.request.php"
          autocomplete="off"
          onsubmit="return validateCardPaymentRequest();">

        <input type="hidden"
               name="mb_id"
               value="<?=htmlspecialchars((string) $row['mb_id'], ENT_QUOTES)?>">

        <div class="card-body">
            <div class="row">

                <div class="col-md-2 col-sm-6 mb-2">
                    <label for="card_company">카드사</label>
                    <input type="text"
                           class="form-control"
                           id="card_company"
                           name="card_company"
                           maxlength="100"
                           placeholder="직접입력">
                </div>

                <div class="col-md-3 col-sm-6 mb-2">
                    <label for="card_number">카드번호</label>
                    <input type="text"
                           class="form-control"
                           id="card_number"
                           name="card_number"
                           inputmode="numeric"
                           maxlength="19"
                           autocomplete="off"
                           placeholder="숫자 15~16자리">
                </div>

                <div class="col-md-2 col-sm-6 mb-2">
                    <label for="card_expiry">유효기간</label>
                    <input type="text"
                           class="form-control"
                           id="card_expiry"
                           name="card_expiry"
                           inputmode="numeric"
                           maxlength="5"
                           autocomplete="off"
                           placeholder="MM/YY">
                </div>

                <div class="col-md-2 col-sm-6 mb-2">
                    <label for="card_password_prefix">비밀번호 앞 2자리</label>
                    <input type="text"
                           class="form-control"
                           id="card_password_prefix"
                           name="card_password_prefix"
                           inputmode="numeric"
                           maxlength="2"
                           autocomplete="new-password">
                </div>

                <div class="col-md-2 col-sm-6 mb-2">
                    <label for="card_birth_date">생년월일</label>
                    <input type="text"
                           class="form-control"
                           id="card_birth_date"
                           name="birth_date"
                           inputmode="numeric"
                           maxlength="6"
                           autocomplete="new-password"
                           placeholder="YYMMDD">
                </div>

                <div class="col-md-1 col-sm-6 mb-2">
                    <label for="card_installment_months">할부</label>
                    <select class="form-control"
                            id="card_installment_months"
                            name="installment_months">
                        <option value="0">일시불</option>
                        <?php for ($installment = 1; $installment <= 12; $installment++) { ?>
                        <option value="<?=$installment?>"><?=$installment?>개월</option>
                        <?php } ?>
                    </select>
                </div>

            </div>

            <div class="row">

                <div class="col-md-3 col-sm-6 mb-2">
                    <label for="card_product_type">상품</label>
                    <select class="form-control"
                            id="card_product_type"
                            name="product_type"
                            onchange="loadCardProductPrice(this.value);">
                        <option value="">선택</option>

                        <?php if (is_array($product_list)) {
                            foreach ($product_list as $product_type) { ?>
                        <option value="<?=htmlspecialchars((string) $product_type, ENT_QUOTES)?>">
                            <?=htmlspecialchars((string) $product_type, ENT_QUOTES)?>
                        </option>
                        <?php }
                        } ?>
                    </select>
                </div>

                <div class="col-md-3 col-sm-6 mb-2">
                    <label for="card_request_amount">결제 요청금액</label>
                    <input type="text"
                           class="form-control"
                           id="card_request_amount"
                           name="request_amount"
                           inputmode="numeric"
                           placeholder="0">
                </div>

            </div>

            <div class="text-danger text-sm">
                카드번호, 유효기간, 비밀번호 앞 2자리, 생년월일은
                승인 처리를 위해 암호화하여 임시 보관됩니다.
            </div>
        </div>

        <div class="card-footer text-right">
            <button type="submit" class="btn btn-primary">
                카드 승인요청
            </button>
        </div>
    </form>
</div>


<?php if (count($payment_request_rows) > 0) { ?>
<div class="card card-outline card-secondary mt-3 mb-0">
    <div class="card-header">
        <h3 class="card-title">내 결제 승인요청</h3>
    </div>

    <div class="card-body table-responsive p-0">
        <table class="table table-hover text-nowrap text-sm mb-0">
            <thead>
            <tr>
                <th>요청일</th>
                <th>결제수단</th>
                <th>상품</th>
                <th class="text-right">요청금액</th>
                <th>상태</th>
                <th>반려사유</th>
                <th>수정</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($payment_request_rows as $payment_request_row) { ?>
            <tr>
                <td><?=htmlspecialchars((string) $payment_request_row['created_at'], ENT_QUOTES)?></td>
                <td><?=htmlspecialchars((string) $payment_request_row['payment_method'], ENT_QUOTES)?></td>
                <td><?=htmlspecialchars((string) $payment_request_row['product_type'], ENT_QUOTES)?></td>
                <td class="text-right"><?=number_format((int) $payment_request_row['request_amount'])?>원</td>
                <td><?=htmlspecialchars((string) $payment_request_row['request_status'], ENT_QUOTES)?></td>
                <td>
                    <?php if ((string) $payment_request_row['request_status'] === '승인거절') { ?>
                    <span class="text-danger">
                        <?=htmlspecialchars((string) $payment_request_row['reject_reason'], ENT_QUOTES)?>
                    </span>
                    <?php } ?>
                </td>
                <td>
                    <?php if (in_array((string) $payment_request_row['request_status'], array('승인대기', '승인거절'), true)) { ?>
                    <?php
                    $payment_edit_url = (string) $payment_request_row['payment_method'] === '신용카드'
                        ? G5_LADMIN_URL.'/payment/payment.card.request.edit.php'
                        : G5_LADMIN_URL.'/payment/payment.request.edit.php';
                    ?>
                    <a href="<?=$payment_edit_url?>?lpr_id=<?=(int) $payment_request_row['lpr_id']?>"
                       class="btn btn-warning btn-sm">
                        수정
                    </a>
                    <?php } else { ?>
                    -
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<?php } ?>

<script>
function loadCardProductPrice(type){
    if (!type) {
        $('#card_request_amount').val('');
        return;
    }

    $.ajax({
        type: 'POST',
        url: './ajax.getPrice.php',
        data: {type: type},
        cache: false,
        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
        success: function(data){
            $('#card_request_amount').val($.trim(data));
        }
    });
}

function validateCardPaymentRequest(){
    if ($.trim($('#card_company').val()) === '') {
        alert('카드사를 입력하세요.');
        $('#card_company').focus();
        return false;
    }

    var cardNumber = $('#card_number').val().replace(/[^0-9]/g, '');

    if (!/^[0-9]{15,16}$/.test(cardNumber)) {
        alert('카드번호를 15~16자리 숫자로 입력하세요.');
        $('#card_number').focus();
        return false;
    }

    if (!/^(0[1-9]|1[0-2])\/[0-9]{2}$/.test($.trim($('#card_expiry').val()))) {
        alert('유효기간을 MM/YY 형식으로 입력하세요.');
        $('#card_expiry').focus();
        return false;
    }

    if (!/^[0-9]{2}$/.test($('#card_password_prefix').val())) {
        alert('카드 비밀번호 앞 2자리를 입력하세요.');
        $('#card_password_prefix').focus();
        return false;
    }

    if (!/^[0-9]{6}$/.test($('#card_birth_date').val())) {
        alert('생년월일을 6자리로 입력하세요.');
        $('#card_birth_date').focus();
        return false;
    }

    if ($('#card_product_type').val() === '') {
        alert('상품을 선택하세요.');
        $('#card_product_type').focus();
        return false;
    }

    var amount = $('#card_request_amount').val().replace(/[^0-9]/g, '');

    if (amount === '' || parseInt(amount, 10) < 1) {
        alert('결제 요청금액을 입력하세요.');
        $('#card_request_amount').focus();
        return false;
    }

    return confirm('카드 결제 승인요청을 등록하시겠습니까?');
}

function loadBankProductPrice(type){
    if (!type) {
        $('#bank_request_amount').val('');
        return;
    }

    $.ajax({
        type: 'POST',
        url: './ajax.getPrice.php',
        data: {type: type},
        cache: false,
        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
        success: function(data){
            $('#bank_request_amount').val($.trim(data));
        }
    });
}

function validateBankPaymentRequest(){
    if ($.trim($('#bank_depositor_name').val()) === '') {
        alert('입금자명을 입력하세요.');
        $('#bank_depositor_name').focus();
        return false;
    }
    if ($('#bank_account_id').val() === '') {
        alert('입금계좌를 선택하세요.');
        $('#bank_account_id').focus();
        return false;
    }
    if ($('#bank_product_type').val() === '') {
        alert('상품을 선택하세요.');
        $('#bank_product_type').focus();
        return false;
    }
    var amount = $('#bank_request_amount').val().replace(/[^0-9]/g, '');
    if (amount === '' || parseInt(amount, 10) < 1) {
        alert('입금 예정금액을 입력하세요.');
        $('#bank_request_amount').focus();
        return false;
    }
    return confirm('무통장 결제 승인요청을 등록하시겠습니까?');
}
</script>
