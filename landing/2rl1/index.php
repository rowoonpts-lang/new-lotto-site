<?php

$page_title = '로또중심';
include '_common.php';
?>
<!DOCTYPE html>
<html lang="ko" class="index">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=Edge" />
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
<meta name="format-detection" content="telephone=no" />
<title><?php echo $page_title; ?></title>
<link rel="stylesheet" href="css/style.css">
<!--[if lte IE 10]>
<script src="script/jquery.placeholder.min.js"></script>
<script>
$(function(){ $('input, textarea').placeholder(); });
</script>
<![endif]-->

<?php
// 포함된 adcode 는 백팝업 '제외'
if (! in_array($_GET['adcode'], array('test', 'target')) ) { ?>
<script src="/js/newpop2.js"></script>
<?php } ?>
</head>
<body class="is-step0">
<h1 class="hidden"><?php echo $page_title; ?></h1>
<div class="wrapper">
    <div class="co1">
        <div class="imgbox"><img src="imgs/co1.png" alt=""></div>
        <div class="count" id="count"><span class="small">남은 인원</span> <span id="counter_1">0</span>명</div>
    </div>
    <div class="spin">
        <img src="imgs/spin_bg.png" alt="" class="spin-bg">
        <div class="spin-box">
            <img src="imgs/spin.png" alt="" id="spin_img">
        </div>
        <button type="button" class="bt start-btn" id="start_spin"><img src="imgs/start_btn.png" alt=""></button>
    </div>
    <div class="dbform <?php if ($submit) { echo 'is-active'; } ?>" id="dbform">
        <div class="bg"></div>
        <form name="app_fm2" id="form2" class="form" action="./order_update.php" onsubmit="return fwrite_submit(this);" method="post" autocomplete="off" data-landing="1">
			<input type="hidden" name="w" value="">
			<input type="hidden" name="bo_table" value="lj_2robot1">
			<input type="hidden" name="ca_name" value="<?php echo $adcode; ?>">
			<input type="hidden" name="url" value="<?php echo G5_URL ?>/../2ns1/<?php if ($adcode) { echo '?adcode='.$adcode; } ?>">
			<input type="hidden" name="wr_subject" value="">
			<input type="hidden" name="wr_content" value="">
			<input type="hidden" name="wr_10" value="2ns1">
			<input type="hidden" name="ltjs_id" value="">
			<input type="hidden" name="lu_type" value="adtive">
			<input type="hidden" name="lu_code" value="adtive_ta">


            <div class="imgbox"><img src="imgs/form_title.png" alt=""></div>
            <div class="item-list">
                <div class="item-cols">
                    <label for="f2" class="label">연락처</label>
                    <div class="field">
                        <input type="tel" name="wr_3" id="f2" class="text required cellnum" maxlength="14" required title="연락처" placeholder="휴대폰 번호를 입력해 주세요…">
                    </div>
                </div>
            </div>
            <div class="form-submit">
                <input type="image" src="imgs/form_submit.png" class="submit-btn" alt="신청하기">
            </div>
            <div class="agree">
                <div>
                    <label class="agree-label"><input type="checkbox" name="agree1" value="1" checked> 개인정보취급방침동의</label><a href="privacy.html" class="privacy-link" target="_blank">[자세히보기]</a>
                </div>
            </div>
        </form>
    </div>
    <div class="notice">※ 본 서비스는 로또중심에서 선착순 100명에게만 제공 됩니다 ※</div>
    <div class="footer">
        <div class="copy">
            본 사이트에서 제공되는 정보는 참고용 자료이며, 과한 로또는 정신적, 금전적으로 위험할 수 있습니다<br>
            상호 : 로또중심ㅣ대표 : 김민지ㅣ사업자등록번호 : 350-04-01576 ㅣ 통신판매신고번호 2020-인천남동구-1821<br>
            대표전화 1522-8302 ㅣ 고객센터 : 인천광역시 남동구 남동대로 777번길 43, 5층(유진빌딩) ㅣ메일주소 : black-h@naver.com
        </div>
    </div>
</div>
<script>
var g5_url = '<?php echo G5_URL ?>';
var g5_bbs_url = '<?php echo G5_BBS_URL ?>';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="<?php echo G5_URL ?>/js/wrest2.js"></script>
<script src="<?php echo G5_URL ?>/js/common.js"></script>
<script src="script/jQueryRotate.js"></script>
<script src="script/jquery.easing.1.3.js"></script>
<script src="script/countUp.js"></script>
<script>
$(function(){

    var cnt = new CountUp('counter_1', 37, {duration: 2});
    cnt.start();

    var new_win;
    $('[class*=privacy-link]').on('click', function(e) {
        e.preventDefault();
        new_win = window.open($(this).attr('href'), 'popup', 'toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizeble=no, width=680px, height=680px');
    });

    $('a[href^=#], area[href^=#]').click(function(){
        $('html, body').animate({
                scrollTop: $( $.attr(this, 'href') ).offset().top
        }, 300);
        return false;
    });

    function db_open() {
        $('#dbform').addClass('is-active');
        $('html, body').animate({
            scrollTop: $('#dbform').offset().top - 50
        }, 200);
    }

    // dbform popup
    $('#start_spin').on('click', function(e) {
        e.preventDefault();
        $(this).hide();

        $('#spin_img').rotate({
            duration:3000,
            angle: 0,
            animateTo:930,
            easing: $.easing.easeInOutQuad,
            callback: function() {
                $("#spin_img").rotate({duration:2000,animateTo:810,easing: $.easing.easeInOutQuad, callback: function() {
                    $("#spin_img").rotate({duration:2000,animateTo:850,easing: $.easing.easeInOutQuad, callback: function() {
                        setTimeout(function() {
                            alert('축하합니다! 당첨되셨습니다.\n1등 번호를 확인해보겠습니까?');
                            db_open();
                        }, 500);
                    }});
                }});
            }
        });
    });

});

function fwrite_submit(f) {

    if(!f.agree1.checked){
        alert("개인정보취급방침에 동의가 필요합니다.");
        f.agree1.focus();
        return false;
    }

    return true;
}
</script>

</body>
</html>
