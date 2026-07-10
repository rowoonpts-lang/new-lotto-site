<?
include_once("./_common.php");

header( "Content-type: application/vnd.ms-excel" );   
header( "Content-type: application/vnd.ms-excel; charset=utf-8");  
header( "Content-Disposition: attachment; filename = lucky.xls" );   
header( "Content-Description: PHP4 Generated Data" );   

?>
<table border='1'>  
<tr>
	<th>NO</th>
	<th>회차</th>
	<th>회원코드</th>
	<th>이름</th>
	<th>아이디</th>
	<th>등급</th>
	<th>당첨결과</th>
	<th>결제일자</th>
	<th>번호발송시간</th>
	<th>수동발송여부</th>
</tr>
<?
	if(!$turn){
		$turn = getTurn();
	}

	$table = "l_turn_".$turn;


	$sql_common = " from {$table} a, g5_member b ";
	$sql_search = " where 1=1 and a.mb_id = b.mb_id and result not in ('','낙첨') ";
	$sql_order = " order by mb_code desc ";

	if($sch_select){
		$sql_search .= " and {$sch_select} like '%{$sch_text}%' ";
	}else{
		$sql_search .= " and (b.mb_code like '%{$sch_text}%' or b.mb_name like '%{$sch_text}%' or a.mb_hp like '%{$sch_text}%' or a.mb_id like '%{$sch_text}%') ";
	}

	if($sch_mb_type){
		$sql_search .= " and a.mb_type = '{$sch_mb_type}' ";
	}

	if($lucky_result && $lucky_result != '전체'){
		$sql_search .= " and result = '{$lucky_result}' ";
	}

	$sql = " select count(distinct a.lt_id) as cnt {$sql_common} {$sql_search} {$sql_order} ";

	$row = sql_fetch($sql);
	$total_count = $row['cnt'];


	$rows = 50;
	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함'


	//$limit = " limit {$from_record}, {$rows} ";

	$sql = "select *, a.mb_type as lucky_type, (select lp_pay_datetime from l_pay where 1=1 and mb_id = a.mb_id and lp_status = '입금' order by lp_pay_datetime desc limit 1) lp_pay_datetime {$sql_common} {$sql_search} {$sql_order} {$limit}";
	$result = sql_query($sql);

	for($i=0; $row = sql_fetch_array($result); $i++){
		$ball_text = "";
		$ball_text = $row[num1].",".$row[num2].",".$row[num3].",".$row[num4].",".$row[num5].",".$row[num6];				
?>
<tr>
	<td><?=$total_count-($page-1)*$rows-$i?></td>
	<td><?=$row['turn']?></td>
	<td style="mso-number-format:'\@';"><?=$row['mb_code']?></td>
	<td><?=$row['mb_name']?></td>
	<td style="mso-number-format:'\@';"><?=$row['mb_hp']?></td>
	<td><?=$row['lucky_type']?></td>
	<td>
		<?if($row['result']){echo $row['result'];}else{echo "-";}?>
	</td>
	<td><?=$row[lp_pay_datetime]?></td>
	<td><?=$row[lt_datetime]?></td>
	<td><?=$row['direct_yn']?></td>
</tr>
<?}?>
</table>
  
<?echo "<meta content=\"application/vnd.ms-excel; charset=UTF-8\" name=\"Content-type\"> ";  
//echo $EXCEL_STR;  
?>  