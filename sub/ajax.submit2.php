<?
	include_once("_common.php");

	@mkdir(G5_DATA_PATH.'/res', G5_DIR_PERMISSION);
	$uploadBase = G5_DATA_PATH.'/res/';

	$bo_table = "res";
	$write_table = "g5_write_".$bo_table;


	$sql = " insert into $write_table
				set  wr_reply = '$wr_reply',
					 wr_comment = 0,
					 ca_name = '$ca_name',
					 wr_option = '',
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
					 wr_password = password('{$wr_password}'),
					 wr_name = '$wr_name',
					 wr_email = '$wr_email',
					 wr_homepage = '$wr_homepage',
					 wr_datetime = '".G5_TIME_YMDHIS."',
					 wr_last = '".G5_TIME_YMDHIS."',
					 wr_ip = '{$ip}',
					 wr_1 = '$wr_1',
					 wr_2 = '$wr_2',
					 wr_3 = '$wr_3',
					 wr_4 = '$wr_4',
					 wr_5 = '$wr_5',
					 wr_6 = '$wr_6',
					 wr_7 = '$wr_7',
					 wr_8 = '$wr_8',
					 wr_9 = '$wr_9',
					 wr_10 = '$wr_10'
					 ";
	sql_query($sql);
	
	$wr_id = sql_insert_id();


	// 부모 아이디에 UPDATE
	sql_query(" update $write_table set wr_parent = '$wr_id', wr_num = $wr_id*(-1) where wr_id = '$wr_id' ");
	sql_query("update g5_board set bo_count_write = bo_count_write+1 where 1=1 and bo_table = '$bo_table'");

	foreach ($_FILES['multi_file']['name'] as $f => $name) {   
		$name = $_FILES['multi_file']['name'][$f];
		$uploadName = explode('.', $name);
		$uploadname = time().$f.'.'.$uploadName[1];
		$uploadFile = $uploadBase.$uploadname;

		if(move_uploaded_file($_FILES['multi_file']['tmp_name'][$f], $uploadFile)){
			$sql2 = "insert into g1_file set wr_id = '{$wr_id}', gf_num = '{$f}', gf_name = '{$uploadname}', gf_name_bf = '{$name}'";
			sql_query($sql2);

			//echo $sql2;
		}else{
		//echo 'error';
		}
	}

	include_once(G5_LIB_PATH.'/mailer.lib.php');	
	
	$content = "이름 : ".$wr_1."<br>";
	$content .= "연락처 : ".$wr_2."<br>";
	$content .= "이메일 : ".$wr_3."<br>";
	$content .= "제목 : ".$wr_subject."<br>";
	$content .= "문의내용 :<br>".nl2br($wr_content)."<br>";

	$sql = " select count(*) cnt from g1_file where wr_id = '{$wr_id}' ";
	$row = sql_fetch($sql);
	$cnt = $row['cnt'];

	if($cnt > 0){
		$sql = " select * from g1_file where wr_id = '{$wr_id}' order by gf_num asc ";
		$result = sql_query($sql);
		for($i=0; $row=sql_fetch_array($result); $i++){
			$content .= "첨부파일 :";
			$content .= "<p><a href='".G5_DATA_URL."/".$bo_table."/".$row['gf_name']."'>".$row['gf_name_bf']."</a></p>";
		}
	}

	mailer("알파색채 문의", "gold@alphacolor.com", "gold@alphacolor.com", $wr_1."님께서 상담을 남기셨습니다.", $content, 1);
	//mailer("관심고객등록", "bjhpo@naver.com", "bjhpo@naver.com", $wr_name."께서 관심고객 등록을 하였습니다..", $content, 1);
?>