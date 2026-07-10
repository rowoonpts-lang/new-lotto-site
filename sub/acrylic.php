<?
	include_once("_common.php");
	include_once(G5_PATH."/_head.php");
	include_once(G5_LIB_PATH.'/thumbnail.lib.php');

	if($prd2){
		$link = "&prd2=".$prd2;
	}else{
		$link ="";
	}

?>

<section class="acrylic">
	<div class="inner">
		<?
			$sql = " select * from g5_write_prd_top where 1=1 and ca_name = '{$prd1}' order by wr_datetime desc limit 1 ";
			$row = sql_fetch($sql);
			$all_img = get_all_thumbnail('prd_top', 1, $row['wr_id'], 1115, 778);
			foreach($all_img as $v);
		?>
		<div class="banner">
			<div class="left">
				<img src="<?=$v['src']?>">
			</div>	<!-- left -->
			<div class="right">
				<ul>
					<li class="banner_title"><?=nl2br($row['wr_content'])?></li>
					<li class="hr"></li>
					<li class="banner_cont"><?=nl2br($row['wr_1'])?></li>
				</ul>
			</div>	<!-- right -->
		</div>	<!-- banner -->

		<div class="inner_2">
			
			<?if($prd2){?>
			<ul class="pro_tab">
				<?if($prd1 == "보조제"){?>
				<li <?if($prd2 == "아크릴보조제(수성)"){?>class="active"<?}?>>
					<a href="<?=G5_URL?>/sub/acrylic.php?prd1=보조제&prd2=아크릴보조제(수성)">
						<p>아크릴보조제<span class="m_br"></span>(수성)</p>
					</a>
				</li>
				<li <?if($prd2 == "유화보조제(유성)"){?>class="active"<?}?>>
					<a href="<?=G5_URL?>/sub/acrylic.php?prd1=보조제&prd2=유화보조제(유성)">
						<p>유화보조제<span class="m_br"></span>(유성)</p>
					</a>
				</li>
				<li <?if($prd2 == "한국화보조제(수성)"){?>class="active"<?}?>>
					<a href="<?=G5_URL?>/sub/acrylic.php?prd1=보조제&prd2=한국화보조제(수성)">
						<p>한국화보조제<span class="m_br"></span>(수성)</p>
					</a>
				</li>
				<?}?>
				<?if($prd1 == "마커"){?>
				<li <?if($prd2 == "디자인마커"){?>class="active"<?}?>>
					<a href="<?=G5_URL?>/sub/acrylic.php?prd1=마커&prd2=디자인마커">
						<p>디자인마커</p>
					</a>
				</li>
				<li <?if($prd2 == "브러쉬마커"){?>class="active"<?}?>>
					<a href="<?=G5_URL?>/sub/acrylic.php?prd1=마커&prd2=브러쉬마커">
						<p>브러쉬마커</p>
					</a>
				</li>
				<li <?if($prd2 == "마커악세사리"){?>class="active"<?}?>>
					<a href="<?=G5_URL?>/sub/acrylic.php?prd1=마커&prd2=마커악세사리">
						<p>마커악세사리</p>
					</a>
				</li>
				<?}?>
			</ul>
			<?}?>

			<div class="acrylic_pro">
				<ul class="acrylic_pro_ul clearfix">
					<?
						$add = "";
						if($prd2){
							$add = " and wr_3 = '{$prd2}' ";
						}

						$sql = " select * from g5_write_prd where 1=1 and ca_name = '{$prd1}' {$add} order by wr_datetime asc ";
						$result = sql_query($sql);
						for($i=0; $row=sql_fetch_array($result); $i++){
							$all_img = get_all_thumbnail('prd', 1, $row['wr_id'], 300, 326);
							foreach($all_img as $v);

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
					<li class="acrylic_pro_li">
						<div class="thum">
							<img src="<?=$v['src']?>">
							<a class="thum_bg" href="<?=G5_URL?>/sub/detail.php?wr_id=<?=$row['wr_id']?>&prd1=<?=$prd1?><?=$link?>">
								<p><?=$en_txt?></p>
								<h3><?=$row['wr_subject']?></h3>
								<img src="<?=G5_THEME_IMG_URL?>/add.png" alt="">
							</a>
						</div>
						<ul class="pro_desc">
							<li class="pro_desc_1"><?=str_replace('<br>',' ',$row['wr_subject'])?></li>
							<li class="pro_desc_2"><?=nl2br($row['wr_2']);?></li>
							<!--li class="pro_desc_3">12색/24색</li-->
						</ul>
					</li>
					<?}?>
					<!--li class="acrylic_pro_li">
						<div class="thum">
							<img src="<?=G5_THEME_IMG_URL?>/a_pro_2.jpg" alt="">
							<a class="thum_bg" href="">
								<p>Acrylic colors</p>
								<h3>골드아크릴</h3>
								<img src="<?=G5_THEME_IMG_URL?>/add.png" alt="">
							</a>
						</div>
						<ul class="pro_desc">
							<li class="pro_desc_1">골드아크릴</li>
							<li class="pro_desc_2">50ml</li>
							<li class="pro_desc_3"></li>
						</ul>
					</li>

					<li class="acrylic_pro_li">
						<div class="thum">
							<img src="<?=G5_THEME_IMG_URL?>/a_pro_1.jpg" alt="">
							<a class="thum_bg" href="">
								<p>Acrylic colors</p>
								<h3>골드아크릴</h3>
								<img src="<?=G5_THEME_IMG_URL?>/add.png" alt="">
							</a>
						</div>
						<ul class="pro_desc">
							<li class="pro_desc_1">실버아크릴</li>
							<li class="pro_desc_2"></li>
							<li class="pro_desc_3">12색/24색</li>
						</ul>
					</li>

					<li class="acrylic_pro_li">
						<div class="thum">
							<img src="<?=G5_THEME_IMG_URL?>/a_pro_2.jpg" alt="">
							<a class="thum_bg" href="">
								<p>Acrylic colors</p>
								<h3>골드아크릴</h3>
								<img src="<?=G5_THEME_IMG_URL?>/add.png" alt="">
							</a>
						</div>
						<ul class="pro_desc">
							<li class="pro_desc_1">학생용 아크릴</li>
							<li class="pro_desc_2">50ml</li>
							<li class="pro_desc_3"></li>
						</ul>
					</li>

					<li class="acrylic_pro_li">
						<div class="thum">
							<img src="<?=G5_THEME_IMG_URL?>/a_pro_1.jpg" alt="">
							<a class="thum_bg" href="">
								<p>Acrylic colors</p>
								<h3>골드아크릴</h3>
								<img src="<?=G5_THEME_IMG_URL?>/add.png" alt="">
							</a>
						</div>
						<ul class="pro_desc">
							<li class="pro_desc_1">골드아크릴</li>
							<li class="pro_desc_2">50ml</li>
							<li class="pro_desc_3">12색/24색</li>
						</ul>
					</li>

					<li class="acrylic_pro_li">
						<div class="thum">
							<img src="<?=G5_THEME_IMG_URL?>/a_pro_2.jpg" alt="">
							<a class="thum_bg" href="">
								<p>Acrylic colors</p>
								<h3>골드아크릴</h3>
								<img src="<?=G5_THEME_IMG_URL?>/add.png" alt="">
							</a>
						</div>
						<ul class="pro_desc">
							<li class="pro_desc_1">골드아크릴</li>
							<li class="pro_desc_2">50ml</li>
							<li class="pro_desc_3">12색/24색</li>
						</ul>
					</li>

					<li class="acrylic_pro_li">
						<div class="thum">
							<img src="<?=G5_THEME_IMG_URL?>/a_pro_1.jpg" alt="">
							<a class="thum_bg" href="">
								<p>Acrylic colors</p>
								<h3>골드아크릴</h3>
								<img src="<?=G5_THEME_IMG_URL?>/add.png" alt="">
							</a>
						</div>
						<ul class="pro_desc">
							<li class="pro_desc_1">골드아크릴</li>
							<li class="pro_desc_2">50ml</li>
							<li class="pro_desc_3"></li>
						</ul>
					</li-->

				</ul>		<!-- acrylic_pro_ul -->
			</div>	<!-- acrylic_pro -->
		</div><!-- inner_2 -->
	</div>   <!-- inner -->
</section>	<!-- acrylic -->

<?
	include_once(G5_PATH."/_tail.php");
?>