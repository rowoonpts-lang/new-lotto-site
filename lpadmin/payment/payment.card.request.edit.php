<?php
include_once("_common.php");
include_once(G5_LADMIN_PATH."/head.sub.php");

$login_mb_id = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$login_level = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

$lpr_id = isset($_GET['lpr_id'])
    ? (int) $_GET['lpr_id']
    : 0;

if ($login_mb_id === '' || $login_level >= LOTTO_ROLE_ADMIN) {
    echo '<script>alert("결제 승인요청 수정 권한이 없습니다.");window.close();</script>';
    exit;
}

if ($lpr_id < 1) {
    echo '<script>alert("결제 승인요청 정보가 올바르지 않습니다.");window.close();</script>';
    exit;
}

$request = sql_fetch(
    "select a.*,
            m.mb_name as member_name,
            m.mb_code as member_code
       from l_payment_request a
       left join g5_member m on m.mb_id = a.mb_id
      where a.lpr_id = {$lpr_id}
      limit 1",
    false
);

if (empty($request['lpr_id'])) {
    echo '<script>alert("결제 승인요청을 찾을 수 없습니다.");window.close();</script>';
    exit;
}

if ((string) $request['requested_by'] !== $login_mb_id) {
    echo '<script>alert("본인이 등록한 결제 승인요청만 수정할 수 있습니다.");window.close();</script>';
    exit;
}

if (!in_array(
    (string) $request['request_status'],
    array('승인대기', '승인거절'),
    true
)) {
    echo '<script>alert("현재 상태에서는 결제 승인요청을 수정할 수 없습니다.");window.close();</script>';
    exit;
}

if ((string) $request['payment_method'] !== '신용카드') {
    echo '<script>alert("신용카드 승인요청이 아닙니다.");window.close();</script>';
    exit;
}

$secret = sql_fetch(
    "select lpcs_id, card_last4
       from l_payment_card_secret
      where lpr_id = {$lpr_id}
      limit 1",
    false
);

$has_card_secret = !empty($secret['lpcs_id']);
$product_list = fnGetTypePre();
?>

<section class="content pt-2">
    <div class="container-fluid">
        <div class="card card-warning mb-2">

            <div class="card-header py-2">
                <h3 class="card-title">카드 결제 승인요청 수정</h3>
            </div>

            <form method="post"
                  action="<?=G5_LADMIN_URL?>/payment/payment.card.request.update.php"
                  autocomplete="off"
                  onsubmit="return validateCardRequestEdit();">

                <input type="hidden"
                       name="lpr_id"
                       value="<?=(int) $request['lpr_id']?>">

                <div class="card-body py-3">

                    <div class="row mb-2">
                        <div class="col-md-6">
                            <strong>회원</strong>
                            <span class="ml-2">
                                <?=htmlspecialchars(
                                    (string) ($request['member_name'] ?: $request['mb_id']),
                                    ENT_QUOTES
                                )?>
                                /
                                <?=htmlspecialchars(
                                    (string) ($request['member_code'] ?: $request['mb_id']),
                                    ENT_QUOTES
                                )?>
                            </span>
                        </div>

                        <div class="col-md-6">
                            <strong>결제수단</strong>
                            <span class="ml-2">신용카드</span>
                        </div>
                    </div>

                    <?php if ((string) $request['request_status'] === '승인거절') { ?>
                    <div class="alert alert-danger py-2 mb-3">
                        <strong>반려사유:</strong>
                        <?=htmlspecialchars((string) $request['reject_reason'], ENT_QUOTES)?>
                    </div>
                    <?php } ?>

                    <div class="row">

                        <div class="col-md-6 pr-md-3">

                            <div class="card card-outline card-secondary h-100 mb-0">
                                <div class="card-header py-2">
                                    <strong>결제정보</strong>
                                </div>

                                <div class="card-body py-3">

                                    <div class="form-group mb-3">
                                        <label for="card_company">카드사</label>
                                        <input type="text"
                                               class="form-control"
                                               id="card_company"
                                               name="card_company"
                                               maxlength="100"
                                               value="<?=htmlspecialchars(
                                                   (string) $request['card_company'],
                                                   ENT_QUOTES
                                               )?>"
                                               required>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="product_type">상품</label>
                                        <select class="form-control"
                                                id="product_type"
                                                name="product_type"
                                                required>
                                            <option value="">선택</option>

                                            <?php if (is_array($product_list)) {
                                                foreach ($product_list as $product_type) { ?>
                                            <option
                                                value="<?=htmlspecialchars(
                                                    (string) $product_type,
                                                    ENT_QUOTES
                                                )?>"
                                                <?=(string) $request['product_type'] === (string) $product_type
                                                    ? ' selected'
                                                    : ''?>>
                                                <?=htmlspecialchars(
                                                    (string) $product_type,
                                                    ENT_QUOTES
                                                )?>
                                            </option>
                                            <?php }
                                            } ?>
                                        </select>
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="request_amount">결제 요청금액</label>
                                        <input type="text"
                                               class="form-control"
                                               id="request_amount"
                                               name="request_amount"
                                               inputmode="numeric"
                                               value="<?=number_format(
                                                   (int) $request['request_amount']
                                               )?>"
                                               required>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label for="installment_months">할부</label>
                                        <select class="form-control"
                                                id="installment_months"
                                                name="installment_months">

                                            <option value="0"
                                                <?=(int) $request['installment_months'] === 0
                                                    ? ' selected'
                                                    : ''?>>
                                                일시불
                                            </option>

                                            <?php for ($month = 1; $month <= 12; $month++) { ?>
                                            <option value="<?=$month?>"
                                                <?=(int) $request['installment_months'] === $month
                                                    ? ' selected'
                                                    : ''?>>
                                                <?=$month?>개월
                                            </option>
                                            <?php } ?>

                                        </select>
                                    </div>

                                </div>
                            </div>

                        </div>

                        <div class="col-md-6 pl-md-3 mt-3 mt-md-0">

                            <div class="card card-outline card-info h-100 mb-0">
                                <div class="card-header py-2">
                                    <strong>카드정보</strong>
                                </div>

                                <div class="card-body py-3">

                                    <?php if ($has_card_secret) { ?>
                                    <div class="alert alert-info py-2">
                                        기존 카드정보가 암호화되어 저장되어 있습니다.
                                        카드번호 끝 4자리:
                                        <strong><?=htmlspecialchars(
                                            (string) $secret['card_last4'],
                                            ENT_QUOTES
                                        )?></strong><br>
                                        <small>
                                            카드정보를 변경하지 않으면 아래 입력칸은 비워두세요.
                                        </small>
                                    </div>
                                    <?php } else { ?>
                                    <div class="alert alert-warning py-2">
                                        반려 처리로 기존 카드정보가 삭제되었습니다.<br>
                                        다시 요청하려면 아래 카드정보를 모두 입력하세요.
                                    </div>
                                    <?php } ?>

                                    <div class="form-group mb-3">
                                        <label for="card_number">카드번호</label>
                                        <input type="text"
                                               class="form-control"
                                               id="card_number"
                                               name="card_number"
                                               inputmode="numeric"
                                               maxlength="19"
                                               autocomplete="new-password"
                                               placeholder="<?=$has_card_secret
                                                   ? '변경할 때만 입력'
                                                   : '숫자 15~16자리'?>">
                                    </div>

                                    <div class="row">

                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="card_expiry">
                                                    유효기간
                                                </label>
                                                <input type="text"
                                                       class="form-control"
                                                       id="card_expiry"
                                                       name="card_expiry"
                                                       inputmode="numeric"
                                                       maxlength="5"
                                                       autocomplete="new-password"
                                                       placeholder="<?=$has_card_secret
                                                           ? '변경할 때만 MM/YY'
                                                           : 'MM/YY'?>">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label for="card_password_prefix">
                                                    비밀번호 앞 2자리
                                                </label>
                                                <input type="text"
                                                       class="form-control"
                                                       id="card_password_prefix"
                                                       name="card_password_prefix"
                                                       inputmode="numeric"
                                                       maxlength="2"
                                                       autocomplete="new-password">
                                            </div>
                                        </div>

                                    </div>

                                    <div class="form-group mb-0">
                                        <label for="birth_date">생년월일</label>
                                        <input type="text"
                                               class="form-control"
                                               id="birth_date"
                                               name="birth_date"
                                               inputmode="numeric"
                                               maxlength="6"
                                               autocomplete="new-password"
                                               placeholder="<?=$has_card_secret
                                                   ? '변경할 때만 YYMMDD'
                                                   : 'YYMMDD'?>">
                                    </div>

                                </div>
                            </div>

                        </div>

                    </div>

                </div>

                <div class="card-footer text-right py-2">
                    <button type="button"
                            class="btn btn-secondary"
                            onclick="window.close();">
                        취소
                    </button>

                    <button type="submit"
                            class="btn btn-warning">
                        수정 후 승인요청
                    </button>
                </div>

            </form>
        </div>
    </div>
</section>

<script>
function validateCardRequestEdit() {
    var cardNumber = $('#card_number').val().replace(/[^0-9]/g, '');
    var expiry = $.trim($('#card_expiry').val());
    var passwordPrefix = $('#card_password_prefix').val();
    var birthDate = $('#birth_date').val();

    var enteredCount = 0;

    if (cardNumber !== '') {
        enteredCount++;
    }

    if (expiry !== '') {
        enteredCount++;
    }

    if (passwordPrefix !== '') {
        enteredCount++;
    }

    if (birthDate !== '') {
        enteredCount++;
    }

    if (enteredCount > 0 && enteredCount < 4) {
        alert(
            '카드정보를 변경하려면 카드번호, 유효기간, ' +
            '비밀번호 앞 2자리, 생년월일을 모두 입력하세요.'
        );
        return false;
    }

    if (enteredCount === 4) {
        if (!/^[0-9]{15,16}$/.test(cardNumber)) {
            alert('카드번호를 15~16자리 숫자로 입력하세요.');
            $('#card_number').focus();
            return false;
        }

        if (!/^(0[1-9]|1[0-2])\/[0-9]{2}$/.test(expiry)) {
            alert('유효기간을 MM/YY 형식으로 입력하세요.');
            $('#card_expiry').focus();
            return false;
        }

        if (!/^[0-9]{2}$/.test(passwordPrefix)) {
            alert('카드 비밀번호 앞 2자리를 입력하세요.');
            $('#card_password_prefix').focus();
            return false;
        }

        if (!/^[0-9]{6}$/.test(birthDate)) {
            alert('생년월일을 6자리로 입력하세요.');
            $('#birth_date').focus();
            return false;
        }
    }

    return confirm('수정한 내용으로 다시 승인요청 하시겠습니까?');
}
</script>
