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
<h2 class="mid01_h2">
	<span><?=$turn?></span>회차 동행복권 당첨결과 <span class="mid01_h2_bar">ㅣ</span> <span class="mid01_h2_day"><?=date("Y-m-d", strtotime($list['drwNoDate']))?></span>
</h2>
<div class="lotto_ball mid01_1_border">
	<?=getBallStyle2($listText, $ver)?>
</div>
<div class="lotto_prize mid01_1_border">
	<ul class="lotto_prize_list">
		<li>
			<p class="lotto_prize_1">1등</p>
			<p class="lotto_prize_2"><?=$config['cf_etc_1_1']?>원</p>
			<p class="lotto_prize_3"></p>
			<p class="lotto_prize_4"><span>당첨자수</span> <?=$config['cf_etc_1']?>명</p>
		</li>
		<li>
			<p class="lotto_prize_1">2등</p>
			<p class="lotto_prize_2"><?=$config['cf_etc_2_1']?>원</p>
			<p class="lotto_prize_3 lotto_prize_3_2"></p>
			<p class="lotto_prize_4"><span>당첨자수</span> <?=$config['cf_etc_2']?>명</p>
		</li>
		<li>
			<p class="lotto_prize_1">3등</p>
			<p class="lotto_prize_2"><?=$config['cf_etc_3_1']?>원</p>
			<p class="lotto_prize_3 lotto_prize_3_3"></p>
			<p class="lotto_prize_4"><span>당첨자수</span> <?=$config['cf_etc_3']?>명</p>
		</li>
	</ul>
</div>
<ul class="lotto_money">
	<li>
		<p class="lotto_money_1">추첨 누적 판매금</p>
		<p class="lotto_money_2"><b>96,392,975,507</b> 원</p>
	</li>
	<li>
		<p class="lotto_money_1">1등 총 당첨금</p>
		<p class="lotto_money_2"><b>23,392,975,507</b> 원</p>
	</li>
</ul>