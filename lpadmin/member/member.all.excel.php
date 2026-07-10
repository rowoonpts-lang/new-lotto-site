<?
include_once("./_common.php");
if($_SESSION['ss_step2'] != $config['cf_10'] || !$is_admin){
	die();
}
header( "Content-type: application/vnd.ms-excel" );   
header( "Content-type: application/vnd.ms-excel; charset=utf-8");  
header( "Content-Disposition: attachment; filename = memberAll.xls" );   
header( "Content-Description: PHP4 Generated Data" );   

?>
<table border='1'>  
<tr>
	<th>NO</th>
	<th>회원코드</th>
	<th>회원명</th>
	<th>연락처</th>
	<th>아이디</th>
	<th>등급</th>
	<th>종료등급</th>
	<th>남은기간</th>
	<th>요일</th>
	<th>남은조합</th>
	<th>가입일</th>
	<th>최근접속일</th>
	<th>약관동의</th>
	<th>디비경로</th>
	<th>상태</th>
</tr>
<?
	$sql_common = " from g5_member a, g5_member_etc b ";
	$sql_search = " where 1=1 and a.mb_id = b.mb_id and a.mb_id != 'admin' and mb_level < 5 ";
	$sql_order = " order by mb_datetime desc ";

	if($sch_select){
		if($sch_select == "a.mb_code"){
			$sql_search .= " and {$sch_select} = '{$sch_text}' ";
		}else{
			$sql_search .= " and {$sch_select} like '%{$sch_text}%' ";
		}
	}else{
		$sql_search .= " and (a.mb_code like '%{$sch_text}%' or a.mb_name like '%{$sch_text}%' or a.mb_hp like '%{$sch_text}%' or a.mb_id like '%{$sch_text}%') ";
	}

	if($sch_mb_type){
		if($sch_mb_type != "종료등급"){
			if($sch_mb_type == "일시정지"){
				$sql_search .= " and left_day > 0 ";
			}else{
				$sql_search .= " and mb_type = '{$sch_mb_type}' and left_day < 1 ";				
			}
		}else if($sch_mb_type == "종료등급"){
			$sql_search .= " and free_pre_type != '' ";
		}
	}

	if($sch_mb_db){
		$sql_search .= " and mb_db = '{$sch_mb_db}' ";
	}

	if($start_date){
		$sql_search .= " and substr(mb_datetime,1,10) >= substr('{$start_date}',1,10) ";
	}
	if($end_date){
		$sql_search .= " and substr(mb_datetime,1,10) <= substr('{$end_date}',1,10) ";
	}
	if($sch_mb_status){
		$sql_search .= " and recent_select = '{$sch_mb_status}' ";
	}



	$sql = " select count(distinct a.mb_id) as cnt {$sql_common} {$sql_search} {$sql_order} ";

	$row = sql_fetch($sql);
	$total_count = $row['cnt'];


	$rows = 10;
	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함'


	//$limit = " limit {$from_record}, {$rows} ";

	$sql = "select * {$sql_common} {$sql_search} {$sql_order} {$limit}";
	$result = sql_query($sql);



for($i=1; $row=sql_fetch_array($result); $i++){
?>
<tr>
	<td><?=$total_count-($i-1)?></td>
	<td style="mso-number-format:'\@';"><?=$row['mb_code']?></td>
	<td><?=$row['mb_name']?></td>
	<td style="mso-number-format:'\@';"><?=$row['mb_hp']?></td>
	<td style="mso-number-format:'\@';"><?=$row['mb_id']?></td>
	<td>
		<?
			if($row['left_day'] > 0){
				echo "일시정지";
			}else{
				echo $row['mb_type'];
			}
		?>
	</td>
	<td><?=$row['free_pre_type']?></td>
	<td>
		<?
			if($row['left_day'] < 1){
				if(intval((strtotime($row[end_date]) - strtotime(date("Y-m-d"))) / 86400) > 0){
					echo intval((strtotime($row[end_date]) - strtotime(date("Y-m-d"))) / 86400);
				}else{
					echo "0";
				}
		}else{
			echo $row['left_day'];							
		}
		?>일
		<?//if($row[start_date]&& $row[end_date]){echo intval((strtotime($row[end_date]) - strtotime($row[start_date])) / 86400);}?>
	</td>
	<td>
		<?
			$tot_num = 0;
			$tot_text = "";
			$tot_num = $row['num_mon']+$row['num_tue']+$row['num_wed']+$row['num_thur']+$row['num_fri']+$row['num_sat'];
			$totAry = array('num_mon','num_tue','num_wed','num_thur','num_fri','num_sat');
			$totAryKor = array('월','화','수','목','금','토');
			for($k=0; $k < count($totAry); $k++){
				if($row[$totAry[$k]] > 0){
					if($tot_text){$tot_text.= " / ";}
					$tot_text.= $totAryKor[$k]." : ".$row[$totAry[$k]];
				}
			}
			
		?>
		<?=$tot_text?>
	</td>
	<td><?=($tot_num-$row[use_num])?></td>
	<td><?=$row[mb_datetime]?></td>
	<td><?=$row[mb_today_login]?></td>
	<td>
		<?
			if(!$row[mb_yak]){
				echo "N";
			}else{
				echo $row['mb_yak'];
			}
		?>
	</td>
	<td><?=str_replace("homepage","home",$row[mb_db])?></td>
	<td>
		<?if($row[recent_select]){?>
			<?=$row[recent_select]?>
		<?}?>
	</td>
</tr>
<?
}
?>
</table>
  
<?echo "<meta content=\"application/vnd.ms-excel; charset=UTF-8\" name=\"Content-type\"> ";  
//echo $EXCEL_STR;  
?>  