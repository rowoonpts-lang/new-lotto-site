<?php
include_once("_common.php");

$login_level = isset($member['mb_level']) ? (int) $member['mb_level'] : 0;

if ($login_level < LOTTO_ROLE_ADMIN) {
    alert('관리자 이상만 접근할 수 있습니다.', G5_LADMIN_URL);
    exit;
}

$site_config = sql_fetch("
    select *
      from l_site_config
     where lsc_id = 1
     limit 1
", false);

if (empty($site_config['lsc_id'])) {
    alert('홈페이지 설정 정보를 찾을 수 없습니다.', G5_LADMIN_URL);
    exit;
}

$token = lottoConfigTokenCreate();

include_once(G5_LADMIN_PATH."/head.php");

function site_config_html($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES);
}
?>

<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">홈페이지 설정</h3>
            </div>

            <form method="post" action="site.config.update.php" autocomplete="off">
                <input type="hidden" name="token" value="<?=site_config_html($token)?>">

                <div class="card-body">

                    <h5 class="mb-3">브랜드 정보</h5>

                    <div class="form-group">
                        <label for="brand_name">브랜드명</label>
                        <input
                            type="text"
                            class="form-control"
                            id="brand_name"
                            name="brand_name"
                            maxlength="100"
                            value="<?=site_config_html($site_config['brand_name'])?>"
                            required
                        >
                    </div>

                    <hr>

                    <h5 class="mb-3">회사 정보</h5>

                    <div class="form-group">
                        <label for="company_name">회사명</label>
                        <input
                            type="text"
                            class="form-control"
                            id="company_name"
                            name="company_name"
                            maxlength="150"
                            value="<?=site_config_html($site_config['company_name'])?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="representative_name">대표자명</label>
                        <input
                            type="text"
                            class="form-control"
                            id="representative_name"
                            name="representative_name"
                            maxlength="100"
                            value="<?=site_config_html($site_config['representative_name'])?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="business_number">사업자등록번호</label>
                        <input
                            type="text"
                            class="form-control"
                            id="business_number"
                            name="business_number"
                            maxlength="50"
                            value="<?=site_config_html($site_config['business_number'])?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="mail_order_number">통신판매업신고번호</label>
                        <input
                            type="text"
                            class="form-control"
                            id="mail_order_number"
                            name="mail_order_number"
                            maxlength="100"
                            value="<?=site_config_html($site_config['mail_order_number'])?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="privacy_manager">개인정보책임자</label>
                        <input
                            type="text"
                            class="form-control"
                            id="privacy_manager"
                            name="privacy_manager"
                            maxlength="100"
                            value="<?=site_config_html($site_config['privacy_manager'])?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="company_address">회사 주소</label>
                        <input
                            type="text"
                            class="form-control"
                            id="company_address"
                            name="company_address"
                            maxlength="255"
                            value="<?=site_config_html($site_config['company_address'])?>"
                        >
                    </div>

                    <hr>

                    <h5 class="mb-3">연락처</h5>

                    <div class="form-group">
                        <label for="contact_phone">전화번호</label>
                        <input
                            type="text"
                            class="form-control"
                            id="contact_phone"
                            name="contact_phone"
                            maxlength="50"
                            value="<?=site_config_html($site_config['contact_phone'])?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="contact_email">이메일</label>
                        <input
                            type="email"
                            class="form-control"
                            id="contact_email"
                            name="contact_email"
                            maxlength="150"
                            value="<?=site_config_html($site_config['contact_email'])?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="contact_hours">고객센터 운영시간</label>
                        <input
                            type="text"
                            class="form-control"
                            id="contact_hours"
                            name="contact_hours"
                            maxlength="255"
                            value="<?=site_config_html($site_config['contact_hours'])?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="contact_closed">휴무 안내</label>
                        <input
                            type="text"
                            class="form-control"
                            id="contact_closed"
                            name="contact_closed"
                            maxlength="255"
                            value="<?=site_config_html($site_config['contact_closed'])?>"
                        >
                    </div>

                    <hr>

                    <h5 class="mb-3">홈페이지 공통 문구</h5>

                    <div class="form-group">
                        <label for="common_notice">공통 안내문</label>
                        <textarea
                            class="form-control"
                            id="common_notice"
                            name="common_notice"
                            rows="5"
                        ><?=site_config_html($site_config['common_notice'])?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="copyright_name">Copyright 회사명</label>
                        <input
                            type="text"
                            class="form-control"
                            id="copyright_name"
                            name="copyright_name"
                            maxlength="150"
                            value="<?=site_config_html($site_config['copyright_name'])?>"
                        >
                    </div>

                </div>

                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">설정 저장</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include_once(G5_LADMIN_PATH."/tail.php");
?>
