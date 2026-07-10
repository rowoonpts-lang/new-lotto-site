<?
	include_once("_common.php");
	include_once(G5_PATH."/_head.php");
?>
<div class="sub_top sub_top_bg2 prize_sub">
	<div class="s01_li1">LOTTO JOONGSI<span>M</span></div>
	<div class="s01_li2">당첨지역</div>
		<ul class="product_ul2 prize_top">
			<li id="view_turn_result">
				<?
					$turn = getTurn()-1;
					include_once(G5_PATH."/sub/ajax.turn.list.view2.php"); 
				?>
				
			</li>
		</ul>
</div>
<div class="sub_tit">당첨판매점</div>
<div class="inner_3">
	<div id="prize">
		<ul class="prz_ul">
			<li class="active"><a href="<?=G5_URL?>/sub/prize.php?type=1">서울</a></li>
			<li><a href="<?=G5_URL?>/sub/prize.php?type=2">인천·경기</a></li>
			<li><a href="<?=G5_URL?>/sub/prize.php?type=3">대전·충남</a></li>
			<li><a href="<?=G5_URL?>/sub/prize.php?type=4">대구·경북</a></li>
			<li><a href="<?=G5_URL?>/sub/prize.php?type=5">부산·경남</a></li>
			<li><a href="<?=G5_URL?>/sub/prize.php?type=6">광주·전라</a></li>
			<li><a href="<?=G5_URL?>/sub/prize.php?type=7">강원</a></li>
		</ul>

		<table class="sts_tb">
			<tr>
				<th>판매점</th>
				<th>배출건수</th>
				<th>주소</th>
			</tr>
			<tr>
				<td>스파</td>
				<td>132</td>
				<td>
					서울 노원구 상계동 666-3 주공10단지종합상가111</p>
					<a href="" class="prz_loc">
						<img src="<?=G5_THEME_IMG_URL?>/map_icon.png">
					</a>
				</td>
			</tr>
			<tr>
				<td>부일카서비스</td>
				<td>97</td>
				<td>
					부산 동구 범일동 830-195번지
					<a href="" class="prz_loc">
						<img src="<?=G5_THEME_IMG_URL?>/map_icon.png">
					</a>
				</td>
			</tr>
			<tr>
				<td>제이복권방</td>
				<td>69</td>
				<td>
					서울 종로구 종로5가 58번지 평창빌딩 1층 103호
					<a href="" class="prz_loc">
						<img src="<?=G5_THEME_IMG_URL?>/map_icon.png">
					</a>
				</td>
			</tr>
			<tr>
				<td>목화휴게소</td>
				<td>58</td>
				<td>
					경남 사천시 용현면 주문리 4
					<a href="" class="prz_loc">
						<img src="<?=G5_THEME_IMG_URL?>/map_icon.png">
					</a>
				</td>
			</tr>
			<tr>
				<td>로또명당인주점</td>
				<td>51</td>
				<td>
					충남 아산시 인주면 신성리 188-8
					<a href="" class="prz_loc">
						<img src="<?=G5_THEME_IMG_URL?>/map_icon.png">
					</a>
				</td>
			</tr>
		</table>

		<div class="more_btn">
			<a href="">더 많은 당첨 판매점 보기</a>
		</div>
	</div>
</div>
<?
	include_once(G5_PATH."/_tail.php");
?>