
<? 
/****************************** 
달력 
******************************/ 

/********** 사용자 설정값 **********/ 
$startYear        = 2007; 
$endYear        = date( "Y" ) + 4; 

/********** 입력값 **********/ 
$year            = ( $_GET['toYear'] )? $_GET['toYear'] : date( "Y" ); 
$month            = ( $_GET['toMonth'] )? $_GET['toMonth'] : date( "m" ); 
$doms            = array( "일", "월", "화", "수", "목", "금", "토" ); 

/********** 계산값 **********/ 
$mktime            = mktime( 0, 0, 0, $month, 1, $year );      // 입력된 값으로 년-월-01을 만든다 
$days            = date( "t", $mktime );                        // 현재의 year와 month로 현재 달의 일수 구해오기 
$startDay        = date( "w", $mktime );                        // 시작요일 알아내기 

// 지난달 일수 구하기 
$prevDayCount    = date( "t", mktime( 0, 0, 0, $month, 0, $year ) ) - $startDay + 1; 

$nowDayCount    = 1;                                            // 이번달 일자 카운팅 
$nextDayCount    = 1;                                          // 다음달 일자 카운팅 

// 이전, 다음 만들기 
$prevYear        = ( $month == 1 )? ( $year - 1 ) : $year; 
$prevMonth        = ( $month == 1 )? 12 : ( $month - 1 ); 
$nextYear        = ( $month == 12 )? ( $year + 1 ) : $year; 
$nextMonth        = ( $month == 12 )? 1 : ( $month + 1 ); 

// 출력행 계산 
$setRows	= ceil( ( $startDay + $days ) / 7 ); 

$add_query = "";
if($member[mb_level] < 7){
	if($member[mb_level] == 6){
		$add_query = " and mb_team = '{$member['mb_team']}'  ";
	}else{
		$add_query = " and mb_id = '{$member[mb_id]}'  ";
	}
}else{
	$add_query = " and mb_team = '{$member['mb_team']}'  ";
}

if($is_admin){
	$add_query = "  ";
}

$toDatePad = str_pad($year, 4, "0", STR_PAD_LEFT)."-".str_pad($month, 2, "0", STR_PAD_LEFT);
$sql = "select from_mb_id as mb_id, substr(lm_alarm_date,1,10) mm_date, count(*) mm_cnt
		from l_memo a, g5_member b
		where 1=1 
			and a.from_mb_id = b.mb_id
			and st_tp = '1'
			and lm_alarm_view = '0'
			and substr(lm_alarm_date,1,7) = '{$toDatePad}' 
			and b.mb_level >= 5
		group by from_mb_id, substr(lm_alarm_date,1,10)";
$result = sql_query($sql);

$dataDate = array();
$dataCnt = array();
for($i =0; $row= sql_fetch_array($result); $i++){
	$dataCnt[$row[mm_date]][$row[mb_id]] = $row[mm_cnt];
}
//print_r($dataCnt);

$sql = "select * 
		from g5_member 
		where 1=1
			{$add_query}
			and mb_level in (5,6,7,8,9,10)

		";
$result = sql_query($sql);
$memberAry = array();
$memberNameAry = array();
for($i=0; $row= sql_fetch_array($result); $i++){
	$memberAry[$i] = $row[mb_id];
	$memberNameAry[$i] = $row[mb_name].$row[mb_team];
}



?> 

<!---------- 달력 출력 ----------> 
<style>
.cal_tbl {width:95%;margin:20px auto;} 
.cal_tbl th {border-bottom:2px solid #777}
.cal_tbl td {border-bottom:2px solid #777;border-right:2px solid #777}
.cal_tbl td:last-child {border-right:0px}
</style>


<table style="border-collapse:collapse;margin-top:30px" class="cal_month_btn"> 
    <tr> 
        <td style="padding:10;border-width:0;border-style:solid;"> 
        <input type="button" onclick="location.href='<?=$_SERVER['PHP_SELF']?>?toYear=<?=$prevYear?>&toMonth=<?=$prevMonth?>'" value=" < "> 
        <?=$year?>년 <?=$month?>월 
        <input type="button" onclick="location.href='<?=$_SERVER['PHP_SELF']?>?toYear=<?=$nextYear?>&toMonth=<?=$nextMonth?>'" value=" > "> 
        </td> 
    </tr> 
</table> 

<br> 

<table cellpadding=0 cellspacing=0 style="border-collapse:collapse;" class="cal_tbl"> 

    <tr> 
        <? for( $i = 0; $i < count( $doms ); $i++ ) { ?> 
        <td align="center" style=""><?=$doms[$i]?>요일</td> 
        <? } ?> 
    </tr> 

    <? for( $rows = 0; $rows < $setRows; $rows++ ) { ?> 
    <tr> 
        <? 
        for( $cols = 0; $cols < 7; $cols++ ) 
        { 
            // 셀 인덱스 만들자 
            $cellIndex    = ( 7 * $rows ) + $cols; 
            ?> 

            <? 
            // 이번달이라면 
            if ( $startDay <= $cellIndex && $nowDayCount <= $days ) { ?> 
            <td align="center" style=""> 
                <? if ( date( "w", mktime( 0, 0, 0, $month, $nowDayCount, $year ) ) == 6 ) { ?> 
                <b><font color="blue"><?=$nowDayCount++?></font></b> 
                <?
					
					
					$pad_year = str_pad($year, 4, "0", STR_PAD_LEFT);
					$pad_month = str_pad($month, 2, "0", STR_PAD_LEFT);
					$pad_day = str_pad($nowDayCount-1, 2, "0", STR_PAD_LEFT);

				?>
				<div class="alarm_overflow">
				<?
					for($k=0; $k < count($memberNameAry); $k++){
				?>
					<a href="./<?=basename($_SERVER["PHP_SELF"]);?>?rowDate=<?=$pad_year."-".$pad_month."-".$pad_day?>&mb_id=<?=$memberAry[$k]?>">
					<div class="box_div"><?=$memberNameAry[$k]?><span><?=$dataCnt[$pad_year."-".$pad_month."-".$pad_day][$memberAry[$k]] ?></span></div>
					</a>
				<?}?>
				</div>
                <? } else if ( date( "w", mktime( 0, 0, 0, $month, $nowDayCount, $year ) ) == 0 ) { ?> 
                <b><font color="red"><?=$nowDayCount++?></font></b> 
				<?
					
					
					$pad_year = str_pad($year, 4, "0", STR_PAD_LEFT);
					$pad_month = str_pad($month, 2, "0", STR_PAD_LEFT);
					$pad_day = str_pad($nowDayCount-1, 2, "0", STR_PAD_LEFT);

				?>
				<div class="alarm_overflow">
				<?
					for($k=0; $k < count($memberNameAry); $k++){
				?>
					<a href="./<?=basename($_SERVER["PHP_SELF"]);?>?rowDate=<?=$pad_year."-".$pad_month."-".$pad_day?>&mb_id=<?=$memberAry[$k]?>">
					<div class="box_div"><?=$memberNameAry[$k]?><span><?=$dataCnt[$pad_year."-".$pad_month."-".$pad_day][$memberAry[$k]] ?></span></div>
					</a>
				<?}?>
				</div>
                <? } else { ?> 
                <b><?=$nowDayCount++?></b> 
				<?				
					$pad_year = str_pad($year, 4, "0", STR_PAD_LEFT);
					$pad_month = str_pad($month, 2, "0", STR_PAD_LEFT);
					$pad_day = str_pad($nowDayCount-1, 2, "0", STR_PAD_LEFT);

				?>
				<div class="alarm_overflow">
				<?
					for($k=0; $k < count($memberNameAry); $k++){
				?>
					<a href="./<?=basename($_SERVER["PHP_SELF"]);?>?rowDate=<?=$pad_year."-".$pad_month."-".$pad_day?>&mb_id=<?=$memberAry[$k]?>">
					<div class="box_div"><?=$memberNameAry[$k]?><span><?=$dataCnt[$pad_year."-".$pad_month."-".$pad_day][$memberAry[$k]] ?></span></div>
					</a>
				<?}?>
				</div>
				<?}?>
            </td> 
            
            <? 
            // 이전달이라면 
            } else if ( $cellIndex < $startDay ) { ?> 
            <td align="center" style=""> 
            <font color="gray"><b><?=$prevDayCount++?></b></font> 
            </td> 
            
            <? 
            // 다음달 이라면 
            } else if ( $cellIndex >= $days ) { ?> 
            <td align="center"> 
            <font color="gray"><b><?=$nextDayCount++?></b></font> 
            </td> 
            <? } 
        } 
        ?> 
    </tr> 
    <? } ?> 

</center> 

</table>

