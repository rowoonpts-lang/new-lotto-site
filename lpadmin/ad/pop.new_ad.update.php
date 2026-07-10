<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.sub.php");
	if(!$idx){
		$sql = "insert into l_ad_user set
					lu_type = '{$lu_type}'
					, lu_name = '{$lu_name}'
					, lu_code = '{$lu_code}'
					, lu_id = '{$lu_id}'
					, lu_pw = '{$lu_pw}'
					, st_tp = '{$st_tp}'
					, lu_datetime = now()
				";
		sql_query($sql);
	}else{
		$sql = "update l_ad_user set
					lu_pw = '{$lu_pw}'
					, st_tp = '{$st_tp}'
				where 1=1
					and idx = '{$idx}'
				";
		sql_query($sql);
	}

?>
<script>
$(function(){
	alert("정상적으로 등록이 완료되었습니다.");
	window.opener.location.reload();
	window.close();
});
</script>