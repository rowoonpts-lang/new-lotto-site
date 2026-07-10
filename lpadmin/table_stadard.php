<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.php");
?>

<div class="card card-default">
	<div class="card-body">
		<div class="col-12">
			<div class="row">
				<div class="col-md-2">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" data-select2-id="9" tabindex="-1" aria-hidden="true">
						<option selected="selected" data-select2-id="11">Alabama</option>
						<option data-select2-id="68">Alaska</option>
						<option disabled="disabled" data-select2-id="69">California (disabled)</option>
						<option data-select2-id="70">Delaware</option>
						<option data-select2-id="71">Tennessee</option>
						<option data-select2-id="72">Texas</option>
						<option data-select2-id="73">Washington</option>
					</select>
				</div>
				<div class="col-md-2">
					<div class="row">
						<div class="col-md-8">
							<input type="text" class="form-control" placeholder="Enter ...">
						</div>
						<div class="col-md-4">
							<button type="button" class="btn btn-block btn-danger">검색</button>
						</div>
					</div>
				</div>
				<div class="col-md-2">
					<select class="form-control select2 select2-hidden-accessible" style="width: 100%;" data-select2-id="9" tabindex="-1" aria-hidden="true">
						<option selected="selected" data-select2-id="11">Alabama</option>
						<option data-select2-id="68">Alaska</option>
						<option disabled="disabled" data-select2-id="69">California (disabled)</option>
						<option data-select2-id="70">Delaware</option>
						<option data-select2-id="71">Tennessee</option>
						<option data-select2-id="72">Texas</option>
						<option data-select2-id="73">Washington</option>
					</select>
				</div>
				<div class="col-md-3">
					<div class="row">
						<div class="col-md-6">
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">
										<i class="far fa-calendar-alt"></i>
									</span>
								</div>
								<input type="text" class="form-control float-right dateinput">
							</div>
						</div>
						<div class="col-md-6">
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text">
										<i class="far fa-calendar-alt"></i>
									</span>
								</div>
								<input type="text" class="form-control float-right dateinput">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title">Responsive Hover Table</h3>

				<div class="card-tools">
					<div class="input-group input-group-sm" style="width: 150px;">
						<input type="text" name="table_search" class="form-control float-right" placeholder="Search">

						<div class="input-group-append">
							<button type="submit" class="btn btn-default"><i class="fas fa-search"></i></button>
						</div>
					</div>
				</div>
			</div>
			<!-- /.card-header -->
			<div class="card-body table-responsive p-0">
				<table class="table table-hover text-nowrap">
				<thead>
				<tr>
					<th><input type="checkbox"></th>
					<th>회원명/연락처</th>
					<th>아이디</th>
					<th>등급</th>
					<th>남은기간</th>
					<th>요일/조합</th>
					<th>가입일/최근접속일</th>
					<th>약관동의/미수금</th>
					<th>최근메모</th>
					<th>상담이력</th>
					<th>정보변경</th>
				</tr>
				</thead>
				<tbody>
					<tr>
					<td>183</td>
					<td>John Doe</td>
					<td>11-7-2014</td>
					<td><span class="tag tag-success">Approved</span></td>
					<td>Bacon ipsum dolor sit amet salami venison chicken flank fatback doner.</td>
				</tr>
				<tr>
					<td>219</td>
					<td>Alexander Pierce</td>
					<td>11-7-2014</td>
					<td><span class="tag tag-warning">Pending</span></td>
					<td>Bacon ipsum dolor sit amet salami venison chicken flank fatback doner.</td>
				</tr>
				<tr>
					<td>657</td>
					<td>Bob Doe</td>
					<td>11-7-2014</td>
					<td><span class="tag tag-primary">Approved</span></td>
					<td>Bacon ipsum dolor sit amet salami venison chicken flank fatback doner.</td>
				</tr>
				<tr>
					<td>175</td>
					<td>Mike Doe</td>
					<td>11-7-2014</td>
					<td><span class="tag tag-danger">Denied</span></td>
					<td>Bacon ipsum dolor sit amet salami venison chicken flank fatback doner.</td>
				</tr>
				</tbody>
				</table>
			</div>
			<!-- /.card-body -->
		</div>
		<!-- /.card -->
	</div>
</div>

<?
	include_once(G5_LADMIN_PATH."/tail.php");
?>