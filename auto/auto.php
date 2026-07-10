<?
	include_once("_common.php");
	include_once(G5_PATH."/head.sub.php");

	// 실행을 중지하고 다시 세팅한다.
	if($config[cf_auto1_date] != date("Y-m-d")){
		$sql = "update g5_config set cf_auto1_date = '".date("Y-m-d")."', cf_auto1_ing = '0' ";
		sql_query($sql);
	}else{
		/*if($config[cf_auto1_ing] != "2"){
			$sql = "update g5_config set cf_auto1_date = '".date("Y-m-d")."', cf_auto1_ing = '0' ";
			sql_query($sql);
		}*/
	}
	
	
?>
<script src="<?=G5_JS_URL?>/jquery-1.12.4.min.js"></script>
<script>
function fnSetBoard(txt){
	$("#greenboard").append("<p>"+txt+"</p>");
}
</script>

<style>
body {padding:0;margin:0;}
.process_board {width:100%;padding:20px;}
.process_tit {font-size:27px;margin-bottom:10px}
.process_board .greenboard {background:#0C371F;border-radius:5px;padding:20px;height:50vh;overflow-y:auto;}
.process_board .greenboard p {color:#FFF766}
</style>
<div class="process_board">
	<h1 class="process_tit">LT Process v0.01</h1>
	<div class="greenboard" id="greenboard">
		<p>Ready!</p>
	</div>
	<button type="button" onClick="fnBtn('01');">매일 번호발송 수동 시작</button>
	<button type="button" onClick="fnBtn('02');">당첨결과 수동 시작</button>
	<button type="button" onClick="fnBtn('03');">번호리셋 수동 시작</button>
	<button type="button" onClick="fnBtn('04');">당첨번호발송 수동 시작</button>
	<button type="button" onClick="fnBtn('05');">무료조합 수동 시작</button>
	<button type="button" onClick="fnBtn('06');">2차접속코드리셋</button>
</div>

<script>
function fnBtn(type){
	// 자동번호발송 프로그램 실행
	fnSetBoard(type+" Process Start ...");
	$("#frame"+type).attr("src","<?=G5_URL?>/auto/process."+type+".php");
}

$(function(){
	// 실행 프로세스(지금 실행가능 하면 실행한다.);
	setInterval(function(){
		if(getProc01() == "1"){
			fnBtn('01');
		}

	},61000*5);

	// 당첨결과 프로세스
	setInterval(function(){
		if(getProc02() == "1"){
			fnBtn('02');
		}

	},62000*5);

	// 당첨결과 프로세스
	setInterval(function(){
		if(getProc03() == "1"){
			fnBtn('03');
		}

	},63000*5);

	setInterval(function(){
		if(getProc04() == "1"){
			fnBtn('04');
		}

	},64000*5);

	setInterval(function(){
		if(getProc05() == "1"){
			fnBtn('05');
		}

	},64000*5);

	setInterval(function(){
		if(getProc06() == "1"){
			fnBtn('06');
		}

	},64000*5);




	// 실행중으로 변경한다.
	setTimeout(function(){
		$.ajax({
			type: "POST",
			url: "./ajax.process01.switch.php",
			data: {}, 
			cache: false,
			async: false,
			contentType : "application/x-www-form-urlencoded; charset=UTF-8",
			success: function(data) {
				
			}
		});	
	},11000);
});

function getProc01(){
	var rt = 0;
	$.ajax({
		type: "POST",
		url: "./ajax.timecheck01.php",
		data: {}, 
		cache: false,
		async: false,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {
			rt = data;	
		}
	});
	return rt;
}

function getProc02(){
	var rt = 0;
	$.ajax({
		type: "POST",
		url: "./ajax.timecheck02.php",
		data: {}, 
		cache: false,
		async: false,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {
			rt = data;	
		}
	});
	return rt;
}

function getProc03(){
	var rt = 0;
	$.ajax({
		type: "POST",
		url: "./ajax.timecheck03.php",
		data: {}, 
		cache: false,
		async: false,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {
			rt = data;	
		}
	});
	return rt;
}

function getProc04(){
	var rt = 0;
	$.ajax({
		type: "POST",
		url: "./ajax.timecheck04.php",
		data: {}, 
		cache: false,
		async: false,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {
			rt = data;	
		}
	});
	return rt;
}

function getProc05(){
	var rt = 0;
	$.ajax({
		type: "POST",
		url: "./ajax.timecheck05.php",
		data: {}, 
		cache: false,
		async: false,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {
			rt = data;	
		}
	});
	return rt;
}

function getProc06(){
	var rt = 0;
	$.ajax({
		type: "POST",
		url: "./ajax.timecheck06.php",
		data: {}, 
		cache: false,
		async: false,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {
			rt = data;	
		}
	});
	return rt;
}
</script>


<iframe id="frame01" src="" style="width:100%;height:30px"></iframe>
<iframe id="frame02" src="" style="width:100%;height:30px"></iframe>
<iframe id="frame03" src="" style="width:100%;height:30px"></iframe>
<iframe id="frame04" src="" style="width:100%;height:30px"></iframe>
<iframe id="frame05" src="" style="width:100%;height:30px"></iframe>
<iframe id="frame06" src="" style="width:100%;height:30px"></iframe>

