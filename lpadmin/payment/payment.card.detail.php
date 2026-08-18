<?php
include_once("_common.php");
include_once(G5_PATH.'/include/lotto_card_security.lib.php');

$login_mb_id = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$login_level = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

$lpr_id = isset($_GET['lpr_id'])
    ? (int) $_GET['lpr_id']
    : 0;

if ($login_mb_id === '' || $login_level < LOTTO_ROLE_ADMIN) {
    echo '<script>alert("관리자 이상만 카드정보를 확인할 수 있습니다.");window.close();</script>';
    exit;
}

if ($lpr_id < 1) {
    echo '<script>alert("결제 승인요청 정보가 올바르지 않습니다.");window.close();</script>';
    exit;
}

$request = sql_fetch(
    "select a.*,
            m.mb_name as member_name,
            m.mb_code as member_code,
            s.mb_name as staff_name,
            r.mb_name as requested_by_name
       from l_payment_request a
       left join g5_member m on m.mb_id = a.mb_id
       left join g5_member s on s.mb_id = a.staff_mb_id
       left join g5_member r on r.mb_id = a.requested_by
      where a.lpr_id = {$lpr_id}
      limit 1",
    false
);

if (empty($request['lpr_id'])) {
    echo '<script>alert("결제 승인요청을 찾을 수 없습니다.");window.close();</script>';
    exit;
}

if ((string) $request['payment_method'] !== '신용카드') {
    echo '<script>alert("신용카드 승인요청이 아닙니다.");window.close();</script>';
    exit;
}

$secret = sql_fetch(
    "select lpcs_id,
            encrypted_payload,
            card_last4,
            key_version,
            created_at
       from l_payment_card_secret
      where lpr_id = {$lpr_id}
      limit 1",
    false
);

if (empty($secret['lpcs_id'])) {
    echo '<script>alert("보관 중인 카드 민감정보가 없습니다.");window.close();</script>';
    exit;
}

try {
    $card_data = lottoCardDecryptPayload(
        (string) $secret['encrypted_payload']
    );
} catch (Throwable $e) {
    echo '<script>alert("카드정보 복호화에 실패했습니다.");window.close();</script>';
    exit;
}

$card_number = isset($card_data['card_number'])
    ? preg_replace('/[^0-9]/', '', (string) $card_data['card_number'])
    : '';

$card_expiry = isset($card_data['card_expiry'])
    ? trim((string) $card_data['card_expiry'])
    : '';

$card_password_prefix = isset($card_data['card_password_prefix'])
    ? trim((string) $card_data['card_password_prefix'])
    : '';

$birth_date = isset($card_data['birth_date'])
    ? trim((string) $card_data['birth_date'])
    : '';

if (
    $card_number === ''
    || $card_expiry === ''
    || $card_password_prefix === ''
    || $birth_date === ''
) {
    echo '<script>alert("복호화된 카드정보가 완전하지 않습니다.");window.close();</script>';
    exit;
}

$card_number_display = trim(
    chunk_split($card_number, 4, ' ')
);

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

include_once(G5_LADMIN_PATH."/head.sub.php");
?>

<section class="content pt-2">
    <div class="container-fluid">

        <div class="card card-danger">

            <div class="card-header py-2">
                <h3 class="card-title">
                    관리자 카드결제 정보
                </h3>
            </div>

            <div class="card-body">

                <div class="alert alert-warning py-2">
                    카드결제 처리용 민감정보입니다.
                    결제 업무 외 용도로 저장·복사하지 마세요.
                </div>

                <div class="row">

                    <div class="col-md-6">

                        <div class="card card-outline card-secondary h-100">
                            <div class="card-header py-2">
                                <strong>결제 요청정보</strong>
                            </div>

                            <div class="card-body">

                                <dl class="row mb-0">

                                    <dt class="col-sm-4">회원</dt>
                                    <dd class="col-sm-8">
                                        <?=htmlspecialchars(
                                            (string) ($request['member_name'] ?: $request['mb_id']),
                                            ENT_QUOTES
                                        )?>
                                        /
                                        <?=htmlspecialchars(
                                            (string) ($request['member_code'] ?: $request['mb_id']),
                                            ENT_QUOTES
                                        )?>
                                    </dd>

                                    <dt class="col-sm-4">요청자</dt>
                                    <dd class="col-sm-8">
                                        <?=htmlspecialchars(
                                            (string) ($request['requested_by_name'] ?: $request['requested_by']),
                                            ENT_QUOTES
                                        )?>
                                    </dd>

                                    <dt class="col-sm-4">카드사</dt>
                                    <dd class="col-sm-8">
                                        <?=htmlspecialchars(
                                            (string) $request['card_company'],
                                            ENT_QUOTES
                                        )?>
                                    </dd>

                                    <dt class="col-sm-4">상품</dt>
                                    <dd class="col-sm-8">
                                        <?=htmlspecialchars(
                                            (string) $request['product_type'],
                                            ENT_QUOTES
                                        )?>
                                    </dd>

                                    <dt class="col-sm-4">결제금액</dt>
                                    <dd class="col-sm-8">
                                        <?=number_format(
                                            (int) $request['request_amount']
                                        )?>원
                                    </dd>

                                    <dt class="col-sm-4">할부</dt>
                                    <dd class="col-sm-8">
                                        <?php if ((int) $request['installment_months'] === 0) { ?>
                                            일시불
                                        <?php } else { ?>
                                            <?=(int) $request['installment_months']?>개월
                                        <?php } ?>
                                    </dd>

                                </dl>

                            </div>
                        </div>

                    </div>

                    <div class="col-md-6">

                        <div class="card card-outline card-danger h-100">
                            <div class="card-header py-2">
                                <strong>카드 / 개인정보</strong>
                            </div>

                            <div class="card-body">

                                <div class="form-group">
                                    <label>카드번호</label>
                                    <input type="text"
                                           class="form-control"
                                           value="<?=htmlspecialchars(
                                               $card_number_display,
                                               ENT_QUOTES
                                           )?>"
                                           readonly>
                                </div>

                                <div class="row">

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>유효기간</label>
                                            <input type="text"
                                                   class="form-control"
                                                   value="<?=htmlspecialchars(
                                                       $card_expiry,
                                                       ENT_QUOTES
                                                   )?>"
                                                   readonly>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>비밀번호 앞 2자리</label>
                                            <input type="text"
                                                   class="form-control"
                                                   value="<?=htmlspecialchars(
                                                       $card_password_prefix,
                                                       ENT_QUOTES
                                                   )?>"
                                                   readonly>
                                        </div>
                                    </div>

                                </div>

                                <div class="form-group mb-0">
                                    <label>생년월일 6자리</label>
                                    <input type="text"
                                           class="form-control"
                                           value="<?=htmlspecialchars(
                                               $birth_date,
                                               ENT_QUOTES
                                           )?>"
                                           readonly>
                                </div>

                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <div class="card-footer text-right">

                <?php if ((string) $request['request_status'] === '승인대기') { ?>
                <form method="post"
                      action="<?=G5_LADMIN_URL?>/payment/payment.approval.update.php"
                      class="d-inline-block mr-2"
                      onsubmit="return confirm('실제 카드결제가 완료되었습니까? 승인완료 후 카드 민감정보는 삭제됩니다.');">

                    <input type="hidden"
                           name="lpr_id"
                           value="<?=(int) $request['lpr_id']?>">

                    <input type="hidden"
                           name="from_card_detail"
                           value="1">

                    <button type="submit"
                            class="btn btn-primary">
                        승인완료
                    </button>
                </form>
                <?php } ?>

                <button type="button"
                        class="btn btn-secondary"
                        onclick="window.close();">
                    닫기
                </button>

            </div>

        </div>

    </div>
</section>
