<?
	include_once("_common.php");
	include_once(G5_PATH."/_head.php");
?>

<div id="membership">
	<div class="mbs_lt">
		<h2 class="mbs_tit">멤버쉽 소개</h2>
		<div class="mbs_lt_box">
			<h3 class="mbs_lt_tit">로또피크 무통장입금</h3>
			<p class="mbs_lt_txt1">KB국민은행</p>
			<p class="mbs_lt_txt2">421701-04-311069</p>
			<p class="mbs_lt_txt3">예금주 : 구현숙(행운나눔)</p>
		</div>
		<div class="mbs_lt_box">
			<h3 class="mbs_lt_tit">로또피크 고객센터</h3>			
			<p class="mbs_lt_txt3">오전 10시 - 오후 5시</p>
			<p class="mbs_lt_txt4">1800 - 6803</p>
		</div>
	</div>
	<div class="mbs_rt">
		<div class="mbs_rt_box mbs_rt_1">
			<p class="mbs_type">정회원</p>
			<h3 class="mbs_grade font_gm_b">PRO</h3>
			<!--p class="mbs_price"><b>110,000</b> 원</p-->
			<div class="mbs_btn_box">
				<!--button type="button" class="mbs_btn" onClick="fnBbsPopOn('정회원');">상담 문의</button-->
				<!--button type="button" class="mbs_btn" onClick="fnSendMuSms()">결제하기</button-->
			</div>
			<p class="mbs_rt_desc">* 카드결제시 고객센터로 연락바랍니다</p>
		</div>
		<div class="mbs_rt_box mbs_rt_2">
			<p class="mbs_type">VIP 회원</p>
			<h3 class="mbs_grade font_gm_b">TOP - CLASS</h3>
			<!--p class="mbs_price"><b>1,320,000</b> 원</p-->
			<div class="mbs_btn_box">
				<!--button type="button" class="mbs_btn" onClick="fnBbsPopOn('VIP회원');">상담 문의</button-->
				<!--button type="button" class="mbs_btn" onClick="fnSendMuSms()">결제하기</button-->
			</div>
			<p class="mbs_rt_desc">* 카드결제시 고객센터로 연락바랍니다</p>
		</div>
	</div>
</div>

<script>
function fnSendMuSms(){
	if("<?=$is_member?>" != "1"){
		alert("로그인 후 이용바랍니다.");
		return false;
	}
	if(confirm("가입하신 번호로 무통장 입금계좌 정보를 받아보시겠습니까?")==true){
		$.ajax({
			type: "POST",
			url: "/sub/ajax.smsMu.php",
			data: {}, 
			cache: false,
			async: false,
			contentType : "application/x-www-form-urlencoded; charset=UTF-8",
			success: function(data) {
				alert("정상적으로 발송되었습니다.");		
			}
		});
		return false;

	}
}
</script>

<?
	include_once(G5_PATH."/_tail.php");
?>