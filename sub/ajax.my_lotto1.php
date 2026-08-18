<?php
	include_once("_common.php");
	if(!$endTurn){
		$endTurn = getTurn()-1;
	}
?>
<div class="select_box">
	<div class="sel_rt">
		<select name="" id="" class="w91" onChange="fnCngTurn1(this.value)">
			<?php
				for($i=$endTurn; $i >= 700; $i--){
			?>
			<option value="<?=$i?>" <?php if($turn == $i){echo "selected";}?>><?=$i?>회차</option>
			<?php	}?>
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
		<?php
			$turn = isset($turn) ? (int) $turn : 0;
			$safe_member_id = sql_real_escape_string($member['mb_id']);

			$sql = "
				select
					lmc_id,
					draw_no,
					member_type,
					num1,
					num2,
					num3,
					num4,
					num5,
					num6,
					sms_status,
					result_rank,
					result_checked_at,
					created_at
				from l_member_combination
				where draw_no = '{$turn}'
				  and mb_id = '{$safe_member_id}'
				order by lmc_id asc
			";

			$result = sql_query($sql);
			$cnt = 0;
			for($i=0; $row = sql_fetch_array($result); $i++){
				$cnt++;
		?>
		<tr>
			<td><?=$turn?>회</td>
			<td><?=date("Y-m-d", strtotime($row['created_at']))?></td>
			<td><?=htmlspecialchars($row['member_type'], ENT_QUOTES, 'UTF-8')?></td>
			<td>
				<ul class="lotto_ball">
					<?php

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
				<?php if ($row['sms_status'] === 'sent') { ?>
				발송
				<?php } else { ?>
				미발송
				<?php } ?>
			</td>
			<!--td>확률분석</td-->
			<td><?php
				if (empty($row['result_checked_at'])) {
					echo '추첨대기';
				} elseif (
					isset($row['result_rank'])
					&& $row['result_rank'] !== null
					&& (int) $row['result_rank'] >= 1
					&& (int) $row['result_rank'] <= 5
				) {
					echo (int) $row['result_rank'] . '등';
				} else {
					echo '낙첨';
				}
				?></td>
		</tr>
		<?php	}?>
		<?php if($cnt < 1){?>
		<tr><td colspan="6">데이터가 없습니다.</td></tr>
		<?php }?>
	</table>
</div>
