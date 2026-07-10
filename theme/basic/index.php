<?php
define('_INDEX_', true);
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
    include_once(G5_THEME_MOBILE_PATH.'/index.php');
    return;
}

include_once(G5_THEME_PATH.'/head.php');
?>
<style>
body {
    -ms-overflow-style: none; /* IE and Edge */
    scrollbar-width: none; /* Firefox */
}
body::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera*/
}
</style>

<section id="visual_back"></section>
<section id="visual" class="section">
	<div class="swiper-container visual_swiper">
		<ul class="swiper-wrapper">
			<li class="swiper-slide visu_li visu_li_1">
				<div class="inner">
					<div class="visu_box">
						<p class="visu_en font_gm_m">Real - Time Analysis System</p>
						<h2 class="visu_h2">
							정확성의 정점<span class="font_mont">,</span><br>
							당첨률의 정점에 도달하다
						</h2>
					</div>
				</div>
			</li>
			<li class="swiper-slide visu_li visu_li_2">
				<div class="inner">
					<div class="visu_box">
						<p class="visu_en font_gm_m">Amazing Analysis & Accuracy Grow Rebenue</p>
						<h2 class="visu_h2">최고의 분석력으로<br>수익을 극대화 하세요</h2>
					</div>
				</div>
			</li>
		</ul>
	</div>
	<p class="mouse">
		<img src="<?=G5_THEME_IMG_URL?>/mouse_ic.png" alt="">
		<span class="font_gm_l">SCROLL</span>
	</p>
</section>

<script>
	var swiper = new Swiper('.visual_swiper', {
		effect : 'fade',
		loop : true,
		autoplay: {
			delay: 5000,
			disableOnInteraction: false,
		},
	});
</script>

<section class="main_frm_box">
	<?if(!$is_member){?>
	<a href="<?=G5_BBS_URL?>/login.php" class="main_frm_btn">
		<img src="<?=G5_THEME_IMG_URL?>/main_frm_logo.png" alt="">로그인
	</a>
	<?}else{?>
	<p class="main_login_info"><span style="font-weight:600;color:#e31b23"><?=$member['mb_name']?>(<?=$member['mb_type']?>)</span> 회원님<br>환영합니다.</p>
	<a href="<?=G5_BBS_URL?>/logout.php" class="main_frm_btn">
		<img src="<?=G5_THEME_IMG_URL?>/main_frm_logo.png" alt="">로그아웃
	</a>
	<?}?>
	<!--div class="main_frm_wrap">
		<form id="frm_1" name="frm_1" method="post">
			<input class="input_tp input_tp1" type="hidden" name="lr_type" id="lr_type_1" value="상담요청">
			<h2 class="main_frm_tit">로또피크 상담신청하기</h2>
			<input type="text" name="lr_name" id="lr_name_1" class="main_ipt" placeholder="이름">
			<div class="main_frm_tel">
				<input type="text" name="lr_hp1" id="lr_hp1_1" class="main_ipt" placeholder="" maxlength="3" onkeyup="value=value.replace(/[^\d]/g,'')">
				<p>-</p>
				<input type="text" name="lr_hp2" id="lr_hp2_1" class="main_ipt" maxlength="4" onkeyup="value=value.replace(/[^\d]/g,'')">
				<p>-</p>
				<input type="text" name="lr_hp3" id="lr_hp3_1" class="main_ipt" maxlength="4" onkeyup="value=value.replace(/[^\d]/g,'')">
			</div>
			<div class="main_chk_box">
				<p class="main_chk">
					<input type="checkbox" checked name="chk2" id="chk2_1">
					<label for="chk2_1">개인정보 수집/이용 동의</label>
				</p>
				<a href="<?=G5_BBS_URL?>/content.php?co_id=privacy" target="_blank" class="main_chk_more">더보기</a>
			</div>
			<button type="button" onclick="fnSubmit('_1')" class="main_frm_btn">상담신청</button>
		</form>
	</div-->
</section>

<? 
	$turn = getTurn()-1; 
	if(!$endTurn){ $endTurn = getTurn()-1; }
	if(!$ver){ $ver = '1'; }
	$list = getLuckyNum($turn);
?>
<section id="mid01" class="section zindex10">
	<div class="inner">
		<div class="mid01_1">
			<div class="mid01_box">
				<h2 class="mid01_h2">MBC 행복드림 동행복권 추첨방송</h2>
				<div class="mid01_move mid01_1_border">
					<p class="mid01_move_img"><img src="<?=G5_THEME_IMG_URL?>/mid01_1_img1.jpg" alt=""></p>
					<p class="mid01_move_txt"><?=$turn?>회ㅣ<?=date("Y년 m월 d일", strtotime($list['drwNoDate']))?> 방송</p>
					<a href="https://dhlottery.co.kr/common.do?method=main" target="_blank" class="mid01_move_a">동행복권사이트 바로가기</a>
				</div>
			</div>
			<div class="mid01_box mid01_box2">
				<?include_once(G5_PATH."/sub/main.lucky.php");?>				
			</div>
			<div class="mid01_box">
				<h2 class="mid01_h2"><!--<?=$turn?>회차 로또피크 당첨현황-->로또피크</h2>
				<img src="<?=G5_THEME_IMG_URL?>/mid01_box_img.png?v=3" alt="">
				<!--ul class="mid01_grade">
					<li class="mid01_1_border">
						<p class="mid01_grade_img"><img src="<?=G5_THEME_IMG_URL?>/mid01_1_grade1.png" alt=""></p>
						<p class="mid01_grade_txt">
							<?=number_format($config[cf_lucky_1])?> 조합ㅣ<?=$config['cf_etc_1_1']?>원
						</p>
					</li>
					<li class="mid01_1_border">
						<p class="mid01_grade_img"><img src="<?=G5_THEME_IMG_URL?>/mid01_1_grade2.png" alt=""></p>
						<p class="mid01_grade_txt">
							<?=number_format($config[cf_lucky_2])?> 조합｜<?=$config['cf_etc_2_1']?>원
						</p>
					</li>
					<li class="mid01_1_border">
						<p class="mid01_grade_img"><img src="<?=G5_THEME_IMG_URL?>/mid01_1_grade3.png" alt=""></p>
						<p class="mid01_grade_txt">
							<?=number_format($config[cf_lucky_3])?> 조합｜<?=$config['cf_etc_3_1']?>원
						</p>
					</li>
				</ul-->
			</div>
		</div>

		<div class="mid01_3">
			<div class="inner">
				<div id="visual2">
					<div class="swiper-container review_swiper">
						<ul class="swiper-wrapper">
							<?
								$sql = " select * from g5_write_review where 1=1 order by wr_datetime desc";
								$result = sql_query($sql);
								for($i=0; $row=sql_fetch_array($result); $i++){
								$all_img = get_all_thumbnail('review', 1, $row['wr_id'], 330, 330);
								foreach($all_img as $v);
							?>
							<li class="swiper-slide">
								<a href=javascript:// onClick="window.open('<?=$v['ori']?>', '', 'scrollbars=no,resizeable=yes,toolbar=no,status=no,top=100,width=500,height=500');" title=''>
									<div class="thum"><img src="<?=$v['src']?>" alt=""></div>
									<div class="tit"><?=$row['wr_subject']?></div>
								</a>
							</li>
							<?}?>							
						</ul>
					</div>
				</div>
			</div>
		</div>
		<script>
		var swiper1 = new Swiper('.review_swiper', {
			slidesPerView: 5,
			spaceBetween: 10,
			autoplay: {
				delay: 3000,
				disableOnInteraction: false,
			},
			loop: true,
		});
		</script>


		<div class="mid01_2">
			<h3 class="mid01_h3 font_gm_b">LOTTO YOUTUBE</h3>
			<div class="mid01_slick">
				<div class="slick_box">
					<a>
						<iframe src="https://www.youtube.com/embed/25uT52s4XxY" title="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe> 
					</a>
				</div>
				<div class="slick_box">
					<a>
						<iframe src="https://www.youtube.com/embed/x-9ajQ2FT68" title="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe> 
					</a>
				</div>
				<div class="slick_box">
					<a>
						<iframe src="https://www.youtube.com/embed/ctszFUUomyA" title="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe> 
					</a>
				</div>
			</div>
		</div>
	</div>
</section>

<section id="mid02" class="section zindex10">
	<div class="inner">
		<h2 class="mid02_h2 font_gm_b">PRODUCT</h2>
		<p class="mid02_en font_gm_b">Real - Time Analysis System</p>
		<p class="mid02_desc">
			로또피크는 탁월하고 다양한 로또 통계분석 경험을 가진 전문가 집단으로<br>
			가장 정확한 분석 처리 능력을 보유하고 있습니다.<br>
			실시간 당첨 피드백과 지속적인 프로그램 업데이트를 통해<br>
			높은 당첨 수익률을 보장합니다.
		</p>
		<ul class="mid02_list">
			<li class="mid02_li">
				<a href="<?=G5_URL?>/sub/membership.php">
					<p class="mid02_img"><img src="<?=G5_THEME_IMG_URL?>/mid02_img1.jpg"></p>
					<p class="mid02_sort">정회원</p>
					<div class="mid02_txt">
						<h3 class="mid02_h3 font_gm_b">PRO</h3>
						<p class="mid02_price"><b>상담문의</b></p>
					</div>
				</a>
			</li>
			<li class="mid02_li">
				<a href="<?=G5_URL?>/sub/membership.php">
					<p class="mid02_img"><img src="<?=G5_THEME_IMG_URL?>/mid02_img2.jpg"></p>
					<p class="mid02_sort">VIP 회원</p>
					<div class="mid02_txt">
						<h3 class="mid02_h3 font_gm_b">TOP - CLASS</h3>
						<p class="mid02_price"><b>상담문의</b></p>
					</div>
				</a>
			</li>
		</ul>
	</div>
</section>

<script type="text/javascript">
	$(document).ready(function(){
		//.parallax(xPosition, speedFactor, outerHeight) options:
		//xPosition - Horizontal position of the element
		//inertia - speed to move relative to vertical scroll. Example: 0.1 is one tenth the speed of scrolling, 2 is twice the speed of scrolling
		//outerHeight (true/false) - Whether or not jQuery should use it's outerHeight option to determine when a section is in the viewport
		$('#mid02').parallax( "50%",0.2); //$('.product').parallax("50%", 0.2);
	})
</script>
<script>
	(function( $ ){
		var $window = $(window);
		var windowHeight = $window.height();

		$window.resize(function () {
			windowHeight = $window.height();
		});

		$.fn.parallax = function(xpos, speedFactor, outerHeight) {
			
			var $this = $(this);
			var getHeight;
			var firstTop;
			var paddingTop = 0;
			
			//get the starting position of each element to have parallax applied to it		
			$this.each(function(){
				firstTop = $this.offset().top;
			});

			if (outerHeight) {
				getHeight = function(jqo) {
					return jqo.outerHeight(true);
				};			
			} else {
				getHeight = function(jqo) {
					return jqo.height();
				};
			}

				
			// setup defaults if arguments aren't specified
			if (arguments.length < 1 || xpos === null) xpos = "50%";
			if (arguments.length < 2 || speedFactor === null) speedFactor = 0.1;
			if (arguments.length < 3 || outerHeight === null) outerHeight = true;
			
			// function to be called whenever the window is scrolled or resized
			function update(){
				var pos = $window.scrollTop();

				$this.each(function(){
					var $element = $(this);
					var top = $element.offset().top;
					var height = getHeight($element);

					// Check if totally above or totally below viewport
					if (top + height < pos || top > pos + windowHeight) {
						return;
					}

					$this.css('backgroundPosition', xpos + " " + Math.round((firstTop - pos) * speedFactor) + "px");
				});
			}		

			$window.bind('scroll', update).resize(update);
			update();
		};
	})(jQuery);	

	$('.mid01_slick').slick({
		centerMode: true,
		centerPadding: '360px',
		//autoplay: true,
		autoplaySpeed: 5000,
	});
</script>

<?php
include_once(G5_THEME_PATH.'/tail.php');
?>