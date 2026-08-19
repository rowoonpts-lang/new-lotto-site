<?php
	$basename = basename($_SERVER["PHP_SELF"]);
?>
<link rel="stylesheet" href="<?=G5_LADMIN_URL?>/css/member.unified.css">

<div class="row">
	<div class="col-3">
		<a href="./pop.member.php?mb_id=<?=$mb_id2?>">
			<button class="btn btn-block <?=$basename === 'pop.member.php' ? 'btn-warning' : 'btn-secondary'?>">
				회원정보
			</button>
		</a>
	</div>

	<div class="col-3">
		<a href="./pop.success.php?mb_id=<?=$mb_id2?>">
			<button class="btn btn-block <?=$basename === 'pop.success.php' ? 'btn-warning' : 'btn-secondary'?>">
				배분당첨
			</button>
		</a>
	</div>

	<div class="col-3">
		<a href="./pop.payment.php?mb_id=<?=$mb_id2?>">
			<button class="btn btn-block <?=$basename === 'pop.payment.php' ? 'btn-warning' : 'btn-secondary'?>">
				결제승인
			</button>
		</a>
	</div>

	<div class="col-3">
		<a href="./pop.sms.php?mb_id=<?=$mb_id2?>">
			<button class="btn btn-block <?=$basename === 'pop.sms.php' ? 'btn-warning' : 'btn-secondary'?>">
				문자발송
			</button>
		</a>
	</div>
</div>
