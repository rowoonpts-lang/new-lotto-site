<?
include_once("./_common.php");
if($_SESSION['ss_step2'] != $config['cf_10']){
	die();
}

header( "Content-type: application/vnd.ms-excel" );   
header( "Content-type: application/vnd.ms-excel; charset=utf-8");  
header( "Content-Disposition: attachment; filename = bbsList.xls" );   
header( "Content-Description: PHP4 Generated Data" );   

?>
<table border='1'>  
<tr>
	<th>NO</th>
	<th>연락처</th>
	<th>이름</th>
	<th>신청일</th>
	<th>중복횟수</th>
	<th>IP</th>
</tr>
<?
	$sql_common = " from l_res a ";
	$sql_search = " where 1=1   and lr_hp != '' and lr_type !='결제시도' ";
	$sql_order = " order by a.lr_datetime desc ";
	
	if($start_date){
		$sql_search .= " and substr(a.lr_datetime,1,10) >= substr('{$start_date}',1,10) ";
	}
	if($end_date){
		$sql_search .= " and substr(a.lr_datetime,1,10) <= substr('{$end_date}',1,10) ";
	}
	

	$sql = " select count(distinct a.lr_id) as cnt {$sql_common} {$sql_search} {$sql_order} ";


	$row = sql_fetch($sql);
	$total_count = $row['cnt'];


	$rows = 50;
	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함'


	//$limit = " limit {$from_record}, {$rows} ";

	$sql = "select a.*, (select count(lr_id) from l_res where 1=1 and lr_hp = a.lr_hp and lr_datetime < a.lr_datetime)+1 cnt {$sql_common} {$sql_search} {$sql_order} {$limit}";

	$result = sql_query($sql);

	for($i=0; $row=sql_fetch_array($result); $i++){
?>
<tr>
	<td><?=$total_count-($i-1)?></td>
	<td style="mso-number-format:'\@';"><?=$row['lr_hp']?></td>
	<td><?=$row['lr_name']?></td>
	<td><?=$row['lr_datetime']?></td>
	<td><?echo $row['cnt'];?></td>
	<td><?=$row['ip']?></td>
</tr>
<?}?>
</table>
  
<?echo "<meta content=\"application/vnd.ms-excel; charset=UTF-8\" name=\"Content-type\"> ";  
//echo $EXCEL_STR;  
?>  