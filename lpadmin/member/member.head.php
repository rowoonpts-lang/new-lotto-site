<?
	$basename=basename($_SERVER["PHP_SELF"]); 
?>
<div class="row">
	<div class="col-2">
		<a href="./pop.memo.php?mb_id=<?=$mb_id2?>"><button class="btn btn-block <?if($basename == "pop.memo.php"){?>btn-warning<?}else{?>btn-secondary<?}?>">상세상담</button></a>
	</div>
	<div class="col-2">
		<a href="./pop.member_info.php?mb_id=<?=$mb_id2?>"><button class="btn btn-block <?if($basename == "pop.member_info.php"){?>btn-warning<?}else{?>btn-secondary<?}?>">정보수정</button></a>
	</div>
	<div class="col-2">
		<a href="./pop.success.php?mb_id=<?=$mb_id2?>"><button class="btn btn-block btn-secondary <?if($basename == "pop.success.php"){?>btn-warning<?}else{?>btn-secondary<?}?>">배분당첨</button></a>
	</div>
	<div class="col-2">
		<a href="./pop.payment.php?mb_id=<?=$mb_id2?>"><button class="btn btn-block btn-secondary <?if($basename == "pop.payment.php"){?>btn-warning<?}else{?>btn-secondary<?}?>">결제승인</button></a>
	</div>
	<div class="col-2">
		<a href="./pop.sms.php?mb_id=<?=$mb_id2?>"><button class="btn btn-block btn-secondary <?if($basename == "pop.sms.php"){?>btn-warning<?}else{?>btn-secondary<?}?>">문자발송</button></a>
	</div>
</div>