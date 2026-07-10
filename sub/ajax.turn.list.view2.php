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
	//print_r(getLuckyNumCurl($turn));
	$list = getLuckyNum($turn);
	$listText = $list['drwtNo1'].",".$list['drwtNo2'].",".$list['drwtNo3'].",".$list['drwtNo4'].",".$list['drwtNo5'].",".$list['drwtNo6'].",".$list['bnusNo'];
?>

<div class="flex_center">
	<div class="product_li1_1"><?=$turn?>&nbsp;<b>당첨번호</b><em>추첨일 : <?=date("Y.m.d", strtotime($list['drwNoDate']))?></em></div>
	<!--div class="product_li1_1"><?=$turn?>&nbsp;<b>당첨번호</b><em>추첨일 : 2020-10-24</em></div-->
	<div class="product_li1_2">
		<select onChange="fnCngTurn2(this.value, '<?=$ver?>')">
		<?for($i=$endTurn; $i >= 700; $i--){?>
		<option value="<?=$i?>" <?if($turn == $i){echo "selected";}?>><?=$i?>회차</option>
		<?}?>
	</select>
	</div>
</div>
<div class="product_li1_3">
	<ul class="flex_center">
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