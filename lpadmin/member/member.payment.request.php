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

<script>
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
