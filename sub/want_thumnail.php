<?
$all_img = get_all_thumbnail('게시판코드(bo_table)', '원하는 이미지 번호', '게시물 글 번호(wr_id)', 600, 430); 

$img_url = "";
foreach($all_img as $v) { 
	$img_url = $v['src']; 
} 
?>