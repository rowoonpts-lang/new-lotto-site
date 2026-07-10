<?
	include_once("_common.php");

	$write_table = "g5_write_".$bo_table;

	$sql = " insert into $write_table
				set  wr_reply = '$wr_reply',
					 wr_comment = 0,
					 ca_name = '$ca_name',
					 wr_option = 'secret',
					 wr_subject = '$wr_subject',
					 wr_content = '$wr_content',
					 wr_link1 = '$wr_link1',
					 wr_link2 = '$wr_link2',
					 wr_link1_hit = 0,
					 wr_link2_hit = 0,
					 wr_hit = 0,
					 wr_good = 0,
					 wr_nogood = 0,
					 mb_id = 'guest',
					 wr_password = password('1234567890'),
					 wr_name = '$wr_subject',
					 wr_email = '$wr_email',
					 wr_homepage = '$wr_homepage',
					 wr_datetime = '".G5_TIME_YMDHIS."',
					 wr_last = '".G5_TIME_YMDHIS."',
					 wr_ip = '{$_SERVER['REMOTE_ADDR']}',
					 wr_1 = '$wr_1',
					 wr_2 = '$wr_2',
					 wr_3 = '$wr_3',
					 wr_4 = '$wr_4',
					 wr_5 = '$wr_5',
					 wr_6 = '$wr_6',
					 wr_7 = '$wr_7',
					 wr_8 = '$wr_8',
					 wr_9 = '$wr_9',
					 wr_10 = '$wr_10' ";
	sql_query($sql);
	
	$wr_id = sql_insert_id();

	// 부모 아이디에 UPDATE
	sql_query(" update $write_table set wr_parent = '$wr_id', wr_num = $wr_id*(-1) where wr_id = '$wr_id' ");
	sql_query("update g5_board set bo_count_write = bo_count_write+1 where 1=1 and bo_table = '$bo_table'");

	echo $sql;


	include_once(G5_LIB_PATH.'/mailer.lib.php');

	
	$content = "성명 : ".$wr_subject."<br>";
	$content .= "연락처 : ".$wr_1."<br>";
	$content .= "이메일 : ".$wr_2."<br>";
	$content .= "문의사항 및 통화가능시간 : ".$wr_content."<br>";

	//mailer("알파색채 상담신청", "abc@naver.com", "abc@naver.com", $wr_subject."님께서 상담을 남기셨습니다.", $content, 1);
	//mailer("관심고객등록", "bjhpo@naver.com", "bjhpo@naver.com", $wr_name."께서 관심고객 등록을 하였습니다..", $content, 1);
	//echo $content;

?>