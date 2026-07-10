<?if($basename != "index.php"){?>
	</section>
</div>
<?}?>

<!-- /.content-wrapper -->
  <footer class="main-footer">
    <strong>Copyright &copy; 2020- <a href="http://adminlte.io"><?=$config['cf_title']?></a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 0.0.1
    </div>
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<?
	//$sql = "select * from l_memo where "
?>

<script>
function fnMemmberMemo(mb_id){
	var url = "<?=G5_LADMIN_URL?>/member/pop.memo.php?mb_id="+mb_id;
	var name = "memo";
	var option = "width = 1400, height = 700, top = 100, left = 200, location = no"
	window.open(url, name, option);
}
</script>
<?
	$add_query = "";
	//if($member[mb_level] < 10){
		$add_query .= " and from_mb_id = '{$member['mb_id']}' ";
	//}
	$sql = "
			select * from l_memo 
			where 1=1 
				and lm_alarm_type in ('유력','단순','미수') 
				and lm_alarm_date <= now()
				and lm_alarm_view = '0' 
				{$add_query}
			";

	
	$result = sql_query($sql);
	$alarmAry = array();
	$cnt = 0;
	for($i=0; $row = sql_fetch_array($result); $i++){
		$cnt++;
		$alarmAry[$row[lm_alarm_type]] = $alarmAry[$row[lm_alarm_type]]+1;
	}
	
?>
<script>
$(function(){
	setTimeout(function(){
		$("#alarm_pop_bt").animate({
			opacity:0
		},500);
	},3000);
});
</script>
<?if($cnt > 0){?>
<div class="dropdown-menu dropdown-menu-lg dropdown-menu-right_2" id="alarm_pop_bt" style="background:#ffc800">
  <!--span class="dropdown-item dropdown-header">새로운 알람이 있습니다.</span-->
  <div class="dropdown-divider"></div>
  <?if($alarmAry['유력'] > 0){?>
  <a href="<?=G5_LADMIN_URL?>/member/alarm.list.php" class="dropdown-item">
	<i class="fas fa-envelope mr-2"></i> 유력
	<span class="float-right text-muted text-sm"><?=$alarmAry['유력']?></span>
  </a>
  <?}?>
  <?if($alarmAry['단순'] > 0){?>
  <div class="dropdown-divider"></div>
  <a href="<?=G5_LADMIN_URL?>/member/alarm.list.php" class="dropdown-item">
	<i class="fas fa-users mr-2"></i> 단순
	<span class="float-right text-muted text-sm"><?=$alarmAry['단순']?></span>
  </a>
  <?}?>
  <?if($alarmAry['미수'] > 0){?>
  <div class="dropdown-divider"></div>
  <a href="<?=G5_LADMIN_URL?>/member/alarm.list.php" class="dropdown-item">
	<i class="fas fa-file mr-2"></i> 미수
	<span class="float-right text-muted text-sm"><?=$alarmAry['미수']?></span>
  </a>
  <?}?>
  <div class="dropdown-divider"></div>
</div>
<?}?>

</body>
</html>