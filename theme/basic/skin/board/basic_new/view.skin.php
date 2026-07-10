<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style2.css">', 0);
?>

<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<!-- 게시물 읽기 시작 { -->
<div>
	<div class="wr_subejct_area"><?=$view[wr_subject]?></div>
	<ul class="bo_table_desc">
		<li><?=$view[wr_name]?></li>
		<li><?=date("Y.m.d",strtotime($view[wr_datetime]))?></li>
		<li>조회수 : <?=$view[wr_hit]?></li>
	</ul>
	<div class="wr_content_area">
		<?php echo get_view_thumbnail($view['content']); ?>
	</div>
	<div class="tag_area">
		<ul>
			<li><span class="span_tag">TAG</span></li>
			<li><?=$view[wr_10]?></li>
		</ul>
	</div>
	<div class="scrap_area">
		<a href="<?php echo $scrap_href;  ?>" target="_blank" onclick="win_scrap(this.href); return false;" class="btn_st_1"><i class="far fa-copy"></i> 스크랩</a>
		<a href="<?php echo $good_href.'&amp;'.$qstr ?>" id="good_button" class="btn_st_1"><i class="far fa-thumbs-up"></i> 추천 <strong><?php echo number_format($view['wr_good']) ?></strong></a>
		<!--a href="<?php echo $good_href.'&amp;'.$qstr ?>" id="good_button" class="btn_st_1 float_right"><i class="far fa-thumbs-up"></i> SNS 공유</a-->

        <?php
        include_once(G5_SNS_PATH."/view.sns.skin.php");
        ?>
	</div>
	<?if($view[wr_9]){?>
	<div>
		<?
			$all_img = get_all_thumbnail($bo_table, 3, $wr_id, 972, 0); 

			foreach($all_img as $v) { 
				$img_url = $v['src'];
			} 
		?>
		<a href="<?=$view[wr_9]?>" target="<?=$view[wr_8]?>"><img src="<?=$v['src']?>"></a>
	</div>
	<?}?>
	<div class="list_a">
		<a href="<?php echo $list_href ?>">목록</a>
	</div>
	<div class="random_area">
		<p class="random_tit">관련된 다른 영상/ 칼럼도 확인해보세요<p>
			<script src="<?php echo G5_JS_URL ?>/jquery.bxslider.min.js"></script>
			<link rel="stylesheet" href="<?php echo G5_CSS_URL; ?>/jquery.bxslider.css">
			<ul class="bxslider">
			<?
				$sql2 = "select * from g5_write_{$bo_table} where 1=1 and wr_comment = 0 and wr_id != {$wr_id} order by rand()";
				$result2 = sql_query($sql2);
				for($j=0; $row2 = sql_fetch_array($result2); $j++){
					$thumb = get_list_thumbnail($bo_table, $row2['wr_id'], 300, 300, true, true);
			?>
			<li>
				<a href="<?=G5_BBS_URL?>/board.php?bo_table=<?=$bo_table?>&wr_id=<?=$row2[wr_id]?>">
				<dl class="bx_dl">
					<dt>
						<?
							if($thumb['src']) {
                                $img_content = '<img src="'.$thumb['src'].'" alt="'.$thumb['alt'].'" >';
                            } else {
                                $img_content = '<div class="no_image"><img src="'.G5_THEME_IMG_URL.'/main_logo.png"></div>';
                            }

                            echo $img_content;
						?>
					</dt>
					<dd><?=conv_subject($row2[wr_subject], 20, '…');?></dd>
				</dl>
				</a>
			</li>
			<?}?>
		</ul>		
		<script>
		$('.bxslider').bxSlider({
		  mode:'horizontal', //default : 'horizontal', options: 'horizontal', 'vertical', 'fade'
		  speed:500, //default:500 이미지변환 속도
		  pause:10000,
		  auto: true, //default:false 자동 시작
		  captions: false, // 이미지의 title 속성이 노출된다.
		  autoControls: false, //default:false 정지,시작 콘트롤 노출, css 수정이 필요
		  maxSlides : 3,
		  minSlides : 3,
		pager:false,
		  slideWidth:300,
		});
		</script>
	</div>
	<div class="comment_area">
		    <?php
			// 코멘트 입출력
			include_once(G5_BBS_PATH.'/view_comment.php');
			 ?>
	</div>
</div>


<!-- 게시물 상단 버튼 시작 { -->
<?if($is_admin){?>
    <div id="bo_v_top">
        <?php
        ob_start();
        ?>

        <ul class="bo_v_left">
            <?php if ($update_href) { ?><li><a href="<?php echo $update_href ?>" class="btn_b01 btn"><i class="fa fa-pencil-square-o" aria-hidden="true"></i> 수정</a></li><?php } ?>
            <?php if ($delete_href) { ?><li><a href="<?php echo $delete_href ?>" class="btn_b01 btn" onclick="del(this.href); return false;"><i class="fas fa-trash-alt"></i> 삭제</a></li><?php } ?>
            <?php if ($copy_href) { ?><li><a href="<?php echo $copy_href ?>" class="btn_admin btn" onclick="board_move(this.href); return false;"><i class="fa fa-files-o" aria-hidden="true"></i> 복사</a></li><?php } ?>
            <?php if ($move_href) { ?><li><a href="<?php echo $move_href ?>" class="btn_admin btn" onclick="board_move(this.href); return false;"><i class="fas fa-arrows-alt"></i> 이동</a></li><?php } ?>
            <?php if ($search_href) { ?><li><a href="<?php echo $search_href ?>" class="btn_b01 btn"><i class="fa fa-search" aria-hidden="true"></i> 검색</a></li><?php } ?>
        </ul>

        <ul class="bo_v_com">
           <li><a href="<?php echo $list_href ?>" class="btn_b01 btn"><i class="fa fa-list" aria-hidden="true"></i> 목록</a></li>
            <?php if ($reply_href) { ?><li><a href="<?php echo $reply_href ?>" class="btn_b01 btn"><i class="fa fa-reply" aria-hidden="true"></i> 답변</a></li><?php } ?>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b02 btn"><i class="fas fa-pencil-alt"></i> 글쓰기</a></li><?php } ?>
        </ul>

       
        <?php
        $link_buttons = ob_get_contents();
        ob_end_flush();
         ?>
    </div>
    <!-- } 게시물 상단 버튼 끝 -->
<?}?>
<script>
<?php if ($board['bo_download_point'] < 0) { ?>
$(function() {
    $("a.view_file_download").click(function() {
        if(!g5_is_member) {
            alert("다운로드 권한이 없습니다.\n회원이시라면 로그인 후 이용해 보십시오.");
            return false;
        }

        var msg = "파일을 다운로드 하시면 포인트가 차감(<?php echo number_format($board['bo_download_point']) ?>점)됩니다.\n\n포인트는 게시물당 한번만 차감되며 다음에 다시 다운로드 하셔도 중복하여 차감하지 않습니다.\n\n그래도 다운로드 하시겠습니까?";

        if(confirm(msg)) {
            var href = $(this).attr("href")+"&js=on";
            $(this).attr("href", href);

            return true;
        } else {
            return false;
        }
    });
});
<?php } ?>

function board_move(href)
{
    window.open(href, "boardmove", "left=50, top=50, width=500, height=550, scrollbars=1");
}
</script>

<script>
$(function() {
    $("a.view_image").click(function() {
        window.open(this.href, "large_image", "location=yes,links=no,toolbar=no,top=10,left=10,width=10,height=10,resizable=yes,scrollbars=no,status=no");
        return false;
    });

    // 추천, 비추천
    $("#good_button, #nogood_button").click(function() {
        var $tx;
        if(this.id == "good_button")
            $tx = $("#bo_v_act_good");
        else
            $tx = $("#bo_v_act_nogood");

        excute_good(this.href, $(this), $tx);
        return false;
    });

    // 이미지 리사이즈
    $("#bo_v_atc").viewimageresize();

    //sns공유
    $(".btn_share").click(function(){
        //$("#bo_v_sns").fadeIn();
   
    });

    $(document).mouseup(function (e) {
        /*var container = $("#bo_v_sns");
        if (!container.is(e.target) && container.has(e.target).length === 0){
        container.css("display","none");
        }*/	
    });
});

function excute_good(href, $el, $tx)
{
    $.post(
        href,
        { js: "on" },
        function(data) {
            if(data.error) {
                alert(data.error);
                return false;
            }

            if(data.count) {
                $el.find("strong").text(number_format(String(data.count)));
                if($tx.attr("id").search("nogood") > -1) {
                    $tx.text("이 글을 비추천하셨습니다.");
                    $tx.fadeIn(200).delay(2500).fadeOut(200);
                } else {
                    $tx.text("이 글을 추천하셨습니다.");
                    $tx.fadeIn(200).delay(2500).fadeOut(200);
                }
            }
        }, "json"
    );
}
</script>
<!-- } 게시글 읽기 끝 -->