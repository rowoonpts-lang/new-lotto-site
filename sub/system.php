<?
	include_once("_common.php");
	include_once(G5_PATH."/_head.php");
?>

<div id="system">
	<div class="inner">
		<p class="st_desc">
			<?if(!G5_IS_MOBILE){?>
			언제까지 행운만 기다리겠습니까? 행운은 행동하는 사람에게 다가옵니다
			<?}else{?>
			언제까지 행운만 기다리겠습니까?<br>행운은 행동하는 사람에게 다가옵니다
			<?}?>
		</p>
		<p class="st_desc2">이제껏 경험하지 못한 로또피크의</p>
		<h2 class="st_h2 font_gm_b">Real Time Analysis System</h2>
		<p class="st_desc2 st_desc3">
			<span><b>데이터의 통계분석·데이터 처리속도·</b></span><b>데이터 가치성</b>이 결과의 차이를 냅니다!
		</p>
		<div class="st_process_box st_style_1">
			<ul class="st_process st_process_1">
				<?for($i=1; $i<=4; $i++){?>
				<li><img src="<?=G5_THEME_IMG_URL?>/system_img<?=$i?>.svg"></li>
				<?}?>
			</ul>
			<ul class="st_process st_process_2">
				<?for($i=5; $i<=9; $i++){?>
				<li><img src="<?=G5_THEME_IMG_URL?>/system_img<?=$i?>.svg"></li>
				<?}?>
			</ul>
			<p class="st_plus"><img src="<?=G5_THEME_IMG_URL?>/system_plus.png"></p>
			<ul class="st_process st_process_3">
				<?for($i=10; $i<=11; $i++){?>
				<li><img src="<?=G5_THEME_IMG_URL?>/system_img<?=$i?>.svg"></li>
				<?}?>
			</ul>
		</div>
		<div class="st_process_box st_style_2">
			<ul class="st_process st_process_1">
				<?for($i=1; $i<=3; $i++){?>
				<li><img src="<?=G5_THEME_IMG_URL?>/system_img<?=$i?>.svg"></li>
				<?}?>
			</ul>
			<ul class="st_process st_process_2">
				<?for($i=4; $i<=6; $i++){?>
				<li><img src="<?=G5_THEME_IMG_URL?>/system_img<?=$i?>.svg"></li>
				<?}?>
			</ul>
			<ul class="st_process st_process_4">
				<?for($i=7; $i<=9; $i++){?>
				<li><img src="<?=G5_THEME_IMG_URL?>/system_img<?=$i?>.svg"></li>
				<?}?>
			</ul>
			<p class="st_plus"><img src="<?=G5_THEME_IMG_URL?>/system_plus.png"></p>
			<ul class="st_process st_process_3">
				<?for($i=10; $i<=11; $i++){?>
				<li><img src="<?=G5_THEME_IMG_URL?>/system_img<?=$i?>.svg"></li>
				<?}?>
			</ul>
		</div>
		<p class="st_bot_desc"><span>고객조합관리시스템으로</span> <span>철저한 피드백을 통해 지속적 당첨관리!</span></p>
	</div>
</div>

<?
	include_once(G5_PATH."/_tail.php");
?>