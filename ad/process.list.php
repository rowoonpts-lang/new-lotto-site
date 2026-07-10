<?
	include_once("_common.php");

	include_once(G5_PATH."/head.sub.php");
	if(!$_SESSION['ad_id']){
		alert("로그인 후 이용바랍니다.", G5_URL."/ad/process.login.php");
	}

	error_reporting(E_ALL);
	ini_set("display_errors", 1);
	
	$sql = "select * from l_ad_user where lu_id = '{$_SESSION['ad_id']}'";
	$row = sql_fetch($sql);
	
?>
<link rel="stylesheet" href="<?=G5_URL?>/ad/style.css">

<div class="list_box">
	<table class="vertical_table">
	<tr>
		<th width="10%">매체사</th>
		<td width="10%"><?=$row['lu_name']?></td>
		<th width="10%">매체사 아이디</th>
		<td width="10%"><?=$row['lu_id']?></td>
		<th width="10%">매체사 코드</th>
		<td width="10%"><?=$row['lu_type']?></td>
		<th width="10%">광고 코드</th>
		<td width="10%"><?=$row['lu_code']?></td>
		<td style="text-align:center;">
			<ul class="list_box_mem_ul">
				<li><a href="<?=G5_URL?>/ad/doc.v1.php?idx=<?=Encrypt($row['idx'],'able','able')?>" target="_blank">연동문서</a></li>
				<li><a href="<?=G5_URL?>/ad/process.excel.php?idx=<?=Encrypt($row['idx'],'able','able')?>">엑셀다운</a></li>
				<li><a href="<?=G5_URL?>/ad/process.logout.php">로그아웃</a></li>
			</ul>
		</td>
	</tr>
	</table>

	<table class="vertical_table margin-top-20 center_data">
	<tr>
		<th width="5%">NO</th>
		<th width="5%">이름</th>
		<th width="10%">연락처</th>
		<th width="15%">비고1</th>
		<th width="15%">비고2</th>
		<th width="10%">등록일</th>
		<th width="10%">IP</th>
		<th>유입경로</th>
	</tr>
	<?
		$sql_common = " from l_ad_list ";
		$sql_search = " where 1=1 and lu_type = '{$row['lu_type']}' and lu_code = '{$row['lu_code']}' and del_yn = '0'";
		$sql_order = " order by ll_datetime desc ";

		$sql = " select count(distinct idx) as cnt {$sql_common} {$sql_search} {$sql_order} ";

		$row = sql_fetch($sql);
		$total_count = $row['cnt'];


		$rows = 30;
		$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
		if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
		$from_record = ($page - 1) * $rows; // 시작 열을 구함'


		$limit = " limit {$from_record}, {$rows} ";

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
	<?	}
		if($total_count < 1){
		?>
	<tr>
		<td colspan="8" class="text-center-class">등록된 내역이 없습니다.</td>
	</tr>
	<?	}?>
	</table>
	<div style="text-align:center;padding:20px 0px">
	<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); ?>
	</div>
</div>

<?
/*function add_hyphen($tel)
{
    $tel = preg_replace("/[^0-9]/", "", $tel);    // 숫자 이외 제거
    if (substr($tel,0,2)=='02')
        return preg_replace("/([0-9][2])([0-9]{3,4})([0-9][4])$/", "\\1-\\2-\\3", $tel);
    else if (strlen($tel)=='8' && (substr($tel,0,2)=='15' || substr($tel,0,2)=='16' || substr($tel,0,2)=='18'))
        // 지능망 번호이면
        return preg_replace("/([0-9][4])([0-9][4])$/", "\\1-\\2", $tel);
    else
        return preg_replace("/([0-9][3])([0-9]{3,4})([0-9][4])$/", "\\1-\\2-\\3", $tel);
}*/
?>