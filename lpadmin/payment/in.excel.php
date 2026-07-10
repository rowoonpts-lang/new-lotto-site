<?
include_once("./_common.php");

header( "Content-type: application/vnd.ms-excel" );   
header( "Content-type: application/vnd.ms-excel; charset=utf-8");  
header( "Content-Disposition: attachment; filename = paymentAll.xls" );   
header( "Content-Description: PHP4 Generated Data" );   

// 관리자 이름 송출
$sql_member = "select * from g5_member where 1=1 and mb_level > 5";
$result_member = sql_query($sql_member);
$member_info = array();
for($i=0; $row_info = sql_fetch_array($result_member); $i++){
	$member_info[$row_info[mb_id]] = $row_info[mb_name].$row_info[mb_team];
}	

?>
<table border='1'>  
<tr>
	<th>NO</th>
	<th>회원코드</th>
	<th>회원명/연락처</th>
	<th>아이디</th>
	<th>등급</th>
	<th>결제방법</th>
	<th>결제금액</th>
	<th>결제일</th>
	<th>승인일</th>
	<th>DB경로</th>
	<th>승인자</th>
</tr>
<?
	$sql_common = " from l_pay_in a, g5_member b, g5_member_etc c ";
	$sql_search = " where 1=1 and a.mb_id = c.mb_id and a.mb_id = b.mb_id and lp_status = '입금' ";
	$sql_order = " order by confirm_in_datetime desc ";

	if($sch_select){
		$sql_search .= " and {$sch_select} like '%{$sch_text}%' ";
	}else{
		$sql_search .= " and (b.mb_code like '%{$sch_text}%' or b.mb_name like '%{$sch_text}%' or a.mb_hp like '%{$sch_text}%' or a.mb_id like '%{$sch_text}%') ";
	}

	if($sch_mb_type){
		if($sch_mb_type == "S1"){
			$sql_search .= " and in_type = '1' ";
		}else{
			$sql_search .= " and in_type = '2' ";
		}
	}

	if($start_date){
		$sql_search .= " and substr(confirm_in_datetime,1,10) >= substr('{$start_date}',1,10) ";
	}
	if($end_date){
		$sql_search .= " and substr(confirm_in_datetime,1,10) <= substr('{$end_date}',1,10) ";
	}

	

	$sql = " select count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";

	$row = sql_fetch($sql);
	$total_count = $row['cnt'];


	$rows = 30;
	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함'

	$sql = " select sum(in_price) sum {$sql_common} {$sql_search} and lp_status = '입금' {$sql_order} ";
	$row = sql_fetch($sql);
	$tot_amt = $row[sum];


	//$limit = " limit {$from_record}, {$rows} ";

	$sql = "select * {$sql_common} {$sql_search} {$sql_order} {$limit}";
	$result = sql_query($sql);

	for($i=0; $row = sql_fetch_array($result); $i++){
		
?>
<tr>
	<td><?=$total_count-($page-1)*$rows-$i?></td>
	<td><a href="<?=G5_LADMIN_URL?>/member/member.all.php?sch_select=a.mb_code&sch_text=<?=$row['mb_code']?>"><?=$row['mb_code']?></td>
	<td>
		<?=$row['mb_name']?><br>
		<?=$row['mb_hp']?>
	</td>
	<td><a href=""  onclick="fnMemmberMemo('<?=base64_encode($row[mb_id])?>')"><?=$row['mb_id']?></a></td>
	<td><?=$row['mb_type']?></td>
	<td>
		<?=$row['pay_method']?>
		<?	if($row['pay_method'] == "무통장"){
				$mu_ary = explode(" ",$row['mu_num']);
				echo "<br>".$mu_ary[0]." (".$row['mu_mb_name'].")";
			}else{
				echo "<br>".$row['card_name']." (".$row['card_sell_mm']."개월)";
			}
		?>
	</td>
	<td><?=number_format($row['in_price'])?></td>
	<td><?=$row['lp_pay_datetime']?></td>
	<td><?=$row['confirm_in_datetime']?></td>
	<td><?=$row['mb_db']?></td>
	<td>
		<?
			if($row[in_type] == "1"){
				echo $member_info[$row['in_mb_id']];
			}
			if($row[in_type] == "2"){
				echo $member_info[$row['confirm_in1']]."/".$member_info[$row['confirm_user']];
			}
		?>
	</td>
	<td>
		<?if($row[lp_status] != "취소"){?>
		<button type="button" class="btn btn-danger" onClick="fnPayInCancel('<?=$row[lp_id]?>')">취소</button>
		<?}?>
		
		<?if($row[lp_status] == "취소"){?>
			<?=$row[lp_cancel_datetime]?>
		<?}?>
	</td>
</tr>
<?}?>
</table>
  
<?echo "<meta content=\"application/vnd.ms-excel; charset=UTF-8\" name=\"Content-type\"> ";  
//echo $EXCEL_STR;  
?>  