<?
	include_once("_common.php");
	if(!$endTurn){
		$endTurn = getTurn()-1;
	}
?>
<div class="select_box">
	<div class="sel_rt">
		<select name="" id="" class="w91" onChange="fnCngTurn1(this.value)">
			<?
				for($i=$endTurn; $i >= 700; $i--){
			?>
			<option value="<?=$i?>" <?if($turn == $i){echo "selected";}?>><?=$i?>회차</option>
			<?	}?>
		</select>
	</div>
</div>
<div class="sts_scr">
	<table class="sts_tb">
		<tr>
			<th>회차</th>
			<th>발급일</th>
			<th>시스템</th>
			<th>조합번호</th>
			<th>SMS 발송여부</th>
			<!--th>확률분석</th-->
			<th>결과</th>
		</tr>
		<?
			$sql = "select * from l_turn_{$turn} where 1=1 and mb_id = '{$member[mb_id]}'";

			$result = sql_query($sql);
			$cnt = 0;
			for($i=0; $row = sql_fetch_array($result); $i++){
				$cnt++;
		?>
		<tr>
			<td><?=$turn?>회</td>
			<td><?=date("Y-m-d",strtotime($row[lt_datetime]))?></td>
			<td><?=$row[mb_type]?></td>
			<td>
				<ul class="lotto_ball">
					<?
						
						$listText = $row['num1'].",".$row['num2'].",".$row['num3'].",".$row['num4'].",".$row['num5'].",".$row['num6'];
						echo getBallStyle3($listText);
					?>
					<!--li class="bg_yellow">8</li>
					<li class="bg_sky">19</li>
					<li class="bg_sky">20</li>
					<li class="bg_red">21</li>
					<li class="bg_gray">33</li>
					<li class="bg_gray">39</li-->
				</ul>
			</td>
			<td>
				<?if($row[mb_type] == "무료회원"){?>
				미발송
				<?}else{?>
				발송
				<?}?>
			</td>
			<!--td>확률분석</td-->
			<td><?=$row['result']?></td>
		</tr>
		<?	}?>
		<?if($cnt < 1){?>
		<tr><td colspan="6">데이터가 없습니다.</td></tr>
		<?}?>
	</table>
</div>