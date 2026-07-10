<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>
<script src="<?php echo G5_JS_URL; ?>/viewimageresize.js"></script>

<!-- 게시물 읽기 시작 { -->

<article id="bo_v" style="width:<?php echo $width; ?>">
<!--
        <h1 id="bo_v_title">
            <?php
            //if ($category_name) echo $view['ca_name'].' | '; // 분류 출력 끝
            echo $view['wr_subject']; // 글제목 출력
            ?>
        </h1>
-->



    <section id="bo_v_atc">
        <h2 id="bo_v_atc_title">본문</h2>

        <!-- 본문 내용 시작 { -->
        <div id="bo_v_con">
            <!--
            <?php echo get_view_thumbnail($view['content']); ?>-->
        
			
            <div style="clear:both; margin-bottom:20px;padding:20px 0px;width:600px;margin:auto;display:none;">
				<script src="<?php echo G5_JS_URL ?>/jquery.bxslider.min.js"></script>
				<link rel="stylesheet" href="<?php echo G5_CSS_URL; ?>/jquery.bxslider.css">
				<ul class="bxslider">
					<?php
					// 파일 출력
					$v_img_count = count($view['file']);
					if($v_img_count) {
					$board['bo_gallery_width'] = "600";
					$board['bo_gallery_height'] = "400";
				
						for ($i=0; $i<=count($view['file']); $i++) {
							if ($view['file'][$i]['view']) {
								echo "<li>";
								$thumb = get_list_thumbnail($board['bo_table'], $view['wr_id'], $board['bo_gallery_width'], $board['bo_gallery_height'], 'false', 'true', 'center', 'false', '80/0.5/3', $i);
								$img_content = '<br><img src="'.$thumb['src'].'" alt="'.$thumb2['alt'].'" width="'.$board['bo_gallery_width'].'" height="'.$board['bo_gallery_height'].'">'; 
								echo $img_content;
			
								//echo get_view_thumbnail($view['file'][$i]['view']);
								echo "</li>\n";
							}
						}

						
					}
					 ?>
				</ul>
				<script>
				$('.bxslider').bxSlider({
				  mode:'horizontal', //default : 'horizontal', options: 'horizontal', 'vertical', 'fade'
				  speed:500, //default:500 이미지변환 속도
				  pause:10000,
				  auto: true, //default:false 자동 시작
				  captions: false, // 이미지의 title 속성이 노출된다.
				  autoControls: false, //default:false 정지,시작 콘트롤 노출, css 수정이 필요
				});
				</script>
			</div>
			<div id="v_map">
				<?=$view[wr_4]?>
			</div>
			<table id="table">
				<tbody>
					<tr>
						<th>지역</th>
						<td><?=$view[ca_name]?></td>
					</tr>
					<tr>
						<th>매장명</th>
						<td><?=$view[wr_subject]?></td>
					</tr>
					<tr>
						<th>주소</th>
						<td><?=$view[wr_1]?></td>
					</tr>
					<tr>
						<th>연락처</th>
						<td><?=$view[wr_2]?></td>
					</tr>
					<tr>
						<th>홈페이지</th>
						<td>
							<?if($view[wr_3]){?>
								<?=$view[wr_3]?>
							<?}else{?>
								없음
							<?}?>
						</td>
					</tr>
				</tbody>
			</table>
			<!--div id="maps" style="width:100%; height:420px;"></div-->
		</div>
        <?php//echo $view['rich_content']; // {이미지:0} 과 같은 코드를 사용할 경우 ?>
        <!-- } 본문 내용 끝 -->

        <?php if ($is_signature) { ?><p><?php echo $signature ?></p><?php } ?>

    </section>
    <!-- 게시물 상단 버튼 시작 { -->
    <div id="bo_v_top">
        <?php
        ob_start();
        ?>
        <?php if ($prev_href || $next_href) { ?>
            <ul class="bo_v_nb">
                <?php if ($prev_href) { ?><li><a href="<?php echo $prev_href ?>" class="btn_b01">이전글</a></li><?php } ?>
                <?php if ($next_href) { ?><li><a href="<?php echo $next_href ?>" class="btn_b01">다음글</a></li><?php } ?>
            </ul>
        <?php } ?>

        <ul class="bo_v_com">
            <?php if ($update_href) { ?><li><a href="<?php echo $update_href ?>" class="btn_b01">수정</a></li><?php } ?>
            <?php if ($delete_href) { ?><li><a href="<?php echo $delete_href ?>" class="btn_b01" onclick="del(this.href); return false;">삭제</a></li><?php } ?>
            <?php if ($copy_href) { ?><li><a href="<?php echo $copy_href ?>" class="btn_admin" onclick="board_move(this.href); return false;">복사</a></li><?php } ?>
            <?php if ($move_href) { ?><li><a href="<?php echo $move_href ?>" class="btn_admin" onclick="board_move(this.href); return false;">이동</a></li><?php } ?>
            <?php if ($search_href) { ?><li><a href="<?php echo $search_href ?>" class="btn_b01">검색</a></li><?php } ?>
            <li><a href="<?php echo $list_href ?>" class="btn_b01">목록</a></li>
            <?php if ($reply_href) { ?><li><a href="<?php echo $reply_href ?>" class="btn_b01">답변</a></li><?php } ?>
            <?php if ($write_href) { ?><li><a href="<?php echo $write_href ?>" class="btn_b02">글쓰기</a></li><?php } ?>
        </ul>
        <?php
        $link_buttons = ob_get_contents();
        ob_end_flush();
        ?>
    </div>
    <!-- } 게시물 상단 버튼 끝 -->

    <?php
    include_once(G5_SNS_PATH."/view.sns.skin.php");
    ?>

</article>
<!-- } 게시판 읽기 끝 -->

<script src="//apis.daum.net/maps/maps3.js?apikey=다음지도key값넣어주세요&#38;libraries=services"></script>
<script>
var juso = "<?=$view['wr_1']?>";
console.log(juso);
//function map_view(){
  var mapContainer = document.getElementById("maps"), // 지도를 표시할 div
    mapOption = {
        center: new daum.maps.LatLng(33.450701, 126.570667), // 지도의 중심좌표
        level: 3 // 지도의 확대 레벨
    };

// 지도를 생성합니다
var map = new daum.maps.Map(mapContainer, mapOption);

// 주소-좌표 변환 객체를 생성합니다
var geocoder = new daum.maps.services.Geocoder();

// 주소로 좌표를 검색합니다
geocoder.addr2coord(juso, function(status, result) {

    // 정상적으로 검색이 완료됐으면
     if (status === daum.maps.services.Status.OK) {

        var coords = new daum.maps.LatLng(result.addr[0].lat, result.addr[0].lng);

        // 결과값으로 받은 위치를 마커로 표시합니다
        var marker = new daum.maps.Marker({
            map: map,
            position: coords
        });

        // 지도의 중심을 결과값으로 받은 위치로 이동시킵니다
        map.setCenter(coords);
    }
});


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