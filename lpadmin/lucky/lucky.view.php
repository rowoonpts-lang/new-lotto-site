<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.php");
?>

<div class="card card-default">
	<div class="card-body">
		<div class="col-12">
		<form id="" name="" autocomplete="off" action="lucky.view.update.php">
			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<div class="col-md-2">
							<input type="text" class="form-control" name="cf_lucky_1" value="<?=$config['cf_lucky_1']?>" placeholder="Enter ...">
						</div>
						<div class="col-md-2">
							<input type="text" class="form-control" name="cf_lucky_2" value="<?=$config['cf_lucky_2']?>" placeholder="Enter ...">
						</div>
						<div class="col-md-2">
							<input type="text" class="form-control" name="cf_lucky_3" value="<?=$config['cf_lucky_3']?>" placeholder="Enter ...">
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<div class="col-md-2">
							당첨현황 1등 인원
							<input type="text" class="form-control" name="cf_etc_1" value="<?=$config['cf_etc_1']?>" placeholder="Enter ...">
						</div>
						<div class="col-md-2">
							당첨현황 2등 인원
							<input type="text" class="form-control" name="cf_etc_2" value="<?=$config['cf_etc_2']?>" placeholder="Enter ...">
						</div>
						<div class="col-md-2">
							당첨현황 3등 인원
							<input type="text" class="form-control" name="cf_etc_3" value="<?=$config['cf_etc_3']?>" placeholder="Enter ...">
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<div class="row">
						<div class="col-md-2">
							당첨현황 1등 금액
							<input type="text" class="form-control" name="cf_etc_1_1" value="<?=$config['cf_etc_1_1']?>" placeholder="Enter ...">
						</div>
						<div class="col-md-2">
							당첨현황 2등 금액
							<input type="text" class="form-control" name="cf_etc_2_1" value="<?=$config['cf_etc_2_1']?>" placeholder="Enter ...">
						</div>
						<div class="col-md-2">
							당첨현황 3등 금액
							<input type="text" class="form-control" name="cf_etc_3_1" value="<?=$config['cf_etc_3_1']?>" placeholder="Enter ...">
						</div>
						<div class="col-md-1">
							<br>
							<button class="btn btn-block btn-primary">저장</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		</form>
	</div>
</div>

<script>
function fnExcel(){
	location.href="./lucky.list.excel.php?1=1<?=$qstr?>";
}
</script>

<?
	include_once(G5_LADMIN_PATH."/tail.php");
?>