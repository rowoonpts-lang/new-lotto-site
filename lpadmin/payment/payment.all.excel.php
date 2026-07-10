<?
include_once("./_common.php");
if($_SESSION['ss_step2'] != $config['cf_10']){
	die();
}
header( "Content-type: application/vnd.ms-excel" );   
header( "Content-type: application/vnd.ms-excel; charset=utf-8");  
header( "Content-Disposition: attachment; filename = paymentAll.xls" );   
header( "Content-Description: PHP4 Generated Data" );   

// 관리자 이름 송출
$sql_member = "select * from g5_member where 1=1 and mb_level >= 5";
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
	<th>회원명</th>
	<th>연락처</th>
	<th>아이디</th>
	<th>등급</th>
	<th>결제방법</th>
	<th>결제정보</th>
	<th>결제금액</th>
	<th>결제상태</th>
	<th>PG사</th>
	<th>결제일</th>
	<th>DB경로</th>
	<th>담당자</th>
</tr>
<?
	$sql_common = " from l_pay a LEFT JOIN g5_member d ON (a.confirm_user = d.mb_id), g5_member b, g5_member_etc c ";
	$sql_search = " where 1=1 and a.mb_id = c.mb_id and a.mb_id = b.mb_id and lp_status = '입금' ";
	$sql_order = " order by lp_pay_datetime desc ";

	if($sch_select){
		$sql_search .= " and {$sch_select} like '%{$sch_text}%' ";
	}else{
		$sql_search .= " and (b.mb_code like '%{$sch_text}%' or b.mb_name like '%{$sch_text}%' or a.mb_hp like '%{$sch_text}%' or a.mb_id like '%{$sch_text}%') ";
	}

	if($sch_mb_type){
		$sql_search .= " and b.mb_type = '{$sch_mb_type}' ";
	}

	if($start_date){
		$sql_search .= " and substr(lp_pay_datetime,1,10) >= substr('{$start_date}',1,10) ";
	}
	if($end_date){
		$sql_search .= " and substr(lp_pay_datetime,1,10) <= substr('{$end_date}',1,10) ";
	}

	if($mb_team){
		$sql_search .= " and d.mb_team = '{$mb_team}' ";
	}
	if($pay_method){
		if($pay_method == "무통장" || $pay_method == "신용카드"){
			$sql_search .= " and a.pay_method = '{$pay_method}' ";
		}else{
			$sql_search .= " and a.pay_company = '{$pay_method}' ";
		}
	}


	$sql = " select count(distinct a.lp_id) as cnt {$sql_common} {$sql_search} {$sql_order} ";

	$row = sql_fetch($sql);
	$total_count = $row['cnt'];


	$rows = 30;
	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함'

	$sql = " select sum(lp_price) sum {$sql_common} {$sql_search} and lp_status = '입금' {$sql_order} ";
	$row = sql_fetch($sql);
	$tot_amt = $row[sum];


	//$limit = " limit {$from_record}, {$rows} ";

	$sql = "select a.*,b.*,c.*, d.mb_team as emp_mb_team {$sql_common} {$sql_search} {$sql_order} {$limit}";

	$result = sql_query($sql);

	for($i=0; $row = sql_fetch_array($result); $i++){
		
?>
<tr>
	<td><?=$total_count-($page-1)*$rows-$i?></td>
	<td style="mso-number-format:'\@';"><?=$row['mb_code']?></td>
	<td><?=$row['mb_name']?></td>
	<td style="mso-number-format:'\@';"><?=$row['mb_hp']?></td>
	<td style="mso-number-format:'\@';"><?=$row['mb_id']?></td>
	<td><?=$row['mb_type']?></td>
	<td><?=$row['pay_method']?></td>
	<td>
		<?	if($row['pay_method'] == "무통장"){
				$mu_ary = explode(" ",$row['mu_num']);
				echo "<br>".$mu_ary[0]." (".$row['mu_mb_name'].")";
			}else{
				echo "<br>".$row['card_name']." (".$row['card_sell_mm']."개월)";
			}
		?>
	</td>
	<td><?=round($row['lp_price']/11*10)?></td>
	<td>
		<?
			if($row['lp_status'] == "입금"){
				echo "완료";
			}else if($row['lp_status'] == "주문"){
				echo "대기";
			}else{
				echo $row['lp_status'];
			}
		?>
	</td>
	<td><?
		if($row['pay_company'] == "수기결제" || $row['pay_company'] == "페이업(수기)" || $row['pay_company'] == "코리아(수기)" || $row['pay_company'] == "세이프(수기)" || $row['pay_company'] == "다모아(수기)" || $row['pay_company'] == "루멘(수기)" || $row['pay_company'] == "온미르(수기)" || $row['pay_company'] == "참좋은(수기)" || $row['pay_company'] == "페이츠(수기)" || $row['pay_company'] == "쇼페이(수기)" || $row['pay_company'] == "웰페이(수기)" || $row['pay_company'] == "오후(수기)"){
			echo $row['pay_company'];
		}else{
			if($row['pay_method']== "신용카드" && $row['pay_company'] == "웰컴페이먼츠"){
				echo "웰컴";
			}
			if($row['pay_method']== "신용카드" && $row['pay_company'] == "페이업"){
				echo "페이업";
			}
			if($row['pay_method']== "신용카드" && $row['pay_company'] == "페이링"){
				echo "페이업";
			}
		}
	?></td>
	<td><?=$row['lp_pay_datetime']?></td>
	<td><?=$row['mb_db']?></td>
	<td style="mso-number-format:'\@';"><?=$member_info[$row['confirm_user']]?></td>
	<td>
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