<?php
include_once("_common.php");

$login_mb_id = isset($member['mb_id']) ? trim((string) $member['mb_id']) : '';
$login_level = isset($member['mb_level']) ? (int) $member['mb_level'] : 0;
$lpr_id = isset($_GET['lpr_id']) ? (int) $_GET['lpr_id'] : 0;

if ($login_mb_id === '' || $login_level >= LOTTO_ROLE_ADMIN) {
    alert('결제 승인요청 수정 권한이 없습니다.', G5_LADMIN_URL);
    exit;
}

if ($lpr_id < 1) {
    alert('결제 승인요청 정보가 올바르지 않습니다.', G5_LADMIN_URL);
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
    alert('결제 승인요청을 찾을 수 없습니다.', G5_LADMIN_URL);
    exit;
}

if ((string) $request['requested_by'] !== $login_mb_id) {
    alert('본인이 등록한 결제 승인요청만 수정할 수 있습니다.', G5_LADMIN_URL);
    exit;
}

if (!in_array((string) $request['request_status'], array('승인대기', '승인거절'), true)) {
    alert('현재 상태에서는 결제 승인요청을 수정할 수 없습니다.', G5_LADMIN_URL);
    exit;
}

if ((string) $request['payment_method'] !== '무통장') {
    alert('현재 수정 화면은 무통장 승인요청만 지원합니다.', G5_LADMIN_URL);
    exit;
}

$bank_accounts = array();
$bank_result = sql_query(
    "select lpba_id, bank_name, account_number, account_holder
       from l_payment_bank_account
      where is_active = 1
      order by sort_order asc, lpba_id asc",
    false
);

if ($bank_result) {
    while ($bank_row = sql_fetch_array($bank_result)) {
        $bank_accounts[] = $bank_row;
    }
}

$product_list = fnGetTypePre();

include_once(G5_LADMIN_PATH."/head.php");
?>

<div class="card card-warning">
    <div class="card-header">
        <h3 class="card-title">결제 승인요청 수정</h3>
    </div>

    <form method="post"
          action="<?=G5_LADMIN_URL?>/payment/payment.request.update.php"
          autocomplete="off">
        <input type="hidden" name="lpr_id" value="<?=(int) $request['lpr_id']?>">

        <div class="card-body">
            <div class="form-group">
                <label>회원</label>
                <div>
                    <?=htmlspecialchars((string) ($request['member_name'] ?: $request['mb_id']), ENT_QUOTES)?>
                    /
                    <?=htmlspecialchars((string) ($request['member_code'] ?: $request['mb_id']), ENT_QUOTES)?>
                </div>
            </div>

            <div class="form-group">
                <label>결제수단</label>
                <div><?=htmlspecialchars((string) $request['payment_method'], ENT_QUOTES)?></div>
            </div>

            <?php if ((string) $request['request_status'] === '승인거절') { ?>
            <div class="alert alert-danger">
                <strong>반려사유:</strong>
                <?=htmlspecialchars((string) $request['reject_reason'], ENT_QUOTES)?>
            </div>
            <?php } ?>

            <div class="form-group">
                <label for="product_type">상품</label>
                <select class="form-control" id="product_type" name="product_type" required>
                    <option value="">선택</option>
                    <?php if (is_array($product_list)) { ?>
                        <?php foreach ($product_list as $product_type) { ?>
                        <option value="<?=htmlspecialchars((string) $product_type, ENT_QUOTES)?>"
                            <?=(string) $request['product_type'] === (string) $product_type ? ' selected' : ''?>>
                            <?=htmlspecialchars((string) $product_type, ENT_QUOTES)?>
                        </option>
                        <?php } ?>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label for="request_amount">입금 예정금액</label>
                <input type="text"
                       class="form-control"
                       id="request_amount"
                       name="request_amount"
                       inputmode="numeric"
                       value="<?=number_format((int) $request['request_amount'])?>"
                       required>
            </div>

            <div class="form-group">
                <label for="depositor_name">입금자명</label>
                <input type="text"
                       class="form-control"
                       id="depositor_name"
                       name="depositor_name"
                       value="<?=htmlspecialchars((string) $request['depositor_name'], ENT_QUOTES)?>"
                       required>
            </div>

            <div class="form-group">
                <label for="bank_account_id">입금계좌</label>
                <select class="form-control" id="bank_account_id" name="bank_account_id" required>
                    <option value="">선택</option>

                    <?php foreach ($bank_accounts as $bank_account) {
                        $bank_text = trim(
                            (string) $bank_account['bank_name'].' '.
                            (string) $bank_account['account_number'].' '.
                            (string) $bank_account['account_holder']
                        );
                    ?>
                    <option value="<?=(int) $bank_account['lpba_id']?>"
                        <?=$bank_text === (string) $request['bank_account'] ? ' selected' : ''?>>
                        <?=htmlspecialchars($bank_text, ENT_QUOTES)?>
                    </option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="card-footer text-right">
            <a href="<?=G5_LADMIN_URL?>/member/pop.member.php?mb_id=<?=urlencode(base64_encode((string) $request['mb_id']))?>"
               class="btn btn-secondary">취소</a>
            <button type="submit" class="btn btn-warning"
                    onclick="return confirm('수정한 내용으로 다시 승인요청 하시겠습니까?');">
                수정 후 승인요청
            </button>
        </div>
    </form>
</div>

<?php include_once(G5_LADMIN_PATH."/tail.php"); ?>
