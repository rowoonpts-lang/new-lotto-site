<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 선택옵션으로 인해 셀합치기가 가변적으로 변함
$colspan = 5;

if ($is_checkbox) $colspan++;
if ($is_good) $colspan++;
if ($is_nogood) $colspan++;

unset($list);

$sch_year = clean_xss_tags($_GET['sch_year']);
$sch_year = preg_match("/([0-9]{4})/", $sch_year) ? $sch_year : substr(G5_TIME_YMD,0,4);

$sch_month = clean_xss_tags($_GET['sch_month']);
$sch_month = preg_match("/([0-9]{2})/", $sch_month) ? $sch_month : substr(G5_TIME_YMD,5,2);

$vew_month = $sch_year.'-'.$sch_month;

unset($arr_db);
$arr_db = array();
$sql = "select wr_id, wr_subject, wr_1 from {$write_table} where left(wr_1, 7) = '$vew_month'";
$res = sql_query($sql);
while($row = sql_fetch_array($res)) {
    $arr_db[$row['wr_1']][] = $row;
}
if ($res) sql_free_result($res);

$weekstr = array('일', '월', '화', '수', '목', '금', '토');

// 한달의 총 날짜 계산 함수
function wz_max_day($i_month, $i_year) {
    $day = 1;
    while(checkdate($i_month, $day, $i_year))
    {
        $day++;
    }
    $day--;
    return $day;
}

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<h2 id="container_title"><?php echo $board['bo_subject'] ?><span class="sound_only"> 목록</span></h2>

<!-- 게시판 목록 시작 { -->
<div id="bo_list" style="width:<?php echo $width; ?>">

    <!-- 게시판 카테고리 시작 { -->
    <?php if ($is_category) { ?>
    <nav id="bo_cate">
        <h2><?php echo $board['bo_subject'] ?> 카테고리</h2>
        <ul id="bo_cate_ul">
            <?php echo $category_option ?>
        </ul>
    </nav>
    <?php } ?>
    <!-- } 게시판 카테고리 끝 -->

    <!-- 게시판 페이지 정보 및 버튼 시작 { -->
    <div class="bo_fx">
        <div id="bo_list_total">
            <span>Total <?php echo number_format($total_count) ?>건</span>
            <?php echo $page ?> 페이지
        </div>

        <?php if ($rss_href || $write_href) { ?>
        <ul class="btn_bo_user">
            <?php if ($rss_href) { ?><li><a href="<?php echo $rss_href ?>" class="btn_b01">RSS</a></li><?php } ?>
            <?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="btn_admin">관리자</a></li><?php } ?>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b02">글쓰기</a></li><?php } ?>
        </ul>
        <?php } ?>
    </div>
    <!-- } 게시판 페이지 정보 및 버튼 끝 -->

    <div class="cal_navi">
        <h4><?php echo substr($vew_month, 0, 4)?> 년 <?php echo substr($vew_month, 5, 2)?> 월</h4>
        <!-- search -->
        <form name="frmSel" method="get" onsubmit="return frmSelChk(this);" action="<?php echo G5_BBS_URL;?>/board.php">
        <input type="hidden" name="bo_table" id="bo_table" value="<?php echo $bo_table;?>" />
        <fieldset class="search3">
            <legend>검색</legend>
            <select name="sch_year" style="width:70px;" title="검색 옵션 선택">
            <?php
            for($i=2017 ; $i<(substr(G5_TIME_YMD, 0, 4)+1) ; $i++) {
                if($i==$sch_year) {	
                    echo '<option value="'.$i.'" selected>'.$i.'</option>';	
                }
                else {	
                    echo '<option value="'.$i.'">'.$i.'</option>';	
                }
            }
            ?>
            </select>
            <select name="sch_month" style="width:70px;" title="검색 옵션 선택">
            <?php
            for ($i=1 ; $i<=12 ; $i++) {
                if (strlen($i) == "1") {	
                    $tempI = "0".$i;	
                }
                else {	
                    $tempI = $i;	
                }

                if ($tempI==$sch_month) {	
                    echo '<option value="'.$tempI.'" selected>'.$i.'월</option>';	
                }
                else {
                    echo '<option value="'.$tempI.'">'.$i.'월</option>';	
                }
            }
            ?>
            </select>
            <input type="image" class="btn" src="<?php echo $board_skin_url;?>/wz.img/bt_search.gif" alt="검색" style="border:0px;" />
        </fieldset>
    </div>
    
    <form name="fboardlist" id="fboardlist" action="./board_list_update.php" onsubmit="return fboardlist_submit(this);" method="post">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="spt" value="<?php echo $spt ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="sw" value="">

    <table class="wrap_calendar">
    <colgroup>
        <col width="14%" span="7" />
    </colgroup>
    <tbody>
    <thead>
        <tr>
            <th scope="col">일</th>
            <th scope="col">월</th>
            <th scope="col">화</th>
            <th scope="col">수</th>
            <th scope="col">목</th>
            <th scope="col">금</th>
            <th scope="col">토</th>
        </tr>
    </thead>
    <tbody>
    <tr>
        <?php
        $sch_year       = substr($vew_month, 0, 4);
        $sch_month      = substr($vew_month, 5, 2);
        $total_day      = wz_max_day($sch_month, $sch_year);
        $first_day      = date('w', mktime(0, 0, 0, $sch_month, 1, $sch_year));
        $count          = 0;
        $weekcut        = 0; // 한주가 지나면 초기화

        for ($i=0; $i<$first_day; $i++) {
            echo '<td><span class="date_head none">&nbsp;</span></td>'.PHP_EOL;
            $count++;
        }

        for ($day=1; $day<=$total_day; $day++) {
            
            $count++;

            $vDate = $sch_year ."-". $sch_month ."-". sprintf('%02d', $day); // 표시 날짜.

            if ($vDate == $today) { // 오늘 표시
                $bg_class = 'dday';
            }
            else { // 오늘이 아니면...
                if ($count == 1) // 일요일
                    $bg_class = 'sun';
                elseif ($count == 7) // 토요일
                    $bg_class = 'sat';
                else // 평일
                    $bg_class = '';
            }

            echo '<td>'.PHP_EOL;
            echo '<span class="date_head none">'.$day.'</span>'.PHP_EOL;     
            
            if (isset($arr_db[$vDate])) {
                $cnt_db = count($arr_db[$vDate]);
                echo '<ul class="data-list">'.PHP_EOL;
                for ($z=0; $z < $cnt_db; $z++) { 
                    echo '  <li><a href="'.G5_BBS_URL.'/board.php?bo_table='.$board['bo_table'].'&wr_id='.$arr_db[$vDate][$z]['wr_id'].'">'.conv_subject($arr_db[$vDate][$z]['wr_subject'], 10, '…').'</a></li>'.PHP_EOL;    
                }             
                echo '</ul>'.PHP_EOL;
            }
            
            echo '</td>'.PHP_EOL;

            if($count==7) { // 토요일이 되면 줄바꾸기 위한 <tr>태그 삽입을 위한 식
                echo '</tr>'.PHP_EOL;
                if($day != $total_day) {
                    echo '<tr>'.PHP_EOL;
                    $count = 0;
                }
            }
        }

        // 선택한 월의 마지막날 이후의 빈테이블 삽입
        for ($day++; $total_day < $day && $count < 7;) {
            $count++;
            echo '<td><span class="date_head none">&nbsp;</span></td>'.PHP_EOL;
            if ($count == 7) 
                echo '</tr>'.PHP_EOL;
        }
        ?>
    </tbody>
    </table>

    <?php if ($list_href || $is_checkbox || $write_href) { ?>
    <div class="bo_fx">
        <?php if ($is_checkbox) { ?>
        <ul class="btn_bo_adm">
            <li><input type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value"></li>
            <li><input type="submit" name="btn_submit" value="선택복사" onclick="document.pressed=this.value"></li>
            <li><input type="submit" name="btn_submit" value="선택이동" onclick="document.pressed=this.value"></li>
        </ul>
        <?php } ?>

        <?php if ($list_href || $write_href) { ?>
        <ul class="btn_bo_user">
            <?php if ($list_href) { ?><li><a href="<?php echo $list_href ?>" class="btn_b01">목록</a></li><?php } ?>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b02">글쓰기</a></li><?php } ?>
        </ul>
        <?php } ?>
    </div>
    <?php } ?>
    </form>
</div>

<?php if($is_checkbox) { ?>
<noscript>
<p>자바스크립트를 사용하지 않는 경우<br>별도의 확인 절차 없이 바로 선택삭제 처리하므로 주의하시기 바랍니다.</p>
</noscript>
<?php } ?>

<!-- 페이지 -->
<?php echo $write_pages;  ?>

<!-- 게시판 검색 시작 { -->
<fieldset id="bo_sch">
    <legend>게시물 검색</legend>

    <form name="fsearch" method="get">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sop" value="and">
    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl">
        <option value="wr_subject"<?php echo get_selected($sfl, 'wr_subject', true); ?>>제목</option>
        <option value="wr_content"<?php echo get_selected($sfl, 'wr_content'); ?>>내용</option>
        <option value="wr_subject||wr_content"<?php echo get_selected($sfl, 'wr_subject||wr_content'); ?>>제목+내용</option>
        <option value="mb_id,1"<?php echo get_selected($sfl, 'mb_id,1'); ?>>회원아이디</option>
        <option value="wr_name,1"<?php echo get_selected($sfl, 'wr_name,1'); ?>>글쓴이</option>
    </select>
    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" required id="stx" class="frm_input required" size="15" maxlength="20">
    <input type="submit" value="검색" class="btn_submit">
    </form>
</fieldset>
<!-- } 게시판 검색 끝 -->

<?php if ($is_checkbox) { ?>
<script>
function all_checked(sw) {
    var f = document.fboardlist;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]")
            f.elements[i].checked = sw;
    }
}

function fboardlist_submit(f) {
    var chk_count = 0;

    for (var i=0; i<f.length; i++) {
        if (f.elements[i].name == "chk_wr_id[]" && f.elements[i].checked)
            chk_count++;
    }

    if (!chk_count) {
        alert(document.pressed + "할 게시물을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택복사") {
        select_copy("copy");
        return;
    }

    if(document.pressed == "선택이동") {
        select_copy("move");
        return;
    }

    if(document.pressed == "선택삭제") {
        if (!confirm("선택한 게시물을 정말 삭제하시겠습니까?\n\n한번 삭제한 자료는 복구할 수 없습니다\n\n답변글이 있는 게시글을 선택하신 경우\n답변글도 선택하셔야 게시글이 삭제됩니다."))
            return false;

        f.removeAttribute("target");
        f.action = "./board_list_update.php";
    }

    return true;
}

// 선택한 게시물 복사 및 이동
function select_copy(sw) {
    var f = document.fboardlist;

    if (sw == "copy")
        str = "복사";
    else
        str = "이동";

    var sub_win = window.open("", "move", "left=50, top=50, width=500, height=550, scrollbars=1");

    f.sw.value = sw;
    f.target = "move";
    f.action = "./move.php";
    f.submit();
}
</script>
<?php } ?>
<!-- } 게시판 목록 끝 -->
