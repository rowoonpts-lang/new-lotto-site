<?
include_once("./_common.php");
if($_SESSION['ss_step2'] != $config['cf_10']){
	die();
}
header( "Content-type: application/vnd.ms-excel" );   
header( "Content-type: application/vnd.ms-excel; charset=utf-8");  
header( "Content-Disposition: attachment; filename = paymentMu.xls" );   
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
	<th>기존신청등급</th>
	<th>현재등급</th>
	<th>결제금액</th>
	<th>결제상태</th>
	<th>담당자</th>
	<th>신청일</th>
	<th>결제일</th>
</tr>
<?
	$sql_common = " from l_pay a, g5_member b ";
	$sql_search = " where 1=1 and a.mb_id = b.mb_id and pay_method = '무통장' and lp_status = '주문' ";
	$sql_order = " order by lp_datetime desc ";

	if($sch_select){
		$sql_search .= " and {$sch_select} like '%{$sch_text}%' ";
	}else{
		$sql_search .= " and (b.mb_code like '%{$sch_text}%' or b.mb_name like '%{$sch_text}%' or a.mb_hp like '%{$sch_text}%' or a.mb_id like '%{$sch_text}%') ";
	}

	if($sch_mb_type){
		$sql_search .= " and mb_type = '{$sch_mb_type}' ";
	}

	if($start_date){
		$sql_search .= " and lp_datetime >= substr('{$start_date}',1,10) ";
	}
	if($end_date){
		$sql_search .= " and lp_datetime <= substr('{$end_date}',1,10) ";
	}

	

	$sql = " select count(distinct a.lp_id) as cnt {$sql_common} {$sql_search} {$sql_order} ";

	$row = sql_fetch($sql);
	$total_count = $row['cnt'];


	$rows = 10;
	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함'


	//$limit = " limit {$from_record}, {$rows} ";

	$sql = "select * {$sql_common} {$sql_search} {$sql_order} {$limit}";
	$result = sql_query($sql);

	for($i=0; $row = sql_fetch_array($result); $i++){
		
?>
<tr>
	<td><?=$total_count-($page-1)*$rows-$i?></td>
	<td style="mso-number-format:'\@';"><?=$row['mb_code']?></td>
	<td><?=$row['mb_name']?></td>
	<td style="mso-number-format:'\@';"><?=$row['mb_hp']?></td>
	<td style="mso-number-format:'\@';"><?=$row['mb_id']?></td>
	<td><?=$row['lp_type']?></td>
	<td><?=$row['mb_type']?></td>
	<td><?=round($row['lp_price']/11*10)?></td>
	<td>
		<?
			if($row['lp_status'] == "입금"){
				echo "완료";
			}else{
				echo $row['lp_status'];
			}
		?>
	</td>
	<td style="mso-number-format:'\@';"><?=$member_info[$row['emp_id']]?></td>
	<td><?=$row['lp_datetime']?></td>
	<td><?=$row['lp_pay_datetime']?></td>
</tr>
<?}?>
</table>
  
<?echo "<meta content=\"application/vnd.ms-excel; charset=UTF-8\" name=\"Content-type\"> ";  
//echo $EXCEL_STR;  
?>  