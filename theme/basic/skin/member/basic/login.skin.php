<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_PATH."/_head.php");
// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<div id="login_wrap">

    <!-- 로그인 시작 { -->
	<?if(!$member_chk){?>
    <div class="login_left">
        <div id="mb_login" class="mbskin">

            <form name="flogin" action="<?php echo $login_action_url ?>" onsubmit="return flogin_submit(this);" method="post">
            <input type="hidden" name="url" value="<?php echo $login_url ?>">

            <fieldset id="login_fs">
                <legend>회원로그인</legend>

                <div class="input_id">
                    <label for="login_id" class="sound_only">회원아이디<strong class="sound_only"> 필수</strong></label>
                    <div class="input_title">아이디</div>
                    <input type="text" name="mb_id" id="login_id" required class="frm_input required" size="20" maxLength="20" placeholder="아이디">
                </div>

                <div class="input_pw">
                    <label for="login_pw" class="sound_only">비밀번호<strong class="sound_only"> 필수</strong></label>
                    <div class="input_title">비밀번호</div>
                    <input type="password" name="mb_password" id="login_pw" required class="frm_input required" size="20" maxLength="20" placeholder="비밀번호">
                </div>

                <div class="input_btn">
                    <input type="submit" value="로그인" class="btn_submit">
					<a class="btn_submit" href="./register.php">회원가입</a>
                </div>
				

            </fieldset>


            <aside id="login_info">
                <h2>회원로그인 안내</h2>
                <div>
                    <a href="<?php echo G5_BBS_URL ?>/password_lost.php" target="_blank" id="login_password_lost"><i class="icon_search"></i>아이디/비밀번호 찾기</a>
                </div>
            </aside>
                <?php
                @include_once(get_social_skin_path().'/social_login.skin.php');
                ?>

            </form>


            <?php // 쇼핑몰 사용시 여기부터 ?>
            <?php if ($default['de_level_sell'] == 1) { // 상품구입 권한 ?>

                <!-- 주문하기, 신청하기 -->
                <?php if (preg_match("/orderform.php/", $url)) { ?>

            <section id="mb_login_notmb">
                <h2>비회원 구매</h2>

                <div id="guest_privacy">
                    <?php echo conv_content($default['de_guest_privacy'], $config['cf_editor']); ?>
                </div>

                <label for="agree">개인정보수집에 대한 내용을 읽었으며 이에 동의합니다.</label>
                <input type="checkbox" id="agree" value="1">

                <div class="btn_confirm">
                    <a href="javascript:guest_submit(document.flogin);" class="btn_submit">비회원으로 구매하기</a>
                </div>

                <script>
                function guest_submit(f)
                {
                    if (document.getElementById('agree')) {
                        if (!document.getElementById('agree').checked) {
                            alert("개인정보수집에 대한 내용을 읽고 이에 동의하셔야 합니다.");
                            return;
                        }
                    }

                    f.url.value = "<?php echo $url; ?>";
                    f.action = "<?php echo $url; ?>";
                    f.submit();
                }
                </script>
            </section>

            <?php } else if (preg_match("/orderinquiry.php/", $url)) { ?>
            <div id="mb_login_od_wr">
                <h2>비회원 주문조회 </h2>

                <fieldset id="mb_login_od">
                    <legend>비회원 주문조회</legend>

                    <form name="forderinquiry" method="post" action="<?php echo urldecode($url); ?>" autocomplete="off">

                    <label for="od_id" class="od_id sound_only">주문서번호<strong class="sound_only"> 필수</strong></label>
                    <input type="text" name="od_id" value="<?php echo $od_id; ?>" id="od_id" required class="frm_input required" size="20" placeholder="주문서번호">
                    <label for="id_pwd" class="od_pwd sound_only" >비밀번호<strong class="sound_only"> 필수</strong></label>
                    <input type="password" name="od_pwd" size="20" id="od_pwd" required class="frm_input required" placeholder="비밀번호">
                    <input type="submit" value="확인" class="btn_submit">

                    </form>
                </fieldset>

                <section id="mb_login_odinfo">
                    <p>메일로 발송해드린 주문서의 <strong>주문번호</strong> 및 주문 시 입력하신 <strong>비밀번호</strong>를 정확히 입력해주십시오.</p>
                </section>

            </div>
            <?php } ?>

            <?php } ?>
            <?php // 쇼핑몰 사용시 여기까지 반드시 복사해 넣으세요 ?>


        </div>
    </div>

	<?}else{?>

	<div class="login_left login_not">
	
		<section class="not_sec">
			<h3>Ⅰ. 회원 확인</h3>
			<form name="flogin" action="<?php echo $login_action_url ?>" onsubmit="return flogin_submit(this);" method="post">
				<input type="hidden" name="url" value="<?php echo $login_url ?>">
				<fieldset id="login_fs">
					<table class="not_tb">
						<tr>
							<th>아이디</th>
							<td>
								<input type="text" name="mb_id" id="login_id" required class="frm_input not_ipt" size="20" maxLength="20">
							</td>
							<td rowspan="3">
								<input type="submit" value="로그인" class="btn_submit">
							</td>
						</tr>
						<tr>
							<td colspan="3" class="verse"></td>
						</tr>
						<tr>
							<th>비밀번호</th>
							<td>
								<input type="password" name="mb_password" id="login_pw" required class="frm_input not_ipt" size="20" maxLength="20">
							</td>
						</tr>
					</table>
				</fieldset>  
			</form>            
		</section>

		<section class="not_sec">
			<h3>Ⅱ. 이메일 예약확인</h3>
			<form name="not_frm_1" action="<?=G5_URL?>/sub/sub0506.php" onsubmit="return not1_submit(this);" method="get">
				<fieldset id="login_fs">
					<table class="not_tb">
						<tr>
							<th>한글명</th>
							<td>
								<input type="text" name="chk_name1" id="chk_name1" required class="frm_input not_ipt">
							</td>
							<td rowspan="3">
								<input type="submit" value="확인" class="btn_submit">
							</td>
						</tr>
						<tr>
							<td colspan="3" class="verse"></td>
						</tr>
						<tr>
							<th>이메일</th>
							<td>
								<input type="text" name="chk_email1" id="str_email01" required class="frm_input not_ipt">
								<span class="golbange">@</span>
								<input type="text" name="chk_email2" id="str_email02" required class="frm_input not_ipt">
								<select name="" id="selectEmail" class="frm_input not_ipt not_ipt2">
									<option value="">직접입력</option>
									<option value="naver.com">naver.com</option>
									<option value="hanmail.net">hanmail.net</option>
									<option value="hotmail.com">hotmail.com</option>
									<option value="nate.com">nate.com</option>
									<option value="yahoo.co.kr">yahoo.co.kr</option>
									<option value="empas.com">empas.com</option>
									<option value="dreamwiz.com">dreamwiz.com</option>
									<option value="freechal.com">freechal.com</option>
									<option value="lycos.co.kr">lycos.co.kr</option>
									<option value="korea.com">korea.com</option>
									<option value="gmail.com">gmail.com</option>
									<option value="hanmir.com">hanmir.com</option>
									<option value="paran.com">paran.com</option>
								</select>
							</td>
						</tr>
					</table>
				</fieldset>  
			</form>

		</section>

		<section class="not_sec">
			<h3>Ⅲ. 휴대전화 예약확인</h3>
			<form name="not_frm_2" action="<?=G5_URL?>/sub/sub0506.php" onsubmit="return not2_submit(this);" method="get">
				<fieldset id="login_fs">
					<table class="not_tb">
						<tr>
							<th>한글명</th>
							<td>
								<input type="text" name="chk_name2" id="chk_name2" required class="frm_input not_ipt">
							</td>
							<td rowspan="3">
								<input type="submit" value="확인" class="btn_submit">
							</td>
						</tr>
						<tr>
							<td colspan="3" class="verse"></td>
						</tr>
						<tr>
							<th>휴대전화번호</th>
							<td>
								<input type="text" name="chk_tel1" id="chk_tel1" required class="frm_input not_ipt not_ipt3" maxlength="4" onkeyup="value=value.replace(/[^\d]/g,'')">
								<span class="hp_bar">-</span>
								<input type="text" name="chk_tel2" id="chk_tel2" required class="frm_input not_ipt not_ipt3" maxlength="4" onkeyup="value=value.replace(/[^\d]/g,'')">
								<span class="hp_bar">-</span>
								<input type="text" name="chk_tel3" id="chk_tel3" required class="frm_input not_ipt not_ipt3" maxlength="4" onkeyup="value=value.replace(/[^\d]/g,'')">
							</td>
						</tr>
					</table>
				</fieldset>  
			</form>
		</section>

		<ul class="not_info">
			<li>※ 비회원으로 예약을 하신 경우 예약시 등록하신 <span>한글명</span>과 <span>이메일</span>를 입력하시면 예약내역을 확인 하실 수 있습니다.</li>
			<li>※ 비회원으로 예약을 하신 경우 예약시 등록하신 <span>한글명</span>과 <span>휴대전화번호</span>를 입력하시면 예약내역을 확인 하실 수 있습니다.</li>
		</ul>

    </div>	
	<?}?>




<script>
function not1_submit(f){
	return true;
}
function not2_submit(f){
	return true;
}
//이메일 입력방식 선택
$('#selectEmail').change(function(){ 
	$("#selectEmail option:selected").each(function () { 
		if($(this).val()== ''){ //직접입력일 경우 
			$("#str_email02").val(''); //값 초기화 
			$("#str_email02").attr("readonly",false); //활성화 
			$("#str_email02").focus();
		}else{ //직접입력이 아닐경우 
			$("#str_email02").val($(this).text()); //선택값 입력 
			$("#str_email02").attr("readonly",true); //비활성화 
		} 
	});
}); 

$(function(){
    $("#login_auto_login").click(function(){
        if (this.checked) {
            this.checked = confirm("자동로그인을 사용하시면 다음부터 회원아이디와 비밀번호를 입력하실 필요가 없습니다.\n\n공공장소에서는 개인정보가 유출될 수 있으니 사용을 자제하여 주십시오.\n\n자동로그인을 사용하시겠습니까?");
        }
    });
});

function flogin_submit(f)
{
    return true;
}
</script>
<!-- } 로그인 끝 -->
</div>

</div>
<?
include_once(G5_PATH."/_tail.php");
?>