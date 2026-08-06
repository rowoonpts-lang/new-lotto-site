<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$top_h2 = isset($top_h2) ? (string) $top_h2 : '';

if (G5_IS_MOBILE) {
    include_once(G5_THEME_MOBILE_PATH.'/tail.php');
    return;
}

if($basename != "index.php") {
?>
	</div>
</div>
<?php } ?>



<footer class="footer lg-footer">
    <div class="lg-footer-shell">
        <div class="lg-footer-main">
            <div class="lg-footer-brand">
                <a href="<?=G5_URL?>" class="lg-footer-logo">Lotto<span>GPT</span></a>
                <p>
                    누적 로또 데이터와 통계 정보를 사용자가 쉽게 확인할 수 있도록
                    정리하는 AI 기반 로또 분석 플랫폼입니다.
                </p>
                <div class="lg-footer-status">
                    <span></span>
                    LOTTOGPT SYSTEM ONLINE
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
                <p>평일·토요일 10:00 ~ 18:00</p>
                <p>일요일 및 공휴일 휴무</p>
                <a href="mailto:lottojoongsim@gmail.com">lottojoongsim@gmail.com</a>
            </div>
        </div>

        <div class="lg-footer-company">
            <strong>지오인터내셔널</strong>
            <span>대표이사 김민지</span>
            <span>사업자등록번호 350-04-01576</span>
            <span>통신판매업신고번호 2020-인천남동구-1821</span>
            <span>개인정보책임자 김민지</span>
            <span>인천광역시 남동구 남동대로 777번길 43, 5층</span>
        </div>

        <div class="lg-footer-notice">
            LottoGPT의 분석 정보는 로또 번호 조합과 통계 정보를 제공하기 위한 참고 자료이며,
            당첨을 보장하거나 확정하는 서비스가 아닙니다. 서비스 이용에 따른 최종 판단과 책임은
            이용자 본인에게 있습니다.
        </div>

        <div class="lg-footer-bottom">
            <span>Copyright © <?=date('Y')?> GIO INTERNATIONAL. All rights reserved.</span>
            <span>LOTTOGPT · DATA DRIVEN LOTTO PLATFORM</span>
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
