<?php
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

        $allowed_extensions = array(
                'zip', '7z', 'dwg', 'stl', '3dm',
                'jpg', 'jpeg', 'gif', 'png', 'bmp',
                'pdf', 'ppt', 'pptx', 'hwp', 'hwpx',
                'doc', 'docx', 'xls', 'xlsx', 'txt'
        );
        $max_file_count = 4;
        $max_total_size = 10 * 1024 * 1024;
        $uploaded_count = 0;
        $total_size = 0;

        if (isset($_FILES['multi_file']['name']) && is_array($_FILES['multi_file']['name'])) {
                foreach ($_FILES['multi_file']['name'] as $f => $name) {
                        if ($name === '') {
                                continue;
                        }

                        $uploaded_count++;

                        if ($uploaded_count > $max_file_count) {
                                http_response_code(400);
                                exit('첨부파일은 최대 4개까지 등록할 수 있습니다.');
                        }

                        $error = isset($_FILES['multi_file']['error'][$f])
                                ? (int) $_FILES['multi_file']['error'][$f]
                                : UPLOAD_ERR_NO_FILE;
                        $tmp_name = isset($_FILES['multi_file']['tmp_name'][$f])
                                ? $_FILES['multi_file']['tmp_name'][$f]
                                : '';
                        $file_size = isset($_FILES['multi_file']['size'][$f])
                                ? (int) $_FILES['multi_file']['size'][$f]
                                : 0;

                        if ($error !== UPLOAD_ERR_OK || !$tmp_name || !is_uploaded_file($tmp_name)) {
                                http_response_code(400);
                                exit('첨부파일 업로드에 실패했습니다.');
                        }

                        $total_size += $file_size;

                        if ($total_size > $max_total_size) {
                                http_response_code(400);
                                exit('첨부파일 전체 용량은 10MB를 초과할 수 없습니다.');
                        }

                        $original_name = basename($name);
                        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

                        if (!$extension || !in_array($extension, $allowed_extensions, true)) {
                                http_response_code(400);
                                exit('허용되지 않은 첨부파일 형식입니다.');
                        }

                        $uploadname = bin2hex(random_bytes(16)) . '.' . $extension;
                        $uploadFile = $uploadBase . $uploadname;

                        if (!move_uploaded_file($tmp_name, $uploadFile)) {
                                http_response_code(500);
                                exit('첨부파일 저장에 실패했습니다.');
                        }

                        $sql2 = "insert into g1_file
                                        set wr_id = '{$wr_id}',
                                            gf_num = '{$f}',
                                            gf_name = '{$uploadname}',
                                            gf_name_bf = '{$original_name}'";
                        sql_query($sql2);
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