<!DOCTYPE html>
<html>
<head>
	<?
		if(!$is_member){
			goto_url(G5_LADMIN_URL);
		}
		if($_SESSION['ss_step2'] != $config['cf_10']){
			goto_url(G5_LADMIN_URL."/login.step2.php");			
		}

		$basename=basename($_SERVER["PHP_SELF"]); 

		$sql = "select * from l_menu where 1=1 and lm_php_name = '{$basename}'";
		$row = sql_fetch($sql);
		$title_meta = $row[lm_name];

		switch($basename){
			case "pop.memo.php": $title_meta = '상세상담'; break;
			case "pop.member_info.php": $title_meta = '정보수정'; break;
			case "pop.success.php": $title_meta = '배분당첨'; break;
			case "pop.payment.php": $title_meta = '결제승인'; break;
			case "pop.sms.php": $title_meta = '문자발송'; break;
		}
		$title_meta = "관리자_".$title_meta;

		// 관리자 이름 송출
		$sql_member = "select * from g5_member where 1=1 and mb_level >= 5";
		$result_member = sql_query($sql_member);
		$member_info = array();
		for($i=0; $row_info = sql_fetch_array($result_member); $i++){
			$member_info[$row_info['mb_id']] = $row_info['mb_name'].$row_info['mb_team'];
		}	
	?>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title><?=$title_meta?></title>
	<!-- Tell the browser to be responsive to screen width -->

	<!-- Font Awesome -->
	<link rel="stylesheet" href="<?=G5_LADMIN_URL?>/plugins/fontawesome-free/css/all.min.css">
	<!-- Ionicons -->
	<link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
	<!-- Tempusdominus Bbootstrap 4 -->
	<link rel="stylesheet" href="<?=G5_LADMIN_URL?>/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">
	<!-- iCheck -->
	<link rel="stylesheet" href="<?=G5_LADMIN_URL?>/plugins/icheck-bootstrap/icheck-bootstrap.min.css">
	<!-- JQVMap -->
	<link rel="stylesheet" href="<?=G5_LADMIN_URL?>/plugins/jqvmap/jqvmap.min.css">
	<!-- Theme style -->
	<link rel="stylesheet" href="<?=G5_LADMIN_URL?>/dist/css/adminlte.min.css">
	<!-- overlayScrollbars -->
	<link rel="stylesheet" href="<?=G5_LADMIN_URL?>/plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
	<!-- Daterange picker -->
	<link rel="stylesheet" href="<?=G5_LADMIN_URL?>/plugins/daterangepicker/daterangepicker.css">
	<!-- summernote -->
	<link rel="stylesheet" href="<?=G5_LADMIN_URL?>/plugins/summernote/summernote-bs4.css">
	<!-- Google Font: Source Sans Pro -->
	<link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
	<!-- jQuery -->
	<script src="<?=G5_LADMIN_URL?>/plugins/jquery/jquery.min.js"></script>
	<script src="<?=G5_LADMIN_URL?>/script.js"></script>
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<link rel="stylesheet" href="<?=G5_LADMIN_URL?>/css/cust.css">

	<script>
	$(function(){
		$('.dateinput').datepicker({
			dateFormat: 'yy-mm-dd', //날짜 표시 형식 설정
			showOtherMonths: true, //이전 달과 다음 달 날짜를 표시
			showMonthAfterYear:true, //연도 표시 후 달 표시
			changeYear: true, //연도 선택 콤보박스
			changeMonth: true, //월 선택 콤보박스
			yearSuffix: "년", //연도 뒤에 나오는 텍스트 지정
			monthNamesShort: ['1','2','3','4','5','6','7','8','9','10','11','12'],
			monthNames: ['1월','2월','3월','4월','5월','6월','7월','8월','9월','10월','11월','12월'],
			dayNamesMin: ['일','월','화','수','목','금','토'],
			dayNames: ['일요일','월요일','화요일','수요일','목요일','금요일','토요일'],
		})	
	});
	</script>


	<!-- jQuery UI 1.11.4 -->
	<script src="<?=G5_LADMIN_URL?>/plugins/jquery-ui/jquery-ui.min.js"></script>
	<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
	<script>
	  $.widget.bridge('uibutton', $.ui.button)
	</script>
	<!-- Bootstrap 4 -->
	<script src="<?=G5_LADMIN_URL?>/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
	<!-- ChartJS -->
	<script src="<?=G5_LADMIN_URL?>/plugins/chart.js/Chart.min.js"></script>
	<!-- Sparkline -->
	<script src="<?=G5_LADMIN_URL?>/plugins/sparklines/sparkline.js"></script>

	<!-- jQuery Knob Chart -->
	<script src="<?=G5_LADMIN_URL?>/plugins/jquery-knob/jquery.knob.min.js"></script>
	<!-- daterangepicker -->
	<script src="<?=G5_LADMIN_URL?>/plugins/moment/moment.min.js"></script>
	<script src="<?=G5_LADMIN_URL?>/plugins/daterangepicker/daterangepicker.js"></script>
	<!-- Tempusdominus Bootstrap 4 -->
	<script src="<?=G5_LADMIN_URL?>/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>
	<!-- Summernote -->
	<script src="<?=G5_LADMIN_URL?>/plugins/summernote/summernote-bs4.min.js"></script>
	<!-- overlayScrollbars -->
	<script src="<?=G5_LADMIN_URL?>/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
	<!-- AdminLTE App -->
	<script src="<?=G5_LADMIN_URL?>/dist/js/adminlte.js"></script>
	<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
	
	<!-- AdminLTE for demo purposes -->
	<script src="<?=G5_LADMIN_URL?>/dist/js/demo.js"></script>
</head>

<script>
function fnProcDel(table, key_name, key_value){
	if(confirm("정말 삭제 하시겠습니까?")== true){
		location.href="<?=G5_LADMIN_URL?>/del.process.php?table="+table+"&key_name="+key_name+"&key_value="+key_value;
	}
}
</script>