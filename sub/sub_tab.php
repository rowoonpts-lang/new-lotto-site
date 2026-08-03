<?php
	include_once("_common.php");
?>

<ul class="s3_ul">
	<?php if($sub_title == "로또자료실"){?>
	<li <?php if($basename=="data01.php"){?>class="active"<?php }?>><a href="<?=G5_URL?>/sub/data01.php">로또 분석용어</a></li>
	<li <?php if($basename=="data02.php"){?>class="active"<?php }?>><a href="<?=G5_URL?>/sub/data02.php">확률과 조합 분석</a></li>
	<li <?php if($basename=="data03.php"){?>class="active"<?php }?>><a href="<?=G5_URL?>/sub/data03.php">로또 구입 잘하는 방법</a></li>
	<?php }?>

	<?php if($sub_title == "고객센터"){?>
	<li <?php if($bo_table=="notice"){?>class="active"<?php }?>><a href="<?=G5_BBS_URL?>/board.php?bo_table=notice">공지사항</a></li>
	<li <?php if($basename=="qalist.php"){?>class="active"<?php }?>><a href="<?=G5_BBS_URL?>/qalist.php">1:1 문의</a></li>
	<?php }?>

	<?php if($sub_title == "나의로또"){?>
	<li <?php if($basename=="my_lotto.php" || $basename=="my_info.php"){?>class="active"<?php }?>><a href="<?=G5_URL?>/sub/my_lotto.php">나의 당첨현황</a></li>
	<li <?php if($basename=="my_lotto02.php"){?>class="active"<?php }?>><a href="<?=G5_URL?>/sub/my_lotto02.php">나의 로또 당첨 현황</a></li>
	<li <?php if($basename=="my_lotto03.php"){?>class="active"<?php }?>><a href="<?=G5_URL?>/sub/my_lotto03.php">로또 보관함</a></li>
	<?php }?>
</ul>