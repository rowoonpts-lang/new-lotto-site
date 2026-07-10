<?
	include_once("_common.php");
?>

<ul class="s3_ul">
	<?if($sub_title == "로또자료실"){?>
	<li <?if($basename=="data01.php"){?>class="active"<?}?>><a href="<?=G5_URL?>/sub/data01.php">로또 분석용어</a></li>
	<li <?if($basename=="data02.php"){?>class="active"<?}?>><a href="<?=G5_URL?>/sub/data02.php">확률과 조합 분석</a></li>
	<li <?if($basename=="data03.php"){?>class="active"<?}?>><a href="<?=G5_URL?>/sub/data03.php">로또 구입 잘하는 방법</a></li>
	<?}?>

	<?if($sub_title == "고객센터"){?>
	<li <?if($bo_table=="notice"){?>class="active"<?}?>><a href="<?=G5_BBS_URL?>/board.php?bo_table=notice">공지사항</a></li>
	<li <?if($basename=="qalist.php"){?>class="active"<?}?>><a href="<?=G5_BBS_URL?>/qalist.php">1:1 문의</a></li>
	<?}?>

	<?if($sub_title == "나의로또"){?>
	<li <?if($basename=="my_lotto.php" || $basename=="my_info.php"){?>class="active"<?}?>><a href="<?=G5_URL?>/sub/my_lotto.php">나의 당첨현황</a></li>
	<li <?if($basename=="my_lotto02.php"){?>class="active"<?}?>><a href="<?=G5_URL?>/sub/my_lotto02.php">나의 로또 당첨 현황</a></li>
	<li <?if($basename=="my_lotto03.php"){?>class="active"<?}?>><a href="<?=G5_URL?>/sub/my_lotto03.php">로또 보관함</a></li>
	<?}?>
</ul>