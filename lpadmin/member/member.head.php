<?php
	$basename=basename($_SERVER["PHP_SELF"]); 
?>
<link rel="stylesheet" href="<?=G5_LADMIN_URL?>/css/member.unified.css">
<div class="row">
	<div class="col-2">
		<a href="./pop.member.php?mb_id=<?=$mb_id2?>"><button class="btn btn-block <?php if($basename == "pop.member.php"){?>btn-warning<?php }else{?>btn-secondary<?php }?>">회원정보</button></a>
	</div>
	<div class="col-2">
		<a href="./pop.memo.php?mb_id=<?=$mb_id2?>"><button class="btn btn-block <?php if($basename == "pop.memo.php"){?>btn-warning<?php }else{?>btn-secondary<?php }?>">상세상담</button></a>
	</div>
	<div class="col-2">
		<a href="./pop.member_info.php?mb_id=<?=$mb_id2?>"><button class="btn btn-block <?php if($basename == "pop.member_info.php"){?>btn-warning<?php }else{?>btn-secondary<?php }?>">정보수정</button></a>
	</div>
	<div class="col-2">
		<a href="./pop.success.php?mb_id=<?=$mb_id2?>"><button class="btn btn-block btn-secondary <?php if($basename == "pop.success.php"){?>btn-warning<?php }else{?>btn-secondary<?php }?>">배분당첨</button></a>
	</div>
	<div class="col-2">
		<a href="./pop.payment.php?mb_id=<?=$mb_id2?>"><button class="btn btn-block btn-secondary <?php if($basename == "pop.payment.php"){?>btn-warning<?php }else{?>btn-secondary<?php }?>">결제승인</button></a>
	</div>
	<div class="col-2">
		<a href="./pop.sms.php?mb_id=<?=$mb_id2?>"><button class="btn btn-block btn-secondary <?php if($basename == "pop.sms.php"){?>btn-warning<?php }else{?>btn-secondary<?php }?>">문자발송</button></a>
	</div>
</div>