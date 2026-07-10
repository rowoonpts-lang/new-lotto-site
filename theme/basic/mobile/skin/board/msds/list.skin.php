<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 선택옵션으로 인해 셀합치기가 가변적으로 변함
$colspan = 5;

if ($is_checkbox) $colspan++;
if ($is_good) $colspan++;
if ($is_nogood) $colspan++;

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<!-- 게시판 목록 시작 { -->
<div id="bo_list">

	<h3 class="table_title"><b>MSDS</b>&nbsp;안전보건공단 화학물질정보</h3>

    <!-- 게시판 페이지 정보 및 버튼 시작 { -->
    <div id="bo_btn_top">
        <div id="bo_list_total">
            전체 <span><?php echo number_format($total_count) ?></span>건
            <?php /*echo $page */?><!-- 페이지-->
        </div>

        <?php if ($rss_href || $write_href) { ?>
        <ul class="btn_bo_user">
            <?php if ($rss_href) { ?><li><a href="<?php echo $rss_href ?>" class="btn_b01 btn"><i class="fa fa-rss" aria-hidden="true"></i> RSS</a></li><?php } ?>
            <?php if ($admin_href) { ?><li><a href="<?php echo $admin_href ?>" class="btn_admin btn"><i class="fa fa-user-circle" aria-hidden="true"></i> </a></li><?php } ?>
            <?php /*if ($write_href) { */?><!--<li><a href="<?php /*echo $write_href */?>" class="btn_b02 btn btn_style_pink">글쓰기</a></li>--><?php /*} */?>
        </ul>
        <?php } ?>

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
                </select>
                <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
                <div class="search_box">
                    <input type="text" name="stx" placeholder="검색어를 입력해주세요" value="<?php echo stripslashes($stx) ?>" required id="stx" class="sch_input" size="25" maxlength="20">
                    <button type="submit" value="검색" class="sch_btn btn_style_pink">검색<span class="sound_only">검색</span></button>
                </div>
            </form>
        </fieldset>
        <!-- } 게시판 검색 끝 -->
    </div>
    <!-- } 게시판 페이지 정보 및 버튼 끝 -->

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

        <div class="tbl_head01 tbl_wrap">
            <table>
            <!--caption><?php echo $board['bo_subject'] ?> 목록</caption-->

            <tbody>
            <?php
            for ($i=0; $i<count($list); $i++) {
				$bg_col = "";
				if($list[$i]['ca_name'] == "포스터칼라"){ $bg_col = "msds_f_1"; }
				if($list[$i]['ca_name'] == "수채화물감"){ $bg_col = "msds_f_2"; }
				if($list[$i]['ca_name'] == "아크릴물감"){ $bg_col = "msds_f_3"; }
				if($list[$i]['ca_name'] == "마커"){ $bg_col = "msds_f_4"; }
				if($list[$i]['ca_name'] == "보조제"){ $bg_col = "msds_f_5"; }
				if($list[$i]['ca_name'] == "한국화물감"){ $bg_col = "msds_f_6"; }
				if($list[$i]['ca_name'] == "기타"){ $bg_col = "msds_f_7"; }
             ?>
            <tr class="<?php if ($list[$i]['is_notice']) echo "bo_notice"; ?>">
                <?php if ($is_checkbox) { ?>
                <!--td class="td_chk">
                    <label for="chk_wr_id_<?php echo $i ?>" class="sound_only"><?php echo $list[$i]['subject'] ?></label>
                    <input type="checkbox" name="chk_wr_id[]" value="<?php echo $list[$i]['wr_id'] ?>" id="chk_wr_id_<?php echo $i ?>">
                </td-->
                <?php } ?>

                <td class="td_subject">
                    <div class="bo_tit">
						<div class="msds_f"><p class="<?=$bg_col?>"><?=$list[$i]['ca_name']?></p></div>
                        <a href="<?php echo $list[$i]['href'] ?>">
						
                            <?php echo $list[$i]['icon_reply'] ?>
                            <?php
                                if (isset($list[$i]['icon_secret'])) echo rtrim($list[$i]['icon_secret']);
                             ?>
                            <?php echo $list[$i]['subject'] ?>
							<div class="date_td"><?php echo date("Y-m-d", strtotime($list[$i]['wr_datetime']));?></div>
                        </a>
                    </div>
                </td>
            </tr>
            <?php } ?>
            <?php if (count($list) == 0) { echo '<tr><td colspan="'.$colspan.'" class="empty_table">게시물이 없습니다.</td></tr>'; } ?>
            </tbody>
            </table>
        </div>

        <div class="list_footer">
            <?php if ($list_href || $is_checkbox || $write_href) { ?>
            <div class="bo_fx">
                <?php if ($list_href || $write_href) { ?>
                <ul class="btn_bo_user">
                    <?php if ($is_checkbox) { ?>
              <!--       <li><button type="submit" name="btn_submit" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_b01">선택삭제</button></li>
                    <li><button type="submit" name="btn_submit" value="선택복사" onclick="document.pressed=this.value" class="btn btn_b01">선택복사</button></li>
                    <li><button type="submit" name="btn_submit" value="선택이동" onclick="document.pressed=this.value" class="btn btn_b01">선택이동</button></li> -->
                    <?php } ?>
                    <?php if ($list_href) { ?><li><a href="<?php echo $list_href ?>" class="btn_list btn">목록</a></li><?php } ?>
                    <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b02 btn">글쓰기</a></li><?php } ?>
                </ul>
                <?php } ?>
            </div>
            <?php } ?>

            <!-- 페이지 -->
            <?php echo $write_pages;  ?>
        </div>
    </form>
     


</div>

<?php if($is_checkbox) { ?>
<noscript>
<p>자바스크립트를 사용하지 않는 경우<br>별도의 확인 절차 없이 바로 선택삭제 처리하므로 주의하시기 바랍니다.</p>
</noscript>
<?php } ?>






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
