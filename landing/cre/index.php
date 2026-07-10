<?php
/*if (strpos($_SERVER['HTTP_HOST'], 'lotto-peakk.co.kr') === false) {
    exit;
} else {
    if (preg_match('/^\/lotto-peakk\//', $_SERVER['REQUEST_URI'])) {
        $request_uri = preg_replace('/^\/lotto-peakk\//', '', $_SERVER['REQUEST_URI']);
        $http = 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ? 's' : '') . '://';
        header("Location: ".$http.$_SERVER['HTTP_HOST'].'/'.$request_uri);
        exit;
    }
}*/

include '_common.php';
$page_title = '로또피크';

// 2차랜딩용 adcode
$adcode2 = $adcode2_str = '';
if ($adcode) {
    $adcode2 = $adcode.'bg';
    $adcode2_str = '?adcode='.$adcode2;
}

$w = date('w');
if ($w == 0) {
    $w = 7;
}
// 이번주 월요일
$date1 = date('Y년 n월 j일',strtotime('-'.($w - 1).'days'));
// +6일
$date2 = date('n월 j일',strtotime('+'.(6 - ($w - 1)).'days'));
// 컨텐츠입력 -1일
$date3 = date('Y-m-d',strtotime('-1day'));

$today = date('Y-m-d');

$bo_table = 'lp_p1';
if ($bo_table) {
    $board = sql_fetch(" select * from {$g5['board_table']} where bo_table = '$bo_table' ");
    if ($board['bo_table']) {
        $write_table = $g5['write_prefix'] . $bo_table; // 게시판 테이블 전체이름
    }
}

$sql = " select * from {$write_table} order by wr_num, wr_reply limit 0, 20 ";
$result = sql_query($sql);
$i = 0;
$list = array();
while ($row = sql_fetch_array($result)) {
    $list[$i] = $row;
    $i++;
}
$list_cnt = count($list);
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

</head>
<body>

<div class="popup-db" id="popup_db">
    <div class="bg"></div>
    <!-- 신청폼2 -->
    <div class="dbform" id="dbform2">
        <div class="form-title"><img src="imgs/form2_title.png" alt=""></div>
		<form name="app_fm2" id="form2" class="form" action="./order_update.php" onsubmit="return fwrite_submit(this);" method="post" autocomplete="off" data-landing="1">
            <input type="hidden" name="w" value="">
            <input type="hidden" name="bo_table" value="lp_p1">
            <input type="hidden" name="ca_name" value="<?php echo $adcode; ?>">
            <input type="hidden" name="url" value="<?php echo G5_URL ?>/../n1-1/<?php if ($adcode) { echo '?adcode='.$adcode; } ?>">
            <input type="hidden" name="wr_subject" value="">
            <input type="hidden" name="wr_content" value="">
            <input type="hidden" name="wr_10" value="n1-1">
            <input type="hidden" name="wr_name" value="익명">
			<input type="hidden" name="lu_code" value="PEAK1">
			<input type="hidden" name="lu_type" value="PEAK1">
            <div class="item-list">
                <div class="item-row">
                    <label class="label">연락처</label>
                    <div class="field tels">
                        <select name="wr_3_1" class="select">
                            <option selected>010</option>
                            <option>011</option>
                            <option>016</option>
                            <option>017</option>
                            <option>018</option>
                            <option>019</option>
                        </select><i></i>
                        <input type="tel" name="wr_3_2" maxlength="4" class="text required tel2" required title="휴대폰 앞4자리" placeholder="휴대폰 앞4자리"><i></i>
                        <input type="tel" name="wr_3_3" maxlength="4" class="text required tel3" required title="휴대폰 뒷4자리" placeholder="뒷4자리">
                    </div>
                </div>
                <div class="submit">
                    <input type="image" src="imgs/form2_submit.gif" class="submit-btn" alt="신청하기">
                </div>
                <div class="agree">
                    <div>
                        <label class="agree-label"><input type="checkbox" name="agree1" value="1" checked> 개인정보취급방침및수집동의</label><a href="privacy.html" class="privacy-link" target="_blank">[보기]</a>
                    </div>
                    <div>
                        <label class="agree-label"><input type="checkbox" name="agree2" value="1" checked> 마케팅정보수신동의</label><a href="privacy2.html" class="privacy-link" target="_blank">[보기]</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <!-- //신청폼2 -->
</div>

<div class="header">
    <a href="#popup_db" class="open-popup-db">로또갤러리</a>
</div>
<div class="wrap clr container">
    <div class="post">
        <article class="inner">
            <div class="post-title">
                <div class="category2">■로또당첨후기</div>
                <div class="inner">
                    <div class="head">
                        <h1>3개월만에 <span class="color1">58억짜리 로또1등</span> 당첨됐어요! 제 당첨 노하우 공유합니다!</h1>
                    </div>
                </div>
                <div class="writer">
                    <div class="writer-info">
                        아이디 : simks0785  l  조회수 : 1267  l  날짜 : <span id="regdate"><?php echo $date3; ?></span>
                    </div>
                    <div class="clr">
                        <span class="sns"><a href="#popup_db" class="open-popup-db"><img src="imgs/sns.jpg" alt=""></a></span>
                        <span class="font"><a href="#popup_db" class="open-popup-db"><img src="imgs/font.jpg" alt=""></a></span>
                    </div>
                </div>
            </div>
            <div class="content-wrap">
                <div class="content">
                    <p>
                        안녕하세요 회원님들^^<br>
                        몇몇 회원님들께서 로또1등 당첨된 후기좀 알려달라고 하시더라고요

                        아시는 분은 아시겠지만 최근에 58억짜리 1등에 당첨 되었는데요<br>
                        지금부터 제 노하우 알려드릴게요

                        일단 일부 현금 뽑아둔거 인증부터 할게요^^
                    </p>
                    <p class="imgbox banner">
                        <a href="#popup_db" class="open-popup-db"><img src="imgs/co1.jpg" alt=""></a>
                    </p>
                    <p>
                        저는 10년동안 항상 토요일마다 1만원씩 로또를 샀었어요
                    </p>
                    <p>
                        그동안 로또명당도 수없이 갔었고, 심지어 조상꿈에 돼지꿈까지 다 꿔봤는데 1등은 커녕 5등만 몇번 당첨되고 4등 당첨되기도 어렵더라고요 ㅜ
                    </p>
                    <p>
                        그러던 어느날 TV에서 로또1등은 자동보다 분석번호를 활용할때 당첨확률이 약 90%이상 올라간다는 방송을 보게 되었어요
                    </p>
                    <p>
                        너무 궁금해서 자세히 방송을 봤는데 역대 1등 당첨 번호를 분석해서 모든 확률을 계산하여 1등 당첨 가능성이 가장 높은 숫자 6개를 추천해주는 업체가 있더라고요
                    </p>
                    <p>
                        저는 당장 이 업체를 통해서 매주 번호를 추천받기 시작했는데요.<br>
                        딱 3개월만에 58억짜리 로또1등 당첨되었어요
                    </p>
                    <p>
                        못믿으실것 같아 당첨 인증샷 올립니다
                    </p>
                    <p>
                        <a href="#popup_db" class="open-popupform-btn btn-2 open-popup-db">◆이번주 로또1등 예측번호는? 6,12,19,31...</a>
                    </p>
                    <p class="imgbox banner">
                        <a href="#popup_db" class="open-popup-db"><img src="imgs/co2.jpg" alt=""></a>
                    </p>
                    <p>
                        이제 믿으시겠죠? ^^
                    </p>
                    <p>
                        1등 당첨되고 나서 친구에게 이 업체를 알려줬는데요<br>
                        제 친구는 바로 2등 당첨되더라고요 ㅎㅎ
                    </p>
                    <p>
                        최근에는 이 업체와 비슷하게 로또1등 예측번호를 추천해주는 로또피크라는 업체가 있는데요<br>
                        저는 당연히 여기서도 추천 받고 있습니다 ^^
                    </p>
                    <p>
                        지금 로또피크에서 <b class="color1">"이번주 로또1등 예측번호 추천 이벤트"</b>를 하고 있으니, 저같이 인생역전 하고싶은 분들은 이벤트 참여해보세요
                    </p>
                    <p>
                        추천해주는 번호로 1등되면 저한테 밥한번 쏘세요 ^^
                    </p>
                    <p>
                        아래 이벤트 링크 남길게요
                    </p>
                    <p>
                        <a href="#popup_db" class="open-popupform-btn open-popup-db">▶이벤트링크 : 이번주 로또1등 예측번호 신청하기</a>
                    </p>
                    <!-- 신청폼 -->
                    <div class="dbform" id="dbform">
                        <div class="form-header">
                            <div class="form-title"><img src="imgs/form_title.png" alt=""></div>
                            <div class="form-date">
                                <b>이벤트제공</b> 로또피크<br>
                                <b>이벤트기간</b> <?php echo $date1; ?> ~ <?php echo $date2; ?>
                            </div>
                        </div>
                        <form name="app_fm1" id="form2" class="form" action="./order_update.php" onsubmit="return fwrite_submit(this);" method="post" autocomplete="off" data-landing="1">
                            <input type="hidden" name="w" value="">
                            <input type="hidden" name="bo_table" value="lp_p1">
                            <input type="hidden" name="ca_name" value="<?php echo $adcode; ?>">
                            <input type="hidden" name="url" value="<?php echo G5_URL ?>/../n1-1/<?php if ($adcode) { echo '?adcode='.$adcode; } ?>">
                            <input type="hidden" name="wr_subject" value="">
                            <input type="hidden" name="wr_content" value="">
                            <input type="hidden" name="wr_10" value="n1-1">
                            <input type="hidden" name="wr_name" value="익명">
							<input type="hidden" name="lu_code" value="PEAK1">
							<input type="hidden" name="lu_type" value="PEAK1">
                            <div class="item-list">
                                <div class="item-row">
                                    <label class="label">연락처</label>
                                    <div class="field tels">
                                        <select name="wr_3_1" class="select">
                                            <option selected>010</option>
                                            <option>011</option>
                                            <option>016</option>
                                            <option>017</option>
                                            <option>018</option>
                                            <option>019</option>
                                        </select><i></i>
                                        <input type="tel" name="wr_3_2" maxlength="4" class="text required tel2" required title="휴대폰 앞4자리" placeholder="휴대폰 앞4자리"><i></i>
                                        <input type="tel" name="wr_3_3" maxlength="4" class="text required tel3" required title="휴대폰 뒷4자리" placeholder="뒷4자리">
                                    </div>
                                </div>
                                <div class="submit">
                                    <input type="image" src="imgs/form_submit.gif" class="submit-btn" alt="신청하기">
                                </div>
                                <div class="agree">
                                    <div>
                                        <label class="agree-label"><input type="checkbox" name="agree1" value="1" checked> 개인정보취급방침및수집동의</label><a href="privacy.html" class="privacy-link" target="_blank">[보기]</a>
                                    </div>
                                    <div>
                                        <label class="agree-label"><input type="checkbox" name="agree2" value="1" checked> 마케팅정보수신동의</label><a href="privacy2.html" class="privacy-link" target="_blank">[보기]</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- //신청폼 -->

                    <div class="latest">
                        <div class="imgbox"><img src="imgs/latest_title.jpg" alt=""></div>
                        <div class="latest-box">
                            <div class="inner ticker-wrap" id="latest_list">
                                <ul>
                                    <?php
                                    for ($i=0; $i<$list_cnt; $i++) {
                                        $tmp_name = iconv_substr($list[$i]['wr_name'], 3, 1, "utf-8");
                                        $tel = array();
                                        list($tel[0], $tel[1], $tel[2]) = explode("-", preg_replace("/^(02|0[0-9]{2})-?([0-9]{3,4})-?([0-9]{4})$/", "$1-$2-$3", $list[$i]['wr_3']) );
                                        $tel[1] = preg_replace("/\d/", "*", $tel[1]);
                                        ksort($tel);
                                        $tel_str = implode('-', $tel);
                                    ?>
                                    <li>
                                        <div class="date"><?php echo substr($list[$i]['wr_datetime'], 0, 10); ?></div>
                                        <div class="tel"><?php if (strlen($tel_str) > 2) { echo $tel_str; } ?></div>
                                    </li>
                                    <?php } ?>
                                    <?php if ($list_cnt == 0) { ?>
                                    <li class="no-data">데이터가 없습니다.</li>
                                    <?php } ?>

                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="main-latest">
                        <strong class="title">당신에게 추천하는 기사</strong>
                        <ul>
                            <li><a href="#popup_db" class="open-popup-db"><img src="imgs/main_latest_1.jpg" alt=""><span>매주 5천원씩 로또산다면 '이것'부터 시작해라.</span></a></li>
                            <li><a href="#popup_db" class="open-popup-db"><img src="imgs/main_latest_2.jpg" alt=""><span>로또명당 절대 가지마라! '이것'해야 로또당첨 확률 올라간다.</span></a></li>
                            <li><a href="#popup_db" class="open-popup-db"><img src="imgs/main_latest_3.jpg" alt=""><span>로또계산기로 번호 계산하면 1등당첨 확률 올라간다</span></a></li>
                            <li><a href="#popup_db" class="open-popup-db"><img src="imgs/main_latest_4.jpg" alt=""><span>1년동안 로또당첨 없었다면 '이렇게'해봐라 1등당첨 가능성 높아진다.</span></a></li>
                        </ul>
                    </div>

                    <div class="comment-wrap">
                        <p class="imgbox comment-title"><a href="#popup_db" class="open-popup-db"><img src="imgs/comment_write.jpg" alt=""></a></p>
                        <ul class="comment">
                            <li>
                                <div class="name"><img src="imgs/best.gif" class="best-icon" alt="">msgll***</div>
                                <p>와 대박이네요! 저도 맨날 자동만 했었는데 이벤트 참여하고 분석번호좀 받아봐야 겠어요 1등 부럽네요 ㅜㅜ</p>
                                <div id="regdate2" class="regdate"></div><div class="singo">| 신고</div>
                                <div class="feedback clr">
                                    <a href="#popup_db" class="open-popup-db"><span class="re">답글(2)</span></a>
                                    <a href="#popup_db" class="open-popup-db">
                                        <div class="vote">
                                            <span class="good">93</span>
                                            <span class="bad">3</span>
                                        </div>
                                    </a>
                                </div>
                            </li>
                            <li>
                                <div class="name"><img src="imgs/best.gif" class="best-icon" alt="">kimask07***</div>
                                <p>저도 지인 통해서 알게된 업체인데요 저는 9개월만에 2등 두번 당첨됐어요 ^^ 지금 1등 기다리고 있습니다!!</p>
                                <div id="regdate3" class="regdate"></div><div class="singo">| 신고</div>
                                <div class="feedback clr">
                                    <a href="#popup_db" class="open-popup-db"><span class="re">답글(5)</span></a>
                                    <a href="#popup_db" class="open-popup-db">
                                        <div class="vote">
                                            <span class="good">88</span>
                                            <span class="bad">6</span>
                                        </div>
                                    </a>
                                </div>
                            </li>
                            <li>
                                <div class="name"><img src="imgs/best.gif" class="best-icon" alt="">hongbb***</div>
                                <p>로또 운으로 당첨되는 시대는 지난것 같아요 로또 번호를 분석해서 당첨될 수 있다니 대박이네요</p>
                                <div id="regdate4" class="regdate"></div><div class="singo">| 신고</div>
                                <div class="feedback clr">
                                    <a href="#popup_db" class="open-popup-db"><span class="re">답글(1)</span></a>
                                    <a href="#popup_db" class="open-popup-db">
                                        <div class="vote">
                                            <span class="good">45</span>
                                            <span class="bad">7</span>
                                        </div>
                                    </a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </article>
    </div>
</div>
<div class="footer">
    업체명:BK솔루션 ㅣ 대표:박기범 ㅣ 주소:인천광역시 미추홀구 경원대로834번길 27-12, 3층 301호<br>
    사업자등록번호:219-11-02103 ㅣ 메일:kimadad@nate.com<br>
    ※과도한 로또구매는 금정적, 정신적으로 해로울 수 있습니다※
</div>
<div class="quick-banner">
    <a href="#popup_db" class="open-popup-db"><img src="imgs/quick_banner.png" alt=""></a>
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
<script src="script/jquery.vticker.js"></script>
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

    var dday_1 = new CountUp('dday_1', 200, {duration: 2.5});

    function onScroll() {
        var win_top = $(window).scrollTop();
        var win_bottom = $(window).scrollTop() + $(window).height();

        // dday_1 용
        $('#dday_1').each(function() {
            if (this.is_start != true) {
                //if (win_bottom > $(this).offset().top) {
                if (win_bottom > $(this).offset().top && win_top < $(this).offset().top + $(this).outerHeight()) {
                    dday_1.start();
                    this.is_start = true;
                }
            }
        });

    }

    setTimeout(onScroll, 200);

    $("#latest_list").vTicker({
        speed: 400,
        pause: 2000,
        animation: 'fade',
        mousePause: true,
        showItems: 4
    });

});

function popup_db() {
    if ($('#popup_db').hasClass('is-active')) {
        var top = ($(window).height() - $('#popup_db .dbform').height()) / 2;
        $('#popup_db').css('top', $(document).scrollTop() + top);
    }
}

function fwrite_submit(f) {
/*
    if (f.wr_name_real.value != '') {
        f.wr_name.value = f.wr_name_real.value;
    } else {
        f.wr_name.value = '익명';
    }
*/
    if(!f.agree1.checked){
        alert("개인정보취급방침및수집에 동의가 필요합니다.");
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

function getTimeStamp5(day) {
  var d = new Date();
  if (day) {
    d.setTime(new Date().getTime() - (day * 24 * 60 * 60 * 1000));
  }

  var s =
    leadingZeros(d.getFullYear(), 4) + '.' +
    (d.getMonth() + 1) + '.' +
    d.getDate() + ' ' +

    leadingZeros(d.getHours(), 2) + ':' +
    leadingZeros(d.getMinutes(), 2);// + ':' +
    //leadingZeros(d.getSeconds(), 2);

  return s;
}
function leadingZeros(n, digits) {
  var zero = '';
  n = n.toString();

  if (n.length < digits) {
    for (var i = 0; i < digits - n.length; i++)
      zero += '0';
  }
  return zero + n;
}

document.getElementById('regdate2').innerHTML = getTimeStamp5(0.754);
document.getElementById('regdate3').innerHTML = getTimeStamp5(1.109);
document.getElementById('regdate4').innerHTML = getTimeStamp5(1.412);
</script>

</body>
</html>
