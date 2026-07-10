<?
	include_once("_common.php");
	include_once(G5_PATH."/head.sub.php");

	if(!$endTurn){
		$endTurn = getTurn()-1;
	}
	if(!$ver){
		$ver = '1';
	}
	//$list = getLuckyNum($turn);
	$list = getLuckyNum($turn);
	$listText = $list['drwtNo1'].",".$list['drwtNo2'].",".$list['drwtNo3'].",".$list['drwtNo4'].",".$list['drwtNo5'].",".$list['drwtNo6'].",".$list['bnusNo'];
?>
<div class="flex_space">
	<div class="product_li1_1"><?=$turn?>회차&nbsp;<b>당첨번호</b><em>추첨일 : <?=date("Y.m.d", strtotime($list['drwNoDate']))?></em></div>
	<!--div class="product_li1_1"><?=$turn?>회차&nbsp;<b>당첨번호</b><em>추첨일 : 2020-10-24</em></div-->
	<div class="product_li1_2">
		<select onChange="fnCngTurn(this.value, '<?=$ver?>')">
			<?for($i=$endTurn; $i >= 700; $i--){?>
			<option value="<?=$i?>" <?if($turn == $i){echo "selected";}?>><?=$i?>회차</option>
			<?}?>
		</select>
	</div>
</div>
<div class="product_li1_3">
	<ul>
		<?
			echo getBallStyle2($listText, $ver);
		?>
		<!--li class="bg_yellow">1</li>
		<li class="bg_yellow">3</li>
		<li class="bg_red">30</li>
		<li class="bg_gray">33</li>
		<li class="bg_gray">36</li>
		<li class="bg_gray">39</li>
		<li class="bg_blue">12</li-->
	</ul>
</div>
<div class="product_li1_4">
	<a class="joong_a" href="http://www.imbc.com/broad/tv/culture/lotto/index.html" target="_blank">
		<img src="<?=G5_THEME_IMG_URL?>/joong_a1.png" alt="">
		<span>MBC 행복드림 동행복권 추첨방송 다시보기</span>
		<img class="joong_a2" src="<?=G5_THEME_IMG_URL?>/joong_a2.png" alt="">
	</a>
	<!-- <ul>
		<li><span>1등</span>&nbsp;<?=$list[firstPrzwnerCo]?>명&nbsp;<?=number_format($list['firstAccumamnt'])?>원</li>
		<li><span>2등</span>&nbsp;72명&nbsp;47,048,395원</li>
		<li><span>3등</span>&nbsp;2,923명&nbsp;1,158,907원</li>
	</ul> -->
</div>
