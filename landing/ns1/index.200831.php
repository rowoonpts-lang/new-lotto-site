<?php
if (strpos($_SERVER['HTTP_HOST'], 'lotto-js.co.kr') === false) {
    exit;
} else {
    if (preg_match('/^\/lotto-js\//', $_SERVER['REQUEST_URI'])) {
        $request_uri = preg_replace('/^\/lotto-js\//', '', $_SERVER['REQUEST_URI']);
        $http = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ? 's' : '') . '://';
        header("Location: ".$http.$_SERVER['HTTP_HOST'].'/'.$request_uri);
        exit;
    }
}

$page_title = '로또중심';
include '_common.php';

$w = date('w');
if ($w == 0) {
    $w = 7;
}
// 이번주 월요일
$date1 = date('Y년 m월 d일',strtotime('-'.($w - 1).'days'));
// +6일
$date2 = date('Y년 m월 d일',strtotime('+'.(6 - ($w - 1)).'days'));
// 컨텐츠입력 -7일
$date3 = date('Y-m-d',strtotime('-7days'));

// 2차랜딩용 adcode
$adcode2 = $adcode2_str = '';
if ($adcode) {
    $adcode2 = $adcode.'bg';
    $adcode2_str = '?adcode='.$adcode2;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=Edge" />
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
<meta name="format-detection" content="telephone=no" />
<title><?php echo $page_title; ?></title>
<link rel="stylesheet" href="css/style.css">

<?php
// 포함된 adcode 는 백팝업 '제외'
if (! in_array($_GET['adcode'], array('test', 'tbl_pc', 'tbl_m', 'adpn_n', 'adpn_t')) ) { ?>
<script src="/js/newpop2.js"></script>
<?php } ?>
</head>
<body>

<div class="popup-db" id="popup_db">
    <div class="bg"></div>
    <!-- 신청폼2 -->
    <div class="dbform" id="dbform2">
        <div class="form-title"><img src="imgs/form2_title.jpg" alt=""></div>
        <form name="app_fm2" id="form2" class="form" action="<?php echo G5_BBS_URL ?>/order_update.php" onsubmit="return fwrite_submit(this);" method="post" autocomplete="off" data-landing="1">
            <input type="hidden" name="w" value="">
            <input type="hidden" name="bo_table" value="lj_robot1">
            <input type="hidden" name="ca_name" value="<?php echo $adcode; ?>">
            <input type="hidden" name="url" value="<?php echo G5_URL ?>/../ns1/<?php if ($adcode) { echo '?adcode='.$adcode; } ?>">
            <input type="hidden" name="wr_subject" value="">
            <input type="hidden" name="wr_content" value="">
            <input type="hidden" name="wr_10" value="ns1">
			<input type="hidden" name="ltjs_id" value="ns1">
            <div class="item-list">
                <div class="item-row">
                    <label for="f21" class="hidden">이름</label>
                    <div class="field">
                        <input type="text" class="text required" id="f21" name="wr_name" required placeholder="이름을 입력하세요…(이름 없이도 신청가능)">
                    </div>
                </div>
                <div class="item-row">
                    <label for="f22" class="hidden">연락처</label>
                    <div class="field">
                        <input type="tel" name="wr_3" id="f22" required class="text required telcellnum" maxlength="14" placeholder="연락처를 입력하세요…">
                    </div>
                </div>
                <div class="submit">
                    <input type="image" src="imgs/form2_submit.gif" class="submit-btn" alt="신청하기">
                </div>
                <div class="agree">
                    <div>
                        <label class="agree-label"><input type="checkbox" name="agree1" value="1" checked> <span>개인정보취급방침및수집동의</span></label>
                        <a href="privacy.html" target="_blank" class="privacy-link" data-popupwidth="600px" data-popupheight="800px">[더보기]</a>
                    </div>
                    <div>
                        <label class="agree-label"><input type="checkbox" name="agree2" value="1" checked> <span>마케팅정보수신동의</span></label>
                        <a href="privacy2.html" target="_blank" class="privacy-link" data-popupwidth="600px" data-popupheight="800px">[더보기]</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- //신청폼2 -->
</div>

<div class="header">
    <div class="wrap">
        <img src="imgs/header.jpg" alt="">
    </div>
</div>
<div class="wrap clr container">
    <div class="snb" id="snb">
        <div class="imgbox"><a href="http://lotto-js.co.kr/rl1/<?php echo $adcode2_str; ?>" target="_blank"><img src="imgs/ad_1.jpg" alt=""></a></div>
    </div>
    <div class="imgbox banner banner-1"><a href="http://lotto-js.co.kr/robot1/<?php echo $adcode2_str; ?>" target="_blank"><img src="imgs/ad_2.jpg" alt=""></a></div>
    <div class="post">
        <article class="inner">
            <div class="post-title">
                <div class="inner">
                    <div class="head">
                        <h1>[화제] 로또1등 2번 당첨된 직장인의 <span class="color1">"당첨노하우"</span> 공개!</h1>

                        <div class="subhead">
                            <em>- A.I 로또추출기로 1등 당첨되다</em>
                            <em>- 수동으로 로또 당첨확률 높이는 방법은?</em>
                        </div>
                    </div>
                </div>
                <div class="writer clr">
                    <span class="regdate">컨텐츠입력 : <span id="regdate"><?php echo $date3; ?></span></span>
                    <span class="sns"><img src="imgs/print.jpg" alt=""></span>
                </div>
            </div>
            <div class="content-wrap">
                <div class="content">
                    <p class="imgbox banner">
                        <img src="imgs/co1.jpg" alt="">
                    </p>
                    <p>
                        <b class="color1">"로또 1등 2번 당첨되었어요!" - 최두헌(가명/38세) 인터뷰</b>
                    </p>
                    <p>
                        로또 1등으로 당첨될 확률은 얼마일까? 정확하게 8,145,060분의 1로 사람이 번개를 맞을 확률보다 낮은 확률이다. 즉, 로또 1등 당첨 이라는것은 불가능에 가까운 일이다.
                    </p>
                    <p>
                        이렇게 어려운 로또 1등을 2번이나 당첨된 사람이 있어 화제가 되고 있다. 그 주인공은 청담동에 살고 있는 최두헌씨다. 최씨가 어떻게 로또 1등을 2번이나 당첨되었는지 그 노하우를 공개해 화제가 되고 있다.
                    </p>
                    <p>
                        최씨는 10년 이상 매주 로또를 구매해왔으나, 5등 이상 당첨된적이 없었다. 그러던 어느날 뉴스를 통하여 <b class="color1"><u>A.I 로또 추출기</u></b>라는 것을 알게되었고, 이 추출기를 통하여 1등 당첨된 사람들의 실제 후기를 보게되었다.
                    </p>
                    <p>
                        1등 당첨된 사람들의 후기를 보니 호기심이 생겨 로또 추출기를 통해 1등 예상번호를 추천받게 되었다. 그 결과
                        정확히 2개월만에 1등 당첨이 되었고, 6개월 후 한번 더 1등에 당첨이 될  수 있었다.
                    </p>
                    <p>
                        최씨는 A.I 로또 추출기를 알게된 것이 인생 최대에 행운이었다고 말하며, 지금도 꾸준히 추출기를 통해 로또를 구매하고 있다고 전했다.
                    </p>
                    <p>
                        <a href="#popup_db" class="open-popupform-btn btn-2 open-popup-db">◆10억 가치의 로또1등 예측번호 확인하기(클릭)</a>
                    </p>
                    <p class="imgbox banner">
                        <img src="imgs/co2.jpg" alt="">
                    </p>
                    <p>
                        이처럼 A.I 로또 추출기를 통해 1등 당첨된 사례가 지속적으로 늘어나고 있다. A.I 로또 추출기가 추천해준 번호가 1등 당첨될 확률이 높은 이유는 무엇일까? 이유는 다음과 같다.
                    </p>
                    <p>
                        A.I 로또 추출기는 미국 파워볼 분석 시스템을 기반으로 탄생한 QPP(Quick Powerball Pick) 시스템을 적용시켰기 때문이다. QPP시스템은 과거 당첨된 자료를 바탕으로 숫자들의 출현 빈도와 특정 패턴 정보를 분석하여 숫자를 조합해주며, A.I 분석 시스템 알고리즘을 통하여 당첨 확률을 높히고 있다.
                    </p>
                    <p>
                        로또중심에서도 동일한 알고리즘의 A.I 로또 추출기를 활용하여 회원들에게 로또1등 예상번호를 추천해주고 있으며, 높은 당첨 확률로 로또중심을 찾는 고객의 수가 급증하고 있다.
                    </p>
                    <p>
                        이에 로또중심에서는 선착순 200명에게 A.I 로또 추출기를 통해 이번주 1등 예상번호를 무료로 추천해주고 있다.
                    </p>
                    <p>
                        하단의 이벤트 신청란에 이름과 연락처만 기입하면 이번주 로또 1등 예상번호를 문자로 받을 수 있다. 이벤트 시작 이후 신청자가 몰리고 있어며, 앞으로도 로또 1등 당첨을 꿈꾸는 서민들의 관심은 더욱 늘어날 전망이다.
                    </p>
                    <p>
                        <a href="#popup_db" class="open-popupform-btn open-popup-db">(링크)10억당첨 가능한 로또1등 예측번호 신청하기</a>
                    </p>
                    <!-- 신청폼 -->
                    <div class="dbform" id="dbform">
                        <div class="form-title"><img src="imgs/form_title.png" alt=""></div>
                        <form name="app_fm1" id="form1" class="form" action="<?php echo G5_BBS_URL ?>/order_update.php" onsubmit="return fwrite_submit(this);" method="post" autocomplete="off" data-landing="1">
                            <input type="hidden" name="w" value="">
                            <input type="hidden" name="bo_table" value="lj_robot1">
                            <input type="hidden" name="ca_name" value="<?php echo $adcode; ?>">
                            <input type="hidden" name="url" value="<?php echo G5_URL ?>/../ns1/<?php if ($adcode) { echo '?adcode='.$adcode; } ?>">
                            <input type="hidden" name="wr_subject" value="">
                            <input type="hidden" name="wr_content" value="">
                            <input type="hidden" name="wr_10" value="ns1">
							<input type="hidden" name="ltjs_id" value="ns1">
                            <div class="item-list">
                                <div class="form-date">
                                    <b>모집기간</b> <?php echo $date1; ?> ~ <?php echo $date2; ?><br>
                                    <b>신청현황</b> 총200명 중 <span id="dday_1">0</span>명 신청완료!
                                </div>
                                <div class="item-row">
                                    <label for="f1" class="hidden">이름</label>
                                    <div class="field">
                                        <input type="text" class="text required" id="f1" name="wr_name" size="10" required placeholder="이름을 입력하세요…(이름 없이도 신청가능)">
                                    </div>
                                </div>
                                <div class="item-row">
                                    <label for="f2" class="hidden">연락처</label>
                                    <div class="field">
                                        <input type="tel" name="wr_3" id="f2" required class="text required telcellnum" maxlength="14" placeholder="연락처를 입력하세요…">
                                    </div>
                                </div>
                                <div class="submit">
                                    <input type="image" src="imgs/form_submit.png" class="submit-btn" alt="신청하기">
                                </div>
                                <div class="agree">
                                    <div>
                                        <label class="agree-label"><input type="checkbox" name="agree1" value="1" checked> <span>개인정보취급방침및수집동의</span></label>
                                        <a href="privacy.html" target="_blank" class="privacy-link" data-popupwidth="600px" data-popupheight="800px">[더보기]</a>
                                    </div>
                                    <div>
                                        <label class="agree-label"><input type="checkbox" name="agree2" value="1" checked> <span>마케팅정보수신동의</span></label>
                                        <a href="privacy2.html" target="_blank" class="privacy-link" data-popupwidth="600px" data-popupheight="800px">[더보기]</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- //신청폼 -->

                    <div class="main-latest">
                        <strong class="title">당신에게 추천하는 기사</strong>
                        <ul>
                            <li><a href="http://lotto-js.co.kr/rl1/<?php echo $adcode2_str; ?>" target="_blank"><img src="imgs/main_latest_1.jpg" alt=""><span>이번주 로또1등 되서 슈퍼카샀다!</span></a></li>
                            <li><a href="http://lotto-js.co.kr/robot1/<?php echo $adcode2_str; ?>" target="_blank"><img src="imgs/main_latest_2.jpg" alt=""><span>명품女의 사생활 유출되다…충격</span></a></li>
                            <li><a href="http://lotto-js.co.kr/rl1/<?php echo $adcode2_str; ?>" target="_blank"><img src="imgs/main_latest_3.jpg" alt=""><span>5초만 기다리면 로또1등 번호를 공개합니다!</span></a></li>
                            <li><a href="http://lotto-js.co.kr/robot1/<?php echo $adcode2_str; ?>" target="_blank"><img src="imgs/main_latest_4.jpg" alt=""><span>사직서 내고 세계여행떠난 여비서의 비밀…</span></a></li>
                        </ul>
                    </div>

                    <div class="comment-wrap">
                        <div class="comment-header">
                            <strong>댓글 105</strong><img src="imgs/comment_reload.jpg" alt="">
                            <div class="info"><img src="imgs/comment_info.jpg" alt=""></div>
                        </div>
                        <p class="imgbox comment-title"><a href="#dbform"><img src="imgs/comment_write.jpg" alt=""></a></p>
                        <ul class="comment">
                            <li>
                                <div class="name">kom00***</div>
                                <p>저도 A.I 로또 분석기로 1등 1번, 2등 3번 당첨됐어요 ㅎㅎ 완전 대박입니다</p>
                                <div id="regdate2" class="regdate"></div><div class="singo">| 신고</div>
                                <div class="feedback clr">
                                    <span class="re">답글 3</span>
                                    <div class="vote">
                                        <span class="good">85</span>
                                        <span class="bad">1</span>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="name">dlaen***</div>
                                <p>대박이네요 저도 이벤트 신청해서 번호 받았습니다. 바로 로또사러갑니다!!</p>
                                <div id="regdate3" class="regdate"></div><div class="singo">| 신고</div>
                                <div class="feedback clr">
                                    <span class="re">답글 3</span>
                                    <div class="vote">
                                        <span class="good">31</span>
                                        <span class="bad">0</span>
                                    </div>
                                </div>
                            </li>
                            <li>
                                <div class="name">hhho***</div>
                                <p>아는 지인이 여기통해서 1등 당첨되서 저도 번호 추천받고 있습니다 전 저번주에 2등 당첨됐어요 ㅎㅎ</p>
                                <div id="regdate4" class="regdate"></div><div class="singo">| 신고</div>
                                <div class="feedback clr">
                                    <span class="re">답글 3</span>
                                    <div class="vote">
                                        <span class="good">48</span>
                                        <span class="bad">0</span>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="page-desc">* 위 내용은 극화한 로또중심의 홍보용 컨텐츠입니다</div>
                </div>
            </div>
        </article>
    </div>
</div>
<div class="footer">
    본 사이트에서 제공되는 정보는 참고용 자료이며, 과한 로또는 정신적, 금전적으로 위험할 수 있습니다<br>
    상호 : 로또중심ㅣ대표 : 김민지ㅣ사업자등록번호 : 350-04-01576 <br>
    주소 : 인천 광역시 남동구 남동대로777번길 43, 5층ㅣ메일주소 : black-h@naver.com
</div>
<script>
var g5_url = '<?php echo G5_URL ?>';
var g5_bbs_url = '<?php echo G5_BBS_URL ?>';
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script src="<?php echo G5_URL ?>/js/wrest2.js"></script>
<script src="<?php echo G5_URL ?>/js/common.js"></script>
<script src="script/jquery.placeholder.min.js"></script>
<script> $(function() { $('input, textarea').placeholder(); }); </script>
<script src="script/countUp.js"></script>
<script>
$(function() {

    var new_win;
    $('[class^=privacy-link], [class^=popup-link]').on('click', function(e) {
        e.preventDefault();
        var width = $(this).data('popupwidth') || '580px';
        var height = $(this).data('popupheight') || '580px';
        if ($(this).data('popupheight-full')) {
            height= screen.height - 120;
        }
        new_win = window.open($(this).attr('href'), 'popup', 'toolbar=no, location=no, status=no, menubar=no, scrollbars=yes, resizeble=no, width='+width+', height='+height);
    });

    $('a[href^=#], area[href^=#]').not('.open-popup-db').click(function(){
        $('html, body').animate({
               scrollTop: $( $.attr(this, 'href') ).offset().top
        }, 300);
        return false;
    });

    $(window).scroll(function(){
        if ($(this).scrollTop() >= 185) {
            $('#snb').addClass('is-fixed');
        } else {
            $('#snb').removeClass('is-fixed');
        }
    });

    $('.open-popup-db').click(function(e) {
        e.preventDefault();
        $('#popup_db').addClass('is-active');
        popup_db();
    });

    $('#popup_db .bg').click(function() {
        $('#popup_db').removeClass('is-active');
    });

    $(document.body).on('scroll', onScroll); // for mobile
    $(window).on('scroll', onScroll);

    var dday_1 = new CountUp('dday_1', 179, {duration: 2.5});

    function onScroll() {
        var win_bottom = $(window).scrollTop() + $(window).height();

        // dday_1 용
        $('#dday_1').each(function() {
            if (this.is_start != true) {
                if (win_bottom > $(this).offset().top) {
                    dday_1.start();
                    this.is_start = true;
                }
            }
        });
    }

    setTimeout(onScroll, 200);

});

function popup_db() {
    if ($('#popup_db').hasClass('is-active')) {
        var top = ($(window).height() - $('#popup_db .dbform').height()) / 2;
        $('#popup_db').css('top', $(document).scrollTop() + top);
    }
}

function fwrite_submit(f) {

    if(!f.agree1.checked){
        alert("개인정보취급방침에 동의가 필요합니다.");
        f.agree1.focus();
        return false;
    }

    if(!f.agree2.checked){
        alert("마케팅정보수신에 동의가 필요합니다.");
        f.agree2.focus();
        return false;
    }

    return true;
}

document.getElementById('regdate2').innerHTML = '6시간전';
document.getElementById('regdate3').innerHTML = '8시간전';
document.getElementById('regdate4').innerHTML = '9시간전';
</script>

</body>
</html>
