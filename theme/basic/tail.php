<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

include_once(G5_PATH.'/include/lotto_site_config.lib.php');
$site_config = lottoGetSiteConfig();

$footer_brand_name = (string) $site_config['brand_name'];
$footer_brand_prefix = $footer_brand_name;
$footer_brand_suffix = '';

if (strlen($footer_brand_name) >= 3 && strcasecmp(substr($footer_brand_name, -3), 'GPT') === 0) {
    $footer_brand_prefix = substr($footer_brand_name, 0, -3);
    $footer_brand_suffix = substr($footer_brand_name, -3);
}

$top_h2 = isset($top_h2) ? (string) $top_h2 : '';

if (G5_IS_MOBILE) {
    include_once(G5_THEME_MOBILE_PATH.'/tail.php');
    return;
}

if ($basename != "index.php" && empty($lottogpt_full_width_page)) {
?>
	</div>
</div>
<?php } ?>



<footer class="footer lg-footer">
    <div class="lg-footer-shell">
        <div class="lg-footer-main">
            <div class="lg-footer-brand">
                <a href="<?=G5_URL?>" class="lg-footer-logo"><?=htmlspecialchars($footer_brand_prefix, ENT_QUOTES)?><?php if ($footer_brand_suffix !== '') { ?><span><?=htmlspecialchars($footer_brand_suffix, ENT_QUOTES)?></span><?php } ?></a>
                <p>
                    누적 로또 데이터와 통계 정보를 사용자가 쉽게 확인할 수 있도록
                    정리하는 AI 기반 로또 분석 플랫폼입니다.
                </p>
                <div class="lg-footer-status">
                    <span></span>
                    <?=htmlspecialchars($site_config['brand_name'], ENT_QUOTES)?> SYSTEM ONLINE
                </div>
            </div>

            <div class="lg-footer-links">
                <strong>서비스</strong>
                <a href="<?=G5_URL?>">홈</a>
                <a href="<?=G5_URL?>/sub/sub0102.php">회사소개</a>
                <a href="<?=G5_URL?>/sub/stats.php">데이터랩</a>
                <a href="<?=G5_BBS_URL?>/board.php?bo_table=notice">공지사항</a>
            </div>

            <div class="lg-footer-links">
                <strong>정책</strong>
                <a href="<?=G5_BBS_URL?>/content.php?co_id=provision">이용약관</a>
                <a href="<?=G5_BBS_URL?>/content.php?co_id=privacy">개인정보처리방침</a>
            </div>

            <div class="lg-footer-contact">
                <strong>CONTACT</strong>
                <?php if ($site_config['contact_hours'] !== '') { ?>
                <p><?=htmlspecialchars($site_config['contact_hours'], ENT_QUOTES)?></p>
                <?php } ?>
                <?php if ($site_config['contact_closed'] !== '') { ?>
                <p><?=htmlspecialchars($site_config['contact_closed'], ENT_QUOTES)?></p>
                <?php } ?>
                <?php if ($site_config['contact_phone'] !== '') { ?>
                <p><?=htmlspecialchars($site_config['contact_phone'], ENT_QUOTES)?></p>
                <?php } ?>
                <?php if ($site_config['contact_email'] !== '') { ?>
                <a href="mailto:<?=htmlspecialchars($site_config['contact_email'], ENT_QUOTES)?>"><?=htmlspecialchars($site_config['contact_email'], ENT_QUOTES)?></a>
                <?php } ?>
            </div>
        </div>

        <div class="lg-footer-company">
            <strong><?=htmlspecialchars($site_config['company_name'], ENT_QUOTES)?></strong>
            <?php if ($site_config['representative_name'] !== '') { ?>
            <span>대표이사 <?=htmlspecialchars($site_config['representative_name'], ENT_QUOTES)?></span>
            <?php } ?>
            <?php if ($site_config['business_number'] !== '') { ?>
            <span>사업자등록번호 <?=htmlspecialchars($site_config['business_number'], ENT_QUOTES)?></span>
            <?php } ?>
            <?php if ($site_config['mail_order_number'] !== '') { ?>
            <span>통신판매업신고번호 <?=htmlspecialchars($site_config['mail_order_number'], ENT_QUOTES)?></span>
            <?php } ?>
            <?php if ($site_config['privacy_manager'] !== '') { ?>
            <span>개인정보책임자 <?=htmlspecialchars($site_config['privacy_manager'], ENT_QUOTES)?></span>
            <?php } ?>
            <?php if ($site_config['company_address'] !== '') { ?>
            <span><?=htmlspecialchars($site_config['company_address'], ENT_QUOTES)?></span>
            <?php } ?>
        </div>

        <div class="lg-footer-notice">
            <?=nl2br(htmlspecialchars($site_config['common_notice'], ENT_QUOTES))?>
        </div>

        <div class="lg-footer-bottom">
            <span>Copyright © <?=date('Y')?> <?=htmlspecialchars($site_config['copyright_name'], ENT_QUOTES)?>. All rights reserved.</span>
            <span><?=htmlspecialchars($site_config['brand_name'], ENT_QUOTES)?> · DATA DRIVEN LOTTO PLATFORM</span>
        </div>
    </div>

    <button type="button" id="top_btn" aria-label="페이지 맨 위로 이동">
        <i class="fas fa-chevron-up"></i>
    </button>

    <script>
    $(function() {
        $("#top_btn").on("click", function() {
            $("html, body").animate({scrollTop: 0}, 500);
            return false;
        });
    });
    </script>
</footer>

<script>
function fnCngTurn(v, ver){
	$.ajax({
		type: "POST",
		url: "<?=G5_URL?>/sub/ajax.turn.list.view.php",
		data: {turn : v, ver : ver},
		cache: false,
		async: false,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {
			$("#view_turn_result").html(data);
		}
	});
	return false;
}
</script>

<script>
function fnCngTurn2(v, ver){
	$.ajax({
		type: "POST",
		url: "<?=G5_URL?>/sub/ajax.turn.list.view2.php",
		data: {turn : v, ver : ver},
		cache: false,
		async: false,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {
			$("#view_turn_result").html(data);
		}
	});
	return false;
}
</script>



<?php
if(G5_DEVICE_BUTTON_DISPLAY && !G5_IS_MOBILE) { ?>
<?php
}

if ($config['cf_analytics']) {
    echo $config['cf_analytics'];
}
?>

<!-- } 하단 끝 -->
<script src="<?=G5_JS_URL?>/aos.js"></script>
<script>
  AOS.init({
	easing: 'ease'
  });
</script>
<script>
$(function() {
    // 폰트 리사이즈 쿠키있으면 실행
    font_resize("container", get_cookie("ck_font_resize_rmv_class"), get_cookie("ck_font_resize_add_class"));
});
</script>

<script>
$(function(){
	$(window).scroll(function(event){
		var scr_top = $(document).scrollTop();
		if('<?=$top_h2?>' == "Our History"){
			var history_tab = $('.history_tab').offset().top;
			if(scr_top >= history_tab-115){
				$('.history_tab_wrap').addClass('on');
			}else{
				$('.history_tab_wrap').removeClass('on');
			}
		}
	});
});
</script>


<?php
include_once(G5_THEME_PATH."/tail.sub.php");
?>
