<tr style="background:<?=$colordata?>;">
	<td class="center"><?=$row[lm_datetime]?></td>
	<td class="center"><?=$row[mb_name]?></td>
	<td class="center" onclick="fnMemmberMemo('<?=base64_encode($row[mb_id])?>')" style="cursor:pointer"><?=$row[mb_id]?></td>
	<td class="center"><?=$row[mb_code]?></td>
	<td class="center"><?=$row[lm_alarm_type]?></td>
	<td class="center"><?=$row[lm_memo]?></td>
	<td class="center"><?=$row[lm_alarm_date]?></td>
	<td class="center"><?=$member_info[$row[from_mb_id]]?></td>
	<td class="center">
		<?if($row[lm_alarm_view] == "0"){?>
		<button type="button" class="btn btn-default" onClick="fnCheckAlarm('<?=$row[lm_id]?>','<?=base64_encode($row[mb_id])?>');">확인</button>
		<?}?>
	</td>
</tr>
