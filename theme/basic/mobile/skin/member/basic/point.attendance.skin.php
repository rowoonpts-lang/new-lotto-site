<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/attendance_point_style.css">', 0);
?>
<?php
$sum_point1 = $sum_point2 = $sum_point3 = 0;
$num = $total_count -($page-1)*$rows;
$sql = "select * {$sql_common} {$sql_search} {$sql_order}";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    if ($row['po_point'] > 0) {
        $sum_point1 += $row['po_point'];
    }
}
if ($sum_point1 > 0)
    $sum_point1 = number_format($sum_point1);
?>


<div id="point_wrap" >
    <div class="point_text">
        <p>
            <i class="fa fa-check-circle-o icon_biz_check" aria-hidden="true"></i> 총 누적 수익금 : <span class="my_point"><?php echo $sum_point1; ?>원</span><span class="go_profit_form"><a href="<?=G5_BBS_URL;?>/write.php?bo_table=profit3">수익금 신청 +</a></span>수익금은 10,000원 단위로 신청이 가능합니다.  <br>
            <i class="fa fa-check-circle-o icon_biz_check" aria-hidden="true"></i> 신청된 수익금은 시 당일 7~8시에 일괄 입금됩니다.<br>
        </p>
    </div>


    <div class="date_search">

        <form name="fsearch" id="fsearch" class="local_sch" method="get">
            <input type="hidden" id="sfl"  name="sfl" value="<?php echo $sfl; ?>">
            <input type="hidden" id="stx"  name="stx" value="<?php echo $stx; ?>" >
            <div class="date_sel">
                기간 :
                <span style="position:relative;display:inline-block"><input type="text" id="fr_date"  name="fr_date" value="<?php echo $fr_date; ?>" class="frm_input" size="10" maxlength="10"><img src="<?=G5_IMG_URL;?>/calendar.png"> - <input type="text" id="to_date"  name="to_date" value="<?php echo $to_date; ?>" class="frm_input right" size="10" maxlength="10"><img src="<?=G5_IMG_URL;?>/calendar.png"></span>
                <input type="submit" class="btn_submit " value="조회하기">
                <a href="<?=G5_BBS_URL;?>/attendance_point.php" class="btn_submit2">초기화</a>
            </div>
            <p>
                기간을 선택하신후 조회하기 버튼을 클릭하시면 상세 실적조회가 가능합니다.
            </p>
        </form>
    </div>

    <div id="profit_gnb_btn">
        <div class="pgb_wr">
            <a href="<?=G5_BBS_URL;?>/reseller_point.php">리셀러수익리스트</a>
            <a href="<?=G5_BBS_URL;?>/shop_point.php">쇼핑수익리스트</a>
            <?if($member['mb_9'] == 'attendance'){?><a href="<?=G5_BBS_URL;?>/attendance_point.php" class="on">출석수익리스트</a><?}?>
        </div>
    </div>

    <div class="new_win_con list_01">

        <div class="tbl_head01 tbl_wrap">
            <table>
                <caption>리셀러 수익 리스트</caption>
                <thead>
                <tr>
                    <th scope="col">번호</th>
                    <th scope="col">서비스</th>
                    <th scope="col">수익금</th>
                    <th scope="col">출석시간</th>
                </tr>
                </thead>
                <tbody>

                <?php
                $sum_point1 = $sum_point2 = $sum_point3 = 0;
                $num = $total_count -($page-1)*$rows;
                $sql = "select * {$sql_common} {$sql_search} {$sql_search2} {$sql_order} limit {$from_record}, {$rows} ";
                $result = sql_query($sql);
                for ($i=0; $row=sql_fetch_array($result); $i++)
                {
                    $point1 = $point2 = 0;
                    if ($row['po_point'] > 0) {
                        $point1 = '+ ' .number_format($row['po_point']);
                        $sum_point1 += $row['po_point'];
                    } else {
                        $point2 = number_format($row['po_point']);
                        $sum_point2 += $row['po_point'];
                    }
                    $po_content = $row['po_content'];

                    $expr = '';
                    if($row['po_expired'] == 1)
                        $expr = ' txt_expired';
                    ?>
                    <tr >
                        <td class="number"><? echo $num;;?></td>
                        <td class="service" ><?=$row['po_content'];?></td>
                        <td class="profit" ><?php if ($point1) echo $point1; else echo $point2; ?></td>
                        <td class="regi_date" ><?=$row['po_datetime'];?></td>
                    </tr>
                    <?php
                    $num--;
                }

                if ($i == 0)
                    echo '<tr ><td colspan="7">자료가 없습니다.</td></tr>';
                else {
                    if ($sum_point1 > 0)
                        $sum_point1 = number_format($sum_point1);
                    $sum_point2 = number_format($sum_point2);
                }
                ?>
                <tr >
                    <td colspan="7" class="total" style="background-color:#fafafa">소계 <span><? echo $sum_point1;?> <?php //echo $sum_point2; ?>원</span></td>
                </tr>
                </tbody>
            </table>
        </div>

    </div>

    <?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'].'?'.$qstr.'&amp;page='); ?>


</div>


<script>
    $(function(){
        $("#fr_date, #to_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" });

    });

</script>

