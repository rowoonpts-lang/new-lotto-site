<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

include_once(G5_THEME_MOBILE_PATH.'/head.php');
?>
<section class="section_1">
	<div class="inner">	
		<!--스위퍼 시작-->
		<div id="visual">
			<div class="swiper-container visual_swiper">
				<ul class="swiper-wrapper">
					<?
						$sql = " select * from g5_write_main_slider where 1=1 and ca_name = '모바일' order by wr_datetime desc ";
						$result = sql_query($sql);
						for($i=0; $row=sql_fetch_array($result); $i++){
							$all_img = get_all_thumbnail('main_slider', 1, $row['wr_id'], 800, 800);
							foreach($all_img as $v);
					?>
					<li class="swiper-slide">
						<a href="<?=$row['wr_link1']?>">							
							<img src="<?=$v['src']?>" alt="">
						</a>
					</li>
					<?}?>
				</ul>
			</div>
			<div class="swiper-pagination"></div>
		</div>

		<script>
		var swiper = new Swiper('.visual_swiper', {
			pagination: {
				mode: 'horizontal',
				el: '.swiper-pagination',
				clickable: true,
			},
			autoplay: {
				delay: 5000,
				disableOnInteraction: false,
			},
			loop: true,
			navigation: {
				nextEl: '.swiper-button-next',
				prevEl: '.swiper-button-prev',
			},
		});
		</script>
		<!--스위퍼 끝-->
	</div><!-- inner 끝 -->
</section><!-- section_1 끝 -->

<section class="section_15">
	<div class="inner_2">
		<ul class="product_ul2">
			<li id="view_turn_result">
				<? $turn = getTurn()-1; ?>
				<?include_once(G5_PATH."/sub/ajax.turn.list.view.php");?>
			</li>
			<li class="product_li2">
				<h4>동행복권 <?=$turn?>회차</h4>
				<h2>당첨현황</h2>
				<ul class="product_li3_ul">
					<li>
						<div class="tit">1등</div>
						<div class="cont1"><?=$config['cf_etc_1']?>명</div>	
						<div class="cont2"><?=$config['cf_etc_1_1']?>원</div>	
					</li>
					<li>
						<div class="tit">2등</div>
						<div class="cont1"><?=$config['cf_etc_2']?>명</div>	
						<div class="cont2"><?=$config['cf_etc_2_1']?>원</div>	
					</li>
					<li>
						<div class="tit">3등</div>
						<div class="cont1"><?=$config['cf_etc_3']?>명</div>	
						<div class="cont2"><?=$config['cf_etc_3_1']?>원</div>	
					</li>
				</ul>
			</li>
			<li class="product_li2">
				<h4>로또중심 <?=$turn?>회차</h4>
				<h2>당첨조합 배출 현황</h2>
				<ul class="product_li2_ul">
					<li>
						<div class="tit">1등배출</div>
						<div class="cont"><span><?=number_format($config[cf_lucky_1])?></span>조합</div>	
					</li>
					<li>
						<div class="tit">2등배출</div>
						<div class="cont"><span><?=number_format($config[cf_lucky_2])?></span>조합</div>	
					</li>
					<li>
						<div class="tit">3등배출</div>
						<div class="cont"><span><?=number_format($config[cf_lucky_3])?></span>조합</div>	
					</li>
				</ul>
			</li>
		</ul>
		<form id="frm_2" name="frm_2" method="post">
		<input class="input_tp input_tp1" type="hidden" name="lr_type" id="lr_type_2" value="상담요청">
		<ul class="product_ul_res flex_space">
			<li class="tit">로또 1등 번호<br><b>상담요청하기</b></li>
			<li class="cont">
				<div class="cont_top flex_space">
					<input class="input_tp input_tp1" type="text" name="lr_name" id="lr_name_2" placeholder="이름을 입력해주세요.">
					<input class="input_tp input_tp2" type="text" name="lr_hp1" id="lr_hp1_2" maxlength="3" value="010" readonly>
					<span>-</span>
					<input class="input_tp input_tp2" type="text" name="lr_hp2" id="lr_hp2_2" maxlength="4">
					<span>-</span>
					<input class="input_tp input_tp2" type="text" name="lr_hp3" id="lr_hp3_2" maxlength="4">
				</div>
				<div class="cont_btm">
					<input type="checkbox" name="chk" id="chk_2" checked>
					<label for="chk_1">이용약관 동의</label>
					<input type="checkbox" name="chk2" id="chk2_2" checked>
					<label for="chk2_1">개인정보 처리방침 동의</label>
				</div>
			</li>
			<li class="s_btn"><button type="button" onclick="fnSubmit('_2')">상담요청</button></li>
		</ul>
		</form>
	</div>
</section>

<section class="section_2">
	<div class="inner_2">
		<h2><span class="light">로또중심</span>&nbsp;PRODUCT</h2>
		<ul class="a_product clearfix">
			<li>
				<a href="<?=G5_URL?>/sub/sub0201.php"><img src="<?=G5_THEME_IMG_URL?>/lo_main_obj1.jpg" alt="">
					<ul class="hover_show">
						<li>SILVER</li>
						<li>실버(연회원)</li>
						<li><img src="<?=G5_THEME_IMG_URL?>/add.png" alt=""></li>
						<li class="li_bg"></li>
					</ul>
				</a>
			</li>
			<li>
				<a href="<?=G5_URL?>/sub/sub0201.php"><img src="<?=G5_THEME_IMG_URL?>/lo_main_obj2.jpg" alt="">
					<ul class="hover_show">
						<li>GOLD</li>
						<li>골드(VIP)</li>
						<li><img src="<?=G5_THEME_IMG_URL?>/add.png" alt=""></li>
						<li class="li_bg"></li>
					</ul>
				</a>
			</li>
			<li>
				<a href="<?=G5_URL?>/sub/sub0201.php"><img src="<?=G5_THEME_IMG_URL?>/lo_main_obj3.jpg" alt="">
					<ul class="hover_show">
						<li>PLATINUM</li>
						<li>플래티넘(VVIP)</li>
						<li><img src="<?=G5_THEME_IMG_URL?>/add.png" alt=""></li>
						<li class="li_bg"></li>
					</ul>
				</a>
			</li>
		</ul>

		<div class="main_you">
			<h2><span class="light">로또중심</span>&nbsp;YOUTUBE</h2>
			<ul class="product_ul3">
				<li>
					<a target="_blank" href="https://www.youtube.com/watch?v=25uT52s4XxY&t=52s">
						<div class="thum" style="background: url('<?=G5_THEME_IMG_URL?>/youtube1.jpg') no-repeat 50% 50%; background-size: cover;"></div>
						<div class="desc">로또 조작설! 이월되지 않는 이유! 시원하게 밝혀 드려요!</div>
					</a>
				</li>
				<li>
					<a target="_blank" href="https://www.youtube.com/watch?v=3ojKMhFZQZs">
						<div class="thum" style="background: url('<?=G5_THEME_IMG_URL?>/youtube2.jpg') no-repeat 50% 50%; background-size: cover;"></div>
						<div class="desc">이 꿈을 꿨다면 무조건 로또를 사야합니다 !! 무턱대고 나의 촉만 믿지 말아요</div>
					</a>
				</li>
				<li>
					<a target="_blank" href="https://www.youtube.com/watch?v=x-9ajQ2FT68">
						<div class="thum" style="background: url('<?=G5_THEME_IMG_URL?>/youtube3.jpg') no-repeat 50% 50%; background-size: cover;"></div>
						<div class="desc">로또당첨꿈 로또 1등 당첨자들이 꾼 꿈 Best7 로또꿈 로또 꿈해몽 총정리</div>
					</a>
				</li>
				<li>
					<a target="_blank" href="https://www.youtube.com/watch?v=ctszFUUomyA">
						<div class="thum" style="background: url('<?=G5_THEME_IMG_URL?>/youtube4.jpg') no-repeat 50% 50%; background-size: cover;"></div>
						<div class="desc">14번 복권에 당첨된 남자가 그의 비법을 세상에 밝히다</div>
					</a>
				</li>
				<li>
					<a target="_blank" href="https://www.youtube.com/watch?v=RR6iUBSSz2I">
						<div class="thum" style="background: url('<?=G5_THEME_IMG_URL?>/youtube5.jpg') no-repeat 50% 50%; background-size: cover;"></div>
						<div class="desc">로또당첨비법 로또당첨확률 올리는 10가지 방법</div>
					</a>
				</li>
			</ul>
		</div>

	</div>
</section><!-- section_2 끝 -->

<section class="section_3">
	<div class="inner_2">
		<div class="a_news">
			<h3>
				<p>로또중심&nbsp;<span>NEWS</span></p>
				<p class="news_more_btn"><a href="<?=G5_BBS_URL?>/board.php?bo_table=notice_"><img src="<?=G5_THEME_IMG_URL?>/plus.png" alt=""></a></p>
			</h3>
			<div class="news_top">
				<?
					$sql = " select * from g5_write_notice_ where 1=1 order by wr_datetime desc limit 1 ";
					$row = sql_fetch($sql);
				?>
				<a href="<?=G5_BBS_URL?>/board.php?bo_table=notice_&wr_id=<?=$row['wr_id']?>">
					<div class="thum">
						<img src="<?=G5_THEME_IMG_URL?>/lo_main_obj6.jpg" alt="">
					</div>
					<ul>						
						<li class="news_title"><?=$row['wr_subject']?></li>
						<li class="news_content"><?php echo conv_subject(strip_tags($row['wr_content']),35,'...')?></li>
						<li class="news_date"><?=substr($row['wr_datetime'],0,10)?></li>
					</ul>
				</a>
			</div>
			<div class="news_bottom">
				<ul>
					<?
						$sql = " select * from g5_write_notice_ where 1=1 order by wr_datetime desc limit 1, 3 ";
						$result = sql_query($sql);
						for($i=0; $row=sql_fetch_array($result); $i++){
					?>
					<li>
						<a href="<?=G5_BBS_URL?>/board.php?bo_table=notice_&wr_id=<?=$row['wr_id']?>" class="news_title"><?=$row['wr_subject']?></a>
						<span class="news_date"><?=substr($row['wr_datetime'],0,10)?></span>
					</li>
					<?}?>
				</ul>
			</div>
		</div><!-- a_news 끝 -->
		<ul class="a_menu">
			<li>
				<a>
					<div class="a_icon"><img src="<?=G5_THEME_IMG_URL?>/lo_main_icon1.png" alt=""></div>
					<div class="a_title">무통장 입금정보</div>
					<div class="red_hr"></div>
					<div class="a_text1">302-1390-5552-31</div>
					<div class="a_text2">농협 / 김민지(지오인터내셔널)</div>
				</a>
			</li>
			<li>
				<a href="<?=G5_URL?>/bbs/board.php?bo_table=notice_">
					<div class="a_icon"><img src="<?=G5_THEME_IMG_URL?>/lo_main_icon2.png" alt=""></div>
					<div class="a_title">고객센터</div>
					<div class="red_hr"></div>
					<div class="a_text1">1522-8302</div>
					<div class="a_text2">Daily 10:00 ~ 18:00</div>
				</a>
			</li>
			<li>
				<a href="<?=G5_URL?>/bbs/qalist.php">
					<div class="a_icon"><img src="<?=G5_THEME_IMG_URL?>/lo_main_ico3.png" alt=""></div>
					<div class="a_title">1:1 문의</div>
					<div class="red_hr"></div>
					<div class="a_text2">여러분의 소중한 의견을 모아</div>
					<div class="a_text2">더욱 좋은 모습으로 보답하겠습니다.</div>
				</a>
			</li>
		</ul>
	</div><!-- inner_2 끝 -->
</section><!-- section_3 끝 -->



<?php
include_once(G5_THEME_PATH.'/tail.php');
?>