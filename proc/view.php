<?
	include_once("_common.php");
	echo '<meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=0,maximum-scale=10,user-scalable=yes">'.PHP_EOL;
    echo '<meta name="HandheldFriendly" content="true">'.PHP_EOL;
    echo '<meta name="format-detection" content="telephone=no">'.PHP_EOL;

	//$ly_type = base64_encode("디럭스그룹");
	//echo  $ly_type;
	

	$ly_type = base64_decode($ly_type);
	$ly_type = str_replace("|","",$ly_type);
	$mb_id = base64_decode($mb_id);
	
	$sql = "select * from l_yak where 1=1 and ly_type = '{$ly_type}'";
	$row = sql_fetch($sql);

	$sql2 = "select * from g5_member_etc where 1=1 and mb_id = '{$mb_id}' ";
	$row2 = sql_fetch($sql2);

?>
<link rel="stylesheet" href="<?=G5_THEME_URL?>/css/default.css">
<script src="<?=G5_JS_URL?>/jquery-1.8.3.min.js"></script>
<style>
.yak_view_div {width:100%;height:100vh;background:#FFC107;padding:20px 10px}
.yak_view_div .tit_p {font-size:21px;font-weight:600;height:5vh}
.yak_view_div .yak_content {width:100%;padding:10px;background:#fff;border:1px solid #DBAC1E;font-size:15px;line-height:20px;height:75vh;overflow-y:auto;}
.yak_btn_div {padding:10px 0px}
.yak_btn_div button {width:100%;padding:10px 0px;background:#403131;color:#fff;font-weight:600;font-size:19px;border:0;border-radius:5px}
.yak_btn_div .yak_ok_btn {background:#a93939}
</style>
<div class="yak_view_div">
	<form id="frm" action="view.update.php">
		<input type="hidden" name="mb_id" value="<?=base64_encode($mb_id)?>">
		<input type="hidden" name="ly_type" value="<?=base64_encode($ly_type)?>">
		<p class="tit_p"><?=$config['cf_title']?> <?=$ly_type?> 약관</p>
		<div class="yak_content">
			<?=nl2br($row[ly_content])?>
		</div>
		<div class="yak_btn_div">
			<button type="button" onClick="fnAgree()">약관동의</button>
			<?//if($row2[mb_yak]){?>
			<!--button type="button" disabled class="yak_ok_btn">약관동의 완료</button-->
			<?//}else{?>
			<!--button type="button" onClick="fnAgree()">약관동의</button-->
			<?//}?>
		</div>
	</form>
</div>

<script>
function fnAgree(){
	if(confirm("약관에 동의하시겠습니까?")==true){
		$("#frm").submit();
	}
}
</script>