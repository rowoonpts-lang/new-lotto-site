<?
include_once("./_common.php");

$idx = Decrypt($idx, 'able', 'able');
$sql = "select * from l_ad_user where idx = '{$idx}'";
$row = sql_fetch($sql);

header( "Content-type: application/vnd.ms-excel" );   
header( "Content-type: application/vnd.ms-excel; charset=utf-8");  
header( "Content-Disposition: attachment; filename = excel_".date('YmdHis')."_".$row['lu_id'].".xls" );   
header( "Content-Description: PHP4 Generated Data" );   

?> 
<table border='1'>  
<tr>
	<th>NO</th>
	<th>이름</th>
	<th>연락처</th>
	<th>비고1</th>
	<th>비고2</th>
	<th>등록일</th>
	<th>IP</th>
	<th>유입경로</th>
</tr>
<?
$sql_common = " from l_ad_list ";
$sql_search = " where 1=1 and lu_type = '{$row['lu_type']}' and lu_code = '{$row['lu_code']}' ";
$sql_order = " order by ll_datetime desc ";

$sql = " select count(distinct idx) as cnt {$sql_common} {$sql_search} {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = 1000000000;

$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함'

$sql = "select * {$sql_common} {$sql_search} {$sql_order} {$limit}"; 
$result = sql_query($sql);
for($i=0; $row = sql_fetch_array($result); $i++){
?>
<tr>
	<td class="text-center-class"><?=$total_count-($page-1)*$rows-$i?></td>
	<td class="text-center-class"><?=$row['name']?></td>
	<td class="text-center-class"><?=add_hyphen($row['tel'])?></td>
	<td><?=$row['etc1']?></td>
	<td><?=$row['etc2']?></td>
	<td class="text-center-class"><?=$row['ll_datetime']?></td>
	<td class="text-center-class"><?=$row['ip']?></td>
	<td><?=$row['refer']?></td>
</tr>
<?
}
?>
</table>
<?
echo "<meta content=\"application/vnd.ms-excel; charset=UTF-8\" name=\"Content-type\"> ";  

function add_hyphen($tel)
{
    $tel = preg_replace("/[^0-9]/", "", $tel);    // 숫자 이외 제거
    if (substr($tel,0,2)=='02')
        return preg_replace("/([0-9][2])([0-9]{3,4})([0-9][4])$/", "\\1-\\2-\\3", $tel);
    else if (strlen($tel)=='8' && (substr($tel,0,2)=='15' || substr($tel,0,2)=='16' || substr($tel,0,2)=='18'))
        // 지능망 번호이면
        return preg_replace("/([0-9][4])([0-9][4])$/", "\\1-\\2", $tel);
    else
        return preg_replace("/([0-9][3])([0-9]{3,4})([0-9][4])$/", "\\1-\\2-\\3", $tel);
}
?>  