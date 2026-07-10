<?php
	include_once("_common.php");
	include_once(G5_PATH."/_head.php");
?>

<section id="s11">
	<article class="s11_1">
		<div class="inner">
			<div class="s11_1desc1">단 한 번의 클릭만으로 로또 1등 당첨에 다가서다!</div>
			<div class="s11_1desc2">로또 클릭만의&nbsp;<span class="font_mont">VRAS</span>&nbsp;프로그램</div>
			<div class="s11_1desc3 font_mont">Virtual Reality Analysis System</div>
		</div>
	</article>
	<article class="s11_2">
		<div class="inner">
			<?if(!G5_IS_MOBILE){?>
			<div class="s11_2tit">데이터의 양, 처리속도, 정확성만이 차별을 말할 수 있다.</div>
			<div class="s11_2desc1">언제까지 방대한 자동 확률에 매주 만원씩 버리겠습니까?<br>로또 클릭 VRAS는 여러분들이 원하는 바를 이루어 드립니다!</div>
			<?}else{?>
			<div class="s11_2tit">데이터의 양, 처리속도, 정확성만이<br>차별을 말할 수 있다.</div>
			<div class="s11_2desc1">언제까지 방대한 자동 확률에 매주 만원씩 버리겠습니까?<br>로또 클릭 VRAS는 여러분들이 원하는 바를<br>이루어 드립니다!</div>
			<?}?>
			<ul class="s11_2ul">
				<li data-aos="fade-up" data-aos-duration="800" data-aos-delay="100">
					<div class="li_box">
						<div class="thum"><img src="<?=G5_THEME_IMG_URL?>/s11_icon1.png" alt=""></div>
						<div class="desc">데이터 통계구성</div>	
					</div>
				</li>
				<li data-aos="fade-up" data-aos-duration="800" data-aos-delay="200">
					<div class="li_box">
						<div class="thum"><img src="<?=G5_THEME_IMG_URL?>/s11_icon2.png" alt=""></div>
						<div class="desc">목표 데이터값 수정</div>	
					</div>
				</li>
				<li data-aos="fade-up" data-aos-duration="800" data-aos-delay="300">
					<div class="li_box">
						<div class="thum"><img src="<?=G5_THEME_IMG_URL?>/s11_icon3.png" alt=""></div>
						<div class="desc">데이터 생성</div>	
					</div>
				</li>
				<li data-aos="fade-up" data-aos-duration="800" data-aos-delay="400">
					<div class="li_box">
						<div class="thum"><img src="<?=G5_THEME_IMG_URL?>/s11_icon4.png" alt=""></div>
						<div class="desc">정제된 목푯값 추출</div>	
					</div>
				</li>
				<li data-aos="fade-up" data-aos-duration="800" data-aos-delay="500">
					<div class="li_box">
						<div class="thum"><img src="<?=G5_THEME_IMG_URL?>/s11_icon5.png" alt=""></div>
						<div class="desc">데이터마이닝</div>	
					</div>
				</li>
				<li data-aos="fade-up" data-aos-duration="800" data-aos-delay="100">
					<div class="li_box">
						<div class="thum"><img src="<?=G5_THEME_IMG_URL?>/s11_icon6.png" alt=""></div>
						<div class="desc">그룹핑</div>	
					</div>
				</li>
				<li data-aos="fade-up" data-aos-duration="800" data-aos-delay="200">
					<div class="li_box">
						<div class="thum"><img src="<?=G5_THEME_IMG_URL?>/s11_icon7.png" alt=""></div>
						<div class="desc">패턴 필터링 작업 </div>	
					</div>
				</li>
				<li data-aos="fade-up" data-aos-duration="800" data-aos-delay="300">
					<div class="li_box">
						<div class="thum"><img src="<?=G5_THEME_IMG_URL?>/s11_icon8.png" alt=""></div>
						<div class="desc">시뮬레이션 결과 창출</div>	
					</div>
				</li>
				<li data-aos="fade-up" data-aos-duration="800" data-aos-delay="400">
					<div class="li_box">
						<div class="thum"><img src="<?=G5_THEME_IMG_URL?>/s11_icon9.png" alt=""></div>
						<div class="desc">최종 조합 구성</div>	
					</div>
				</li>
				<li data-aos="fade-up" data-aos-duration="800" data-aos-delay="500">
					<div class="li_box">
						<div class="thum"><img src="<?=G5_THEME_IMG_URL?>/s11_icon10.png" alt=""></div>
						<div class="desc">SMS 발송</div>	
					</div>
				</li>
			</ul>
			<?if(!G5_IS_MOBILE){?>
			<div class="s11_2desc2">그리고 체계적인 고객 관리시스템 구축으로 지속적인 당첨관리! </div>	
			<?}else{?>
			<div class="s11_2desc2">그리고 체계적인 고객 관리시스템<br>구축으로 지속적인 당첨관리! </div>
			<?}?>
		</div>
	</article>
</section>

<?php
	include_once(G5_THEME_PATH.'/tail.php');
?>