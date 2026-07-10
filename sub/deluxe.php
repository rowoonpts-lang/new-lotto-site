<?
	include_once("_common.php");
	if(!$is_admin){
		alert("로그인 후 이용 가능합니다.");
	}

	include_once(G5_PATH."/_head.php");
?>

<div id="deluxe">

	<div class="dux_1">
		<div class="deluxe_inner">
			<p class="dux_txt01">New RDC Pro System Start!!</p>
			<p class="dux_cm_txt1 dux_txt02">오직 디럭스 그룹만을 위한 퍼포먼스!</p>
			<p class="dux_cm_txt2 dux_txt03">고등수 알고리즘을 치밀하게 읽어내다!</p>
			<div class="dux_1_box">				
				<div class="dux_1_lt">
					<p class="dux_1_lt_txt01">상위 1%를 위한<br><strong>디럭스 그룹 Deluxe-group</strong></p>
					<p class="dux_1_lt_txt02">최상위 등수 당첨 목표 프로세스<br>You are the best!</p>
					<p class="dux_1_lt_txt03">조합수 : 20조합 ~ 100조합</p>
					<p class="dux_1_arr"><img src="<?=G5_THEME_IMG_URL?>/deluxe_img2.png" alt=""></p>
				</div>
				<div class="dux_1_rt">
					<a class="dux_1_rt_txt01" onClick="fnShowpop('4')" style="cursor:pointer">가입문의</a>
					<p class="dux_1_rt_txt02">
						* 로또 중심 무료회원은 가입불가<br>
						* 로또 전문 애널리스트와의 상담만으로만 가입 가능<br>
						(1522-8302)
					</p>
				</div>
			</div>
		</div>
	</div>

	<div class="dux_2">
		<div class="deluxe_inner">
			<p class="dux_cm_txt1 dux2_txt01">디럭스 그룹만의<br>독특하고 세밀한 분석 패턴</p>
			<p class="dux_cm_txt2 dux2_txt02">
				<?if(!G5_IS_MOBILE){?>
				체계적인 분석 스토리로 빠르고 정확한 결과를 창출해냅니다!
				<?}else{?>
				체계적인 분석 스토리로<br>빠르고 정확한 결과를 창출해냅니다!
				<?}?>
			</p>
			<p class="dux_2_img"><img src="<?=G5_THEME_IMG_URL?>/deluxe_img3.png" alt=""></p>
		</div>
	</div>

	<div class="dux_3">
		<div class="deluxe_inner">
			<p class="dux_cm_txt1 dux3_txt01">시대 흐름에 따른 빠른 성능 개선</p>
			<p class="dux_cm_txt2 dux3_txt02">
				<?if(!G5_IS_MOBILE){?>
				데이터 처리 속도 3배 가량 업그레이드 함으로써 당첨시기를 더욱 빨리 앞당겨드립니다!
				<?}else{?>
				데이터 처리 속도 3배 가량 업그레이드 함으로써<br>당첨시기를 더욱 빨리 앞당겨드립니다!
				<?}?>
			</p>
			<p class="dux_3_img"><img src="<?=G5_THEME_IMG_URL?>/deluxe_img4.jpg" alt=""></p>
		</div>
	</div>

</div>

<?
	include_once(G5_PATH."/_tail.php");
?>