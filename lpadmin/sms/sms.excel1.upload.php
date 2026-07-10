<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.php");
	


?>
<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"></h3>
			</div>
			<!-- /.card-header -->
			<div class="card-body table-responsive p-0">
				<form name="fitemexcel" method="post" action="./sms.excel1.upload.update.php" enctype="MULTIPART/FORM-DATA" autocomplete="off" onsubmit="return fnCheck();">
				<table class="table table-hover text-nowrap">
				<thead>
				<tr>
					<th>오늘 발송된 문자</th>
					<th>내용입력</th>
					<th>파일선택</th>
					<th>샘플</th>
					<th>발송</th>
				</tr>
				</thead>
				<tbody>

				<tr>
					<td>
						<?
							$sql = "select count(idx) cnt from msg_cust_log where 1=1 and etc = 'excel1' and substr(send_time,1,10) = substr(now(),1,10)";
							$row = sql_fetch($sql);
							echo $row[cnt];
						?>
					</td>
					<td>
						<textarea class="form-control" rows="10" name="msg" placeholder="Enter ..."></textarea>
					</td>
					<td><input type="file" name="excelfile"></td>
					<td><a href="./excel.sample1.xls" class="btn btn-block btn-success" download="전화번호 샘플.xls">샘플다운</a></td>
					<td><button class="btn btn-block btn-primary">발송</button></td>
				</tr>

				</tbody>
				</table>
				</form>
			</div>
			<!-- /.card-body -->
		</div>
		<!-- /.card -->
	</div>
</div>

<script>
function fnCheck(){
	if(confirm("정말 발송하시겠습니까?")==true){
		return true;
	}

	return false;
}
</script>
<?
	include_once(G5_LADMIN_PATH."/tail.php");
?>