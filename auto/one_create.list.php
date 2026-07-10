<?
	include_once("_common.php");

	if(!$is_admin){
		alert("관리자만 접속가능!");
	}

	$sql = "SELECT COUNT(*) cnt FROM g5_member_etc a, g5_member b WHERE 1=1 and a.mb_id = b.mb_id and b.mb_type = '무료회원' and b.mb_leave_date = '' and (a.recent_free_date is null or (a.recent_free_date != '".date("Y-m-d")."' and a.recent_free_date != '".date("Y-m-d", strtotime("-1 day"))."'))";
	$row = sql_fetch($sql);
	$setCnt = 10;
	$cnt = $row['cnt']*($setCnt+1);
	//$cnt = 5000;
	$createnum = 1000;
	$cnt_share = ceil($cnt / $createnum);
	$turn = getTurn();
	if($config['free_num_turn'] != $turn){

?>
<script src="<?php echo G5_JS_URL ?>/jquery-1.8.3.min.js"></script>
총 <?=number_format($cnt)?>개 생성 예정 <span id="progress1">□□□□□□□□□□</span> [<span id="per1">0</span> %]

<button type="button" onClick="fnStartProc()" id="fnStartProc">생성시작</button> [<span id="status1">미처리</span>] <button type="button" id="fnUpdateProcBef" onClick="fnUpdateProcBef()">적용</button>
<?	}else{?>
<?=$turn?>회차는 무료번호가 발송되었습니다.
<?	}?>
<script>
var iii = 0;
var cnt_share = "<?=$cnt_share?>"*1;

function fnStartProc(){
	for(i=1; i <= (cnt_share*1); i++){
		fnStart(i);
	}
	
}

function fnCreateProgress(per){
	//console.log(per);
	tmpText = "";
	for(k=1; k <= per*1; k++){
		tmpText+="■";
	}
	for(k=per*1; k < 10; k++){
		tmpText+="□";
	}
	return tmpText;
}

function fnStart(i_temp){
	$("#fnStartProc").hide();
	$.ajax({
		type: "POST",
		url: "./one_create.proc.php",
		data: {create : "<?=$createnum?>", cnt_share : "<?=$cnt_share?>", iii : i_temp}, 
		cache: false,
		async: true,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {
			iii++;
			per1 = Math.round((100/cnt_share)*iii/10);
			per2 = Math.ceil((100/cnt_share)*iii);
			txt1 = fnCreateProgress(per1);
			$("#progress1").text(txt1);
			$("#per1").text(per2);		
		}
	});
	return false;
}
function fnUpdateProcBef(){
	$("#status1").text("처리중...기다려주세요.");
	setTimeout(function(){
		fnUpdateProc();
	},100);
}
function fnUpdateProc(){
	
	var cnt = "<?=$setCnt?>";
	$.ajax({
		type: "POST",
		url: "./one_update.proc.php",
		data: {cnt : cnt}, 
		cache: false,
		async: false,
		contentType : "application/x-www-form-urlencoded; charset=UTF-8",
		success: function(data) {
			$("#status1").text("처리완료!");
			alert("정상적으로 처리되었습니다.");
			location.reload();
		}
	});
	return false;
}
</script>