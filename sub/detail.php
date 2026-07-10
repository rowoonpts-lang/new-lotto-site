<?
	include_once("_common.php");
	include_once(G5_PATH."/_head.php");
	include_once(G5_LIB_PATH.'/thumbnail.lib.php');

	if($prd2){
		$link = "&prd2=".$prd2;
	}else{
		$link ="";
	}

	$bo_table = 'prd';

	$sql = " select * from g5_write_prd where 1=1 and wr_id = '{$wr_id}' ";
	$row = sql_fetch($sql);

	$en_txt = "";
	$cate = $row['ca_name'];
	if($cate == "아크릴물감"){$en_txt = "Acrylic colors";}
	if($cate == "보조제"){$en_txt = "Mediums";}
	if($cate == "포스터칼라"){$en_txt = "Poster Colors";}
	if($cate == "수채화"){$en_txt = "Watercolor";}
	if($cate == "한국화물감"){$en_txt = "Korean colors";}
	if($cate == "마커"){$en_txt = "Marker";}
	if($cate == "염색물감"){$en_txt = "Dye colors";}
	if($cate == "기타화구"){$en_txt = "ETC";}
?>

<section class="detail">
	<div class="inner">
		<div class="detail_img">
			<?=get_view_thumbnail($row['wr_content']);?>
			<!--img src="<?=G5_THEME_IMG_URL?>/detail_img.jpg" alt=""-->
		</div>
		<ul class="detail_cont">
			<li class="detail_eng"><?=$en_txt?></li>
			<li class="detail_kor"><?=$row['wr_subject']?></li>
			<li class="detail_ex"><?=nl2br($row['wr_1'])?></li>
			<li class="detail_hr"></li>
			<li class="detail_color"><?=nl2br($row['wr_2'])?></li>
			<li class="detail_back"><a href="<?=G5_URL?>/sub/acrylic.php?prd1=<?=$prd1?><?=$link?>">목록</a></li>
		</ul>
	</div>
</section>

<script>
<?if(!G5_IS_MOBILE){?>
	$(window).scroll(function() {
		var scroll = $(window).scrollTop();
		var f_scroll = $('footer.footer').offset().top;
		if (scroll >= f_scroll-'700') {
			$(".detail").addClass("abs");
		} else {
			$(".detail").removeClass("abs");
		}
	});
	<?}else{?>

<?}?>

</script>

<?
	include_once(G5_PATH."/_tail.php");
?>
