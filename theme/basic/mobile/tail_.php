<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if($basename != "index.php") {
?>
	</div>
</div>
<?
	}
?>




<footer class="footer">
		<div class="footer_top">
			<div class="inner_2">
				<ul>
					<li><a href="<?=G5_URL?>/sub/sub0101.php">HOME</a></li>
					<li><a href="<?=G5_URL?>/sub/sub0102.php">회사소개</a></li>
					<li><a href="<?=G5_BBS_URL?>/content.php?co_id=provision">이용약관</a></li>
					<li><a href="<?=G5_BBS_URL?>/content.php?co_id=privacy">개인정보처리방침</a></li>
				</ul>
			</div>
		</div><!-- footer_top 끝 -->
		<div class="inner_2">
			<div class="footer_bottom">
				 <ul class="f_l">
					<li><b>지오인터내셔널</b></li>
					<li><span>대표이사 김민지</span><span>사업자등록번호 350-04-01576</span><span>통신판매업신고번호 2020-인천남동구-1821</span></li>
					<li><span>고객센터 인천광역시 남동구 남동대로 777번길 43, 5층 (구월동, 유진빌딩)</span></li>
					<li><span>개인정보책임자 김민지</span><span>이메일 lottojoongsim@gmail.com</span></li>
					<li><a href="<?=G5_URL?>"><img src="<?=G5_THEME_IMG_URL?>/logo_f.png" alt=""></a>&nbsp;&nbsp;&nbsp;&nbsp;Copyright (c) 2020 GIO INTERNATIONAL COLORS CO, LTD. All right reserved.</li>
				 </ul>	
				 <ul class="f_r">
					<li><b>CONTACT US</b></li>
					<li>평일·토요일 10:00 ~ 18:00</li>
					<li>일요일 및 공휴일 휴무</li>
				 </ul>
				 <div class="f_r_abs">당사의 분석시스템은 전체 로또번호 조합 중 등급별 압축 필터링한 조합 정보제공만을 목적으로 하며, 당첨 확정 서비스가 아니므로 서비스 이용 과정에서 기대이익을 얻지 못하거나 발생한 손해 등에 대한 최종책임은 서비스 이용자 본인에게 있습니다
				 </div>
				 
			</div><!-- footer_bottom 끝 -->
		</div>

	
		<button type="button" id="top_btn"></button>
        <script>
        
        $(function() {
            $("#top_btn").on("click", function() {
                $("html, body").animate({scrollTop:0}, '500');
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

<script src="<?=G5_JS_URL?>/aos.js"></script>
<script>
  AOS.init({
	easing: 'ease'
  });
</script>

<script>
$(function(){
	$(window).scroll(function(event){
		var scr_top = $(document).scrollTop();
		if('<?=$top_h2?>' == "Our History"){
			var history_tab = $('.history_tab').offset().top;
			if(scr_top >= history_tab-50){
				$('.history_tab_wrap').addClass('on');
			}else{
				$('.history_tab_wrap').removeClass('on');
			}
		}
	});
});
</script>

<script>
jQuery(function($) {

    $( document ).ready( function() {

        // 폰트 리사이즈 쿠키있으면 실행
        font_resize("container", get_cookie("ck_font_resize_rmv_class"), get_cookie("ck_font_resize_add_class"));
        
        //상단고정
        if( $(".top").length ){
            var jbOffset = $(".top").offset();
            $( window ).scroll( function() {
                if ( $( document ).scrollTop() > jbOffset.top ) {
                    $( '.top' ).addClass( 'fixed' );
                }
                else {
                    $( '.top' ).removeClass( 'fixed' );
                }
            });
        }

        //상단으로
        $("#top_btn").on("click", function() {
            $("html, body").animate({scrollTop:0}, '500');
            return false;
        });

    });
});
</script>
