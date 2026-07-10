<?php
define('_INDEX_', true);
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
    include_once(G5_THEME_MOBILE_PATH.'/index.php');
    return;
}

include_once(G5_THEME_PATH.'/head.php');
?>

<section id="main">
	<article class="main1">
		<div class="swiper-container visual_swiper">
			<ul class="swiper-wrapper">
				<li class="swiper-slide" style="background: url('<?=G5_THEME_IMG_URL?>/banner1.jpg') no-repeat 50% 50%; background-size: cover;">
					<div class="slide_bg" data-swiper-animation="scaleKss" data-duration="6s" data-delay="0s" data-swiper-out-animation="scaleKss2" data-out-duration="0s"><img src="<?=G5_THEME_IMG_URL?>/banner1.jpg" alt=""></div>
					<div class="inner">
						<div class="banner_txt1 font_mont" data-swiper-animation="fadeInUp" data-duration="1.5s" data-delay="0.5s" data-swiper-out-animation="fadeOut" data-out-duration="0s">Virtual Reality Analysis System</div>
						<div class="banner_txt2" data-swiper-animation="fadeInUp" data-duration="1.5s" data-delay="1s" data-swiper-out-animation="fadeOut" data-out-duration="0s">세상에 없던 단 하나의 분석 시스템<br>지금 시작됩니다!</div>
						<a href="<?=G5_URL?>/sub/sub0101.php" class="banner_txt3" data-swiper-animation="fadeInUp" data-duration="1.5s" data-delay="1.5s" data-swiper-out-animation="fadeOut" data-out-duration="0s">MORE</a>
					</div>
				</li>
				<li class="swiper-slide" style="background: url('<?=G5_THEME_IMG_URL?>/banner2.jpg') no-repeat 50% 50%; background-size: cover;">
					<div class="slide_bg" data-swiper-animation="scaleKss" data-duration="6s" data-delay="0s" data-swiper-out-animation="scaleKss2" data-out-duration="0s"><img src="<?=G5_THEME_IMG_URL?>/banner2.jpg" alt=""></div>
					<div class="inner">
						<div class="banner_txt1 font_mont" data-swiper-animation="fadeInUp" data-duration="1.5s" data-delay="0.5s" data-swiper-out-animation="fadeOut" data-out-duration="0s">Lotto Click</div>
						<div class="banner_txt2" data-swiper-animation="fadeInUp" data-duration="1.5s" data-delay="1s" data-swiper-out-animation="fadeOut" data-out-duration="0s">아직도 로또 자동으로 하십니까?<br>단 한번의 클릭으로 행운을 잡을수 있습니다!</div>
						<a href="<?=G5_URL?>/sub/sub0201.php" class="banner_txt3" data-swiper-animation="fadeInUp" data-duration="1.5s" data-delay="1.5s" data-swiper-out-animation="fadeOut" data-out-duration="0s">MORE</a>
					</div>
				</li>
			</ul>
		</div>

		<script>
		
		$(document).ready(function(){
				var swiperAnimation = new SwiperAnimation();
				var swiper = new Swiper('.visual_swiper', {
					pagination: {
						mode: 'horizontal',
						el: '.swiper-pagination',
						clickable: true,
					},
					speed:2000,
					autoplay: {
						delay: 6000,
						disableOnInteraction: false,
					},
					loop: true,
					navigation: {
						nextEl: '.swiper-button-next',
						prevEl: '.swiper-button-prev',
					},
					on: {
					  init: function () {
						//$('.banner_txt1, banner_txt2, .banner_txt3').css('opacity','0');
						swiperAnimation.init(this).animate();
					  },
					  slideChange: function () {
						swiperAnimation.init(this).animate();
					  }
					}

				});
				
		});
		</script>
		<!--스위퍼 끝-->
		
	</article>
	<article class="main2">
		<div class="inner">
			<ul class="left">
				<li>
					<a href="<?=G5_URL?>/bbs/board.php?bo_table=notice_" class="tit">NOTICE&nbsp;+</a>
					<ul class="left_ul">
						<?
							$sql = " select * from g5_write_notice_ where 1=1 order by wr_datetime desc limit 2";
							$result = sql_query($sql);
							for($i=0; $row=sql_fetch_array($result); $i++){
						?>
						<li><a href="<?=G5_BBS_URL?>/board.php?bo_table=notice_&wr_id=<?=$row['wr_id']?>">[공지]&nbsp;<?php echo cut_str($row['wr_subject'],40,'...')?></a></li>
						<?}?>
						<!-- <li><a href="">[공지]&nbsp;동해물과 백두산이 마르고 닳도록 하느님이 보우하사 우리나라 만세...</a></li> -->
					</ul>
				</li>
				<li>
					<a class="tit">문자가 오지 않을때</a>
					<ul class="left_ul">
						<li><a>로또클릭의 조합문자가 오지 않을 경우 통신사의 스팸설정을 체크 해주세요.</a></li>
						<li><a href="<?=G5_URL?>/sub/sub0501.php" class="smsx_link"><span>자세한 방법 보기</span><span>more&nbsp;+</span></a></li>
					</ul>
				</li>
			</ul>
			<div class="right">
				<form id="frm_2" name="frm_2" method="post">
				<input class="input_tp input_tp1" type="hidden" name="lr_type" id="lr_type_2" value="상담요청">
					<div class="main2_res">
						<div class="subtit font_mont">LOTTO CLICK</div>
						<div class="tit">로또1등 예상번호<br>꼭 받아가세요!</div>
						<ul class="main2_resul">
							<li><input type="text" class="input_tp input_tp1" placeholder="이름" name="lr_name" id="lr_name_2"></li>
							<li>
								<input class="input_tp input_tp2" type="text" name="lr_hp1" id="lr_hp1_2" maxlength="3" value="010" readonly>
								<span>-</span>
								<input class="input_tp input_tp2" type="text" name="lr_hp2" id="lr_hp2_2" maxlength="4">
								<span>-</span>
								<input class="input_tp input_tp2" type="text" name="lr_hp3" id="lr_hp3_2" maxlength="4">
							</li>
						</ul>
						<div class="main2_ckbox">
							<input type="checkbox" name="chk2" id="chk2_2" checked>
							<label for="main2_ck">개인정보 수집/이용 동의</label>
							<a class="main2_ck_a" href="<?=G5_URL?>/bbs/content.php?co_id=privacy" target="_blank">more&nbsp;+</a>
						</div>
						<button class="main2_btn hv08" type="button" onclick="fnSubmit('_2')">빠른상담요청</button>
					</div>
				</form>
			</div>
		</div>
	</article>
	<article class="main3">
		<? $turn = getTurn()-1; ?>
		<div class="inner">
			<div class="main3_left">
				<div class="main3_tit">로또클릭&nbsp;<span><?=$turn?></span>회차 당첨실적</div>
				<div class="main3_box">
					<ul class="main3_lul">
						<li>
							<div class="num bgc_r">1등</div>
							<div class="add"><?=number_format($config[cf_lucky_1])?>조합</div>
							<div class="pri"><?=$config['cf_etc_1_1']?>원</div>
						</li>
						<li>
							<div class="num bgc_y">2등</div>
							<div class="add"><?=number_format($config[cf_lucky_2])?>조합</div>
							<div class="pri"><?=$config['cf_etc_2_1']?>원</div>
						</li>
						<li>
							<div class="num bgc_s">3등</div>
							<div class="add"><?=number_format($config[cf_lucky_3])?>조합</div>
							<div class="pri"><?=$config['cf_etc_3_1']?>원</div>
						</li>
					</ul>
				</div>
			</div>
			<div class="main3_mid">
				<?include_once(G5_PATH."/sub/main.lucky.php");?>
			</div>
			<div class="main3_right">
				<div class="main3_tit">동행복권 추첨방송</div>
				<div class="main3_box main3_box3">
					<div class="video"><img src="<?=G5_THEME_IMG_URL?>/video.png" alt=""></div>
					<div class="desc">[&nbsp;<span><?=$turn?></span>회&nbsp;]&nbsp;-&nbsp;<span><?=date("Y년 m월 d일", strtotime($list['drwNoDate']))?></span>&nbsp;방송</div>
					<a href="https://dhlottery.co.kr/common.do?method=main" target="_blank" class="link hv08">동행복권 사이트 바로가기</a>
				</div>
			</div>
		</div>
	</article>
	<article class="main4">
		<div class="inner">
			<div class="main4_subtit font_mont">lotto system</div>
			<div class="main4_tit">최상의 적중률! 로또클릭 멤버쉽 시스템</div>
			<div class="main4_cont">VRAS시스템을 기반으로 분석번호를 제공하며, VRAS시스템 프로그램 필터 및 레벨을<br>기준으로 총 3가지의 v3,v5,v7 등급으로 최상의 서비스와 관리를 받아 보실 수 있습니다.</div>
			<ul class="main4ul">
				<li>
					<div class="tit"><b class="font_mont">V3</b>- 월회원</div>
					<div class="pri"><b class="font_mont">22,000</b>원</div>
					<a href="<?=G5_URL?>/sub/sub0201.php" class="more font_mont">more&nbsp;+</a>
				</li>
				<li>
					<div class="tit"><b class="font_mont">V5</b>- 정회원</div>
					<div class="pri"><b class="font_mont">154,000</b>원</div>
					<a href="<?=G5_URL?>/sub/sub0201.php" class="more font_mont">more&nbsp;+</a>
				</li>
				<li>
					<div class="tit"><b class="font_mont">V7</b>- VIP</div>
					<div class="pri"><b class="font_mont">770,000</b>원</div>
					<a href="<?=G5_URL?>/sub/sub0201.php" class="more font_mont">more&nbsp;+</a>
				</li>
			</ul>
		</div>
	</article>
</section>


<?php
include_once(G5_THEME_PATH.'/tail.php');
?>