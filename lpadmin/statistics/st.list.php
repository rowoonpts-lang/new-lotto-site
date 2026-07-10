<?
	include_once("_common.php");
	include_once(G5_LADMIN_PATH."/head.php");
	

	$sql_common = " from g1_now_page a, g5_member b ";
	$sql_search = " where 1=1 and a.mb_id != 'admin' and a.mb_id = b.mb_id ";
	$sql_order = " ORDER BY max(gn_datetime) desc ";
	$group_by = " GROUP BY mb_id  ";

	$sql = " select count(distinct a.mb_id) as cnt {$sql_common} {$sql_search} {$sql_order} ";


	$row = sql_fetch($sql);
	$total_count = $row['cnt'];


	$rows = 30;
	$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
	if ($page < 1) $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
	$from_record = ($page - 1) * $rows; // 시작 열을 구함'


	$limit = " limit {$from_record}, {$rows} ";

	$sql = "select a.mb_id, MAX(gn_datetime) gn_datetime {$sql_common} {$sql_search} {$group_by} {$sql_order} {$limit}";
	$result = sql_query($sql);


?>


<div class="row">
	
	<div class="col-12">
		<div class="card">
			<div class="card-header">
				<h3 class="card-title"></h3>
			</div>
			<!-- /.card-header -->
			<div class="card-body table-responsive p-0">
				<form name="frm_member" id="frm_member">
				<table class="table table-hover text-nowrap">
				<thead>
				<tr>
					<th><input type="checkbox" id="checkall"></th>
					<th>NO</th>
					<th>회원코드</th>
					<th>회원명/연락처</th>
					<th>아이디</th>
					<th>페이지명</th>
					<th>URL</th>
					<th>접속기기</th>
					<th>최종접속시간</th>
				</tr>
				</thead>
				<tbody>
				<?for($i=0; $row = sql_fetch_array($result); $i++){
					$sql2 = "select * 
							from g1_now_page a, g5_member b, g5_member_etc c
							where 1=1
								and a.mb_id = b.mb_id
								and b.mb_id = c.mb_id
								and a.mb_id = '{$row['mb_id']}'
							order by gn_datetime desc
							limit 1
							";
					$row2 = sql_fetch($sql2);
					$nowtr = false;
					if($row2['gn_datetime'] >= date("Y-m-d H:i:s", strtotime("-2 minute"))){
						$nowtr = true;
					}
				?>
				<tr <?if($nowtr){?>style='background:#ffffd7'<?}?>>
					<td><input type="checkbox" name="chk[]" value="<?=$row['mb_id']?>"></td>
					<td><?=$total_count-($page-1)*$rows-$i?></td>
					<td><a style="cursor:pointer" onclick="fnMemmberMemo('<?=base64_encode($row2[mb_id])?>')"><?=$row2['mb_code']?></a></td>
					<td>
						<?=$row2['mb_name']?><br>
						<?=$row2['mb_hp']?>
					</td>
					<td><?=$row2['mb_id']?></td>
					<td><?=$row2['gn_title']?></td>
					<td><?=$row2['gn_url']?></td>
					<td><?=$row2['gn_device']?></td>
					<td><?=$row2['gn_datetime']?></td>
				</tr>
				<?}?>
				<?if($total_count < 1){?>
				<tr>
					<td colspan="13">내역이 없습니다.</td>
				</tr>
				<?}?>
				</tbody>
				</table>
				</form>
				<?php 
					$qstr .= "&sch_select={$sch_select}&sch_text={$sch_text}&sch_mb_type={$sch_mb_type}&start_date={$start_date}&end_date={$end_date}&sch_mb_status={$sch_mb_status}";
					echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, '?'.$qstr.'&amp;page='); 
				?>
			</div>
			<!-- /.card-body -->
		</div>
		<!-- /.card -->
	</div>
	
</div>

<script>
function fnMemberChkDel(){
	if(confirm("선택하신 회원을 삭제하시겠습니까?")==true){
		var string = $("form[name=frm_member]").serialize();

		$.ajax({
			type: "POST",
			url: "./member.alldel.php",
			data: string, 
			cache: false,
			async: false,
			contentType : "application/x-www-form-urlencoded; charset=UTF-8",
			success: function(data) {
				location.reload();
			}
		});
		return false;
	}

}

$(document).ready(function(){
    //최상단 체크박스 클릭
    $("#checkall").click(function(){
        //클릭되었으면
        if($("#checkall").prop("checked")){
            //input태그의 name이 chk인 태그들을 찾아서 checked옵션을 true로 정의
            $("input[name='chk[]']").prop("checked",true);
            //클릭이 안되있으면
        }else{
            //input태그의 name이 chk인 태그들을 찾아서 checked옵션을 false로 정의
            $("input[name='chk[]']").prop("checked",false);
        }
    })
})

function fnExcel(){
	location.href="./member.all.excel.php?1=1<?=$qstr?>";
}


function fnMemberDel(mb_id){
	if(confirm("회원을 삭제하시겠습니까?")==true){
		location.href="./member.del.php?mb_id="+mb_id;
	}
}
</script>
<?
	include_once(G5_LADMIN_PATH."/tail.php");
?>