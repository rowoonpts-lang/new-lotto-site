<?
	include_once("_common.php");
	include_once(G5_PATH."/head.sub.php");

	error_reporting(E_ALL);
	ini_set("display_errors", 1);


	$idx = Decrypt($idx, 'able', 'able');

	$sql = "select * from l_ad_user where 1=1 and idx = '{$idx}'";
	$row = sql_fetch($sql);
	if(!$row['idx']){
		alert("허용되지 않은 URL 입니다.");
	}

?>
<link rel="stylesheet" href="<?=G5_URL?>/ad/style.css">

<div class="a4_size">
	<h1 class="ad_tit">[<?=$config['cf_title']?>] 광고 연동 문서 <small>Ver. 0.02</small></h1>
	<p class="ad_tit_desc">
		이 문서는 프로토타입이며, API 연동 문서는 상시 업데이트 될 수 있습니다.<br><br>
		JSON 데이터를 이용하여 FORM 방식 / CURL 방식으로 호출이 가능하며,<br>AJAX는 크로스도메인 등의 문제로 지원하지 않습니다.<br><br>
		고된 개발생활에 지친 개발자를 위한 CURL <a href="<?=G5_URL?>/ad/json_sample_php.zip" download>PHP 샘플코드</a> 입니다.<br>
		<strong>20.03.28 update - name 값은 선택 값으로 변경 > name 값이 없을 시 '익명'으로 수집됩니다.</strong>
	</p>
	
	<table class="vertical_table margin-top-20">
	<tr>
		<th style="width:20%;border-bottom:1px solid #000">매체사</th>
		<td style="border-right:1px solid #000"><?=$row['lu_name']?></td>
		<th style="width:20%;border-bottom:1px solid #000">접속아이디</th>
		<td><?=$row['lu_id']?></td>
	</tr>
	<tr>
		<th style="width:20%">매체사 코드</th>
		<td style="width:30%;border-right:1px solid #000"><?=$row['lu_type']?></td>
		<th style="width:20%">광고 코드</th>
		<td><?=$row['lu_code']?></td>
	</tr>
	<tr>
		<th style="border-top:1px solid #000">관리자 페이지</th>
		<td colspan="3"><?=G5_URL?>/ad/process.list.php</td>
	</tr>
	</table>

	<table class="vertical_table margin-top-20">
	<tr>
		<th style="width:30%">REQUEST URL (GET/POST)</th>
		<td><?=G5_URL?>/ad/process.php</td>
	</tr>
	</table>

	<table class="vertical_table margin-top-20">
	<tr>
		<th colspan="3">REQUEST PARAMETER</th>
	</tr>
	<tr>
		<td style="width:20%"><strong>lu_type</strong></td>
		<td style="width:20%"><strong>필수</strong></td>
		<td>매체사 코드</td>
	</tr>
	<tr>
		<td><strong>lu_code</strong></td>
		<td><strong>필수</strong></td>
		<td>광고 코드</td>
	</tr>
	<tr>
		<td><strong>tel</strong></td>
		<td><strong>필수</strong></td>
		<td>연락처 (ex. 01012345678 or 010-1234-5678)</td>
	</tr>
	<tr>
		<td><strong>name</strong></td>
		<td>선택</td>
		<td>이름 (ex. 홍길동)</td>
	</tr>
	<tr>
		<td><strong>etc1</strong></td>
		<td>선택</td>
		<td>매체사 전용 비고1</td>
	</tr>
	<tr>
		<td><strong>etc2</strong></td>
		<td>선택</td>
		<td>매체사 전용 비고2</td>
	</tr>
	</table>

	<table class="vertical_table margin-top-20">
	<tr>
		<th style="width:20%">REQUEST EXAMPLE (GET)</th>
	</tr>
	<tr>
		<td><?=G5_URL?>/ad/process.php?<strong>lu_type=<?=$row['lu_type']?>&lu_code=<?=$row['lu_code']?>&tel=01012345678</strong></td>
	</tr>
	</table>

	<table class="vertical_table margin-top-20">
	<tr>
		<th colspan="2">JSON RESULT</th>
	</tr>
	<tr>
		<td style="width:20%"><strong>result</strong></td>
		<td>에러 코드표 참고</td>
	</tr>
	<tr>
		<td style="width:20%"><strong>result_desc</strong></td>
		<td>에러 설명</td>
	</tr>
	</table>

	<table class="vertical_table margin-top-20">
	<tr>
		<th colspan="2">ERROR CODE</th>
	</tr>
	<tr>
		<td style="width:20%"><strong>0000</strong></td>
		<td>정상</td>
	</tr>
	<tr>
		<td><strong>0001</strong></td>
		<td>필수값 lu_type 누락</td>
	</tr>
	<tr>
		<td><strong>0002</strong></td>
		<td>필수값 lu_code 누락</td>
	</tr>
	<tr>
		<td><strong>0003</strong></td>
		<td>필수값 name 누락_(0.02 버전부터 선택값으로 변경되어 제거 됨)</td>
	</tr>
	<tr>
		<td><strong>0004</strong></td>
		<td>필수값 tel 누락</td>
	</tr>
	<tr>
		<td><strong>0005</strong></td>
		<td>필수값 tel 형식 에러</td>
	</tr>
	<tr>
		<td><strong>0006</strong></td>
		<td>매체사 코드 또는 광고 코드 에러</td>
	</tr>
	<tr>
		<td><strong>1111</strong></td>
		<td>
			기타 예외 상황<br>
			- 문의 1544-0152 / <?=$config['cf_title']?> API 관련하여 문의주셨다고 말씀 부탁드립니다.<br>
			- 각 운영하는 사이트 소스코드에 대한 문의는 답변드릴 수 없으니 참고바랍니다.<br>
			- 평일 오전 10:30 - 오후 06:30
		</td>
	</tr>
	</table>

</div>