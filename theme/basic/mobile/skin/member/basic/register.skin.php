<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);
?>

<!-- 회원가입약관 동의 시작 { -->
<div id="register_wrap">
   
    <?php
    // 소셜로그인 사용시 소셜로그인 버튼
    @include_once(get_social_skin_path().'/social_register.skin.php');
    ?>

    <form  name="fregister" id="fregister" action="<?php echo $register_action_url ?>" onsubmit="return fregister_submit(this);" method="POST" autocomplete="off">

    <!--<p>회원가입약관 및 개인정보처리방침안내의 내용에 동의하셔야 회원가입 하실 수 있습니다.</p>-->

    <section id="fregister_term">
        <h2>이용약관</h2>

        <textarea readonly><?php echo get_text($config['cf_stipulation']) ?></textarea>

        <fieldset class="fregister_agree">
            <input type="checkbox" name="agree" value="1" id="agree11" class="agree_check">
            <label for="agree11"><span class="agree_name">동의</span></label>
        </fieldset>
    </section>

<!--        <label for="fruitItem">여기를 클릭</label>
        <input id="fruitItem" type="checkbox" />-->


    <section id="fregister_private">

        <div class="title">
            <h2>개인정보 수집·이용<span style="color:red;">(필수)</span></h2>
            <fieldset class="fregister_agree">
                <input type="checkbox" name="agree2" value="1" id="agree21" class="agree_check">
                <label for="agree21"><span class="agree_name">동의</span></label>
            </fieldset>
        </div>

        <textarea readonly><?php echo get_text($config['cf_privacy']) ?></textarea>

        <div id="fregister_chkall">
            <input type="checkbox" name="chk_all"  value="1"  id="chk_all" class="agree_check">
            <label for="chk_all"><span class="agree_name">전체선택</span></label>
        </div>
    </section>

    <div class="btn_confirm">
        <input type="submit" class="btn_submit" value="무료회원 가입하기">
    </div>


    </form>

    <script>
    function fregister_submit(f)
    {
        if (!f.agree.checked) {
            alert("회원가입약관의 내용에 동의하셔야 회원가입 하실 수 있습니다.");
            f.agree.focus();
            return false;
        }

        if (!f.agree2.checked) {
            alert("개인정보처리방침안내의 내용에 동의하셔야 회원가입 하실 수 있습니다.");
            f.agree2.focus();
            return false;
        }

        return true;
    }
    
    jQuery(function($){
        // 모두선택
        $("input[name=chk_all]").click(function() {
            if ($(this).prop('checked')) {
                $("input[name^=agree]").prop('checked', true);
            } else {
                $("input[name^=agree]").prop("checked", false);
            }
        });
    });

    </script>
</div>
<!-- } 회원가입 약관 동의 끝 -->
