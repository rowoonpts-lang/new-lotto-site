<?php


$page_title = '로또중심';
include '_common.php';

if (strpos($_SERVER['HTTP_REFERER'], 'order_update.php') !== false) {
    $submit = 1;
}
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
    <div class="container">
        <div class="imgbox"><img src="imgs/header.jpg" alt=""></div>
        <div class="co">
            <div class="imgbox"><img src="imgs/co1.png" alt=""></div>
            <div class="countdown <?php if ($submit) { echo 'end'; } ?>" id="countdown">
                <span></span>
            </div>
            <div class="bottom">
                <div class="progress-bar">
                    <div class="bar"><i></i></div>
                    <div class="status">
                        <span class="a">0%</span>
                        <span class="b">30%</span>
                        <span class="c">50%</span>
                        <span class="d">70%</span>
                        <span class="e">100%</span>
                    </div>
                    <div class="per"></div>
                    <div class="status2">빅데이터 분석중...</div>
                </div>
            </div>
            <div class="imgbox"><img src="imgs/co2.png" alt=""></div>
            <ul class="loading-box">
                <li><span><img src="imgs/loading.gif" alt=""></span></li>
                <li><span><img src="imgs/loading.gif" alt=""></span></li>
                <li><span><img src="imgs/loading.gif" alt=""></span></li>
                <li><span><img src="imgs/loading.gif" alt=""></span></li>
                <li><span><img src="imgs/loading.gif" alt=""></span></li>
                <li><span><img src="imgs/loading.gif" alt=""></span></li>
            </ul>
            <div class="imgbox"><img src="imgs/co3.png" alt=""></div>
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

                <div class="imgbox"><img src="imgs/form_title.jpg" alt=""></div>
                <div class="item-list">
                    <div class="item-cols">
                        <label for="f1" class="hidden">이름</label>
                        <div class="field">
                            <input type="text" name="wr_name_real" id="f1" class="text" placeholder="이름을 입력하세요…(이름없이도 신청 가능)">
                        </div>
                    </div>
                    <div class="item-cols">
                        <label for="f2" class="hidden">연락처</label>
                        <div class="field">
                            <input type="tel" name="wr_3" id="f2" required class="text required telcellnum" maxlength="14" placeholder="전화번호를 입력하세요…">
                        </div>
                    </div>
                </div>
                <div class="agree">
                    <div>
                        <label class="agree-label"><input type="checkbox" name="agree1" value="1" checked> 개인정보취급방침동의</label><a href="privacy.html" class="privacy-link" target="_blank">[내용보기]</a>
                    </div>
                </div>
                <div class="notice">
                    별도의 회원가입 없이 무료서비스 정보제공 후 개인정보는 안전하게 폐기됩니다 <b>(미성년자 신청 불가)</b>
                </div>
                <div class="form-submit">
                    <input type="image" src="imgs/submit.jpg" class="submit-btn" alt="신청하기">
                </div>
            </form>
        </div>
        <div class="footer">
            <div class="copy">
                본 사이트에서 제공되는 정보는 참고용 자료이며, 과한 로또는 정신적, 금전적으로 위험할 수 있습니다<br>
                상호 : 로또중심ㅣ대표 : 김민지ㅣ사업자등록번호 : 350-04-01576 ㅣ 통신판매신고번호 2020-인천남동구-1821<br>
                대표전화 1522-8302 ㅣ 고객센터 : 인천광역시 남동구 남동대로 777번길 43, 5층(유진빌딩) ㅣ메일주소 : black-h@naver.com
            </div>
        </div>
    </div>
</div>
<div class="hidden" id="img_load"></div>
<script>
var g5_url = '<?php echo G5_URL ?>';
var g5_bbs_url = '<?php echo G5_BBS_URL ?>';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="<?php echo G5_URL ?>/js/wrest2.js"></script>
<script src="<?php echo G5_URL ?>/js/common.js"></script>
<script>
$(function(){

    for (var i = 5; i > 0; i--) {
        $('#img_load').append('<img src="imgs/'+i+'.png" alt="">');
    }
    $('#img_load').append('<img src="imgs/form_bg.jpg" alt="">');

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

    (function blink() {
        $('#countdown_end').fadeOut(150).fadeIn(50, function() { setTimeout(blink, 500)});
    })();

    // countdown
    var count = 5;
    function countdown() {
        count--;
        if (count <= 0) {
            countdown_end();
            return  false;
        }

        $('#countdown').prop('class', 'countdown').addClass('s-'+count);

        setTimeout(countdown, 1000);
    }

    function countdown_end() {
        $('#countdown').prop('class', 'countdown').addClass('end');
        alert('축하합니다! 당첨되셨습니다.\n1등 예상번호를 확인해보겠습니까?');
        $('body').attr('class', 'is-step1');
        progress_go();
    }

    // progress
    var status = new Array();
    var progress_per = 0;
    var $bar = $('.progress-bar .bar i');
    var $status = $('.progress-bar .status');
    var $per = $('.progress-bar .per');
    var progress_hd = 0;

    function db_open() {
        $('#dbform').addClass('is-active');
        $('html, body').animate({
            scrollTop: $('#dbform').offset().top - 50
        }, 200);
        progress_hd = 0;
    }

    function progress_go() {
        progress_hd = 1;
        if (progress_per > 100) {
            $('.progress-bar').addClass('is-end').find('.status2').text('분석완료!');
            $('body').attr('class', 'is-step2');
            setTimeout(function() { alert('분석이 완료되었습니다\n번호를 받아보시겠습니까?'); db_open(); }, 800);
            return false;
        }
        //$bar.width(progress_per+'%');
        $bar.animate({'width': progress_per+'%'}, 500);
        //$status.text(progress_per+'% '+status[progress_per]);
        $per.text(progress_per+'%');
        progress_per += 20;
        setTimeout(progress_go, 500);
    }

    // dbform popup
    $('#open_dbform').on('click', function(e) {
        e.preventDefault();
        if (progress_hd) {
            return false;
        }
        progress_go();
    });
    $('#close_dbform').on('click', function(e) {
        e.preventDefault();
        $('#dbform').removeClass('is-active');
    });

    <?php if (!$submit) { ?>
    setTimeout(countdown, 1000);
    <?php } else { ?>
    progress_per = 120;
    $bar.width('100%');
    //$status.text('100% '+status[100]);
    $per.text('100%');
    <?php } ?>

});

function fwrite_submit(f) {

    if (f.wr_name_real.value != '') {
        f.wr_name.value = f.wr_name_real.value;
    } else {
        f.wr_name.value = '익명';
    }

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
