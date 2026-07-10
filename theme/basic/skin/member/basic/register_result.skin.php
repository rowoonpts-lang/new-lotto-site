<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<!-- 회원가입결과 시작 { -->
<div id="reg_result">


    <img src="../../../../img/join_com.png" alt="">
    <h2>회원가입이 완료되었습니다!</h2>


    <?php if (is_use_email_certify()) {  ?>
    <p>
        회원 가입 시 입력하신 이메일 주소로 인증메일이 발송되었습니다.<br>
        발송된 인증메일을 확인하신 후 인증처리를 하시면 사이트를 원활하게 이용하실 수 있습니다.
    </p>
    <div id="result_email">
        <span>아이디</span>
        <strong><?php echo $mb['mb_id'] ?></strong><br>
        <span>이메일 주소</span>
        <strong><?php echo $mb['mb_email'] ?></strong>
    </div>
    <p>
        이메일 주소를 잘못 입력하셨다면, 사이트 관리자에게 문의해주시기 바랍니다.
    </p>
    <?php }  ?>

    <p>
        모든 회원가입절차가 완료되었습니다.<br>
        <?=$config['cf_title']?> 읽기/쓰기가 가능한 <span style="color: #ff2769;">준회원</span>이 되셨습니다.
    </p>

    <div class="result_btn_all">
        <a href="<?php echo G5_URL; ?>/" class="btn_submit">홈으로</a>
        <a href="<?php echo G5_SHOP_URL;?>/reseller.php?it_id=1562921863" class="btn_reseller btn_style_pink">리셀러 신청하기</a>
    </div>

    <p style="margin-top:40px">
        SNS 회원가입 회원이시라면,<br>
        <a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=register_form.php" style="color:#ff2769;text-decoration: underline">정보수정</a>에서 비밀번호를 설정해주세요.
    </p>
</div>
<!-- } 회원가입결과 끝 -->