<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.$member_skin_url.'/style.css">', 0);

if($member['mb_level'] >= '4'){
    $my_point = $member['mb_point2'] + $member['mb_point3'] + $member['mb_point4'] ;
}else{
    $my_point = 0;
}
?>

<!-- 내정보 시작 { -->
<script src="<?php echo G5_JS_URL ?>/jquery.register_form.js"></script>
<?php if($config['cf_cert_use'] && ($config['cf_cert_ipin'] || $config['cf_cert_hp'])) { ?>
    <script src="<?php echo G5_JS_URL ?>/certify.js?v=<?php echo G5_JS_VER; ?>"></script>
<?php } ?>



    <div id="register_form"  class="form_01">

        <div id="myinfo_top">
            <div class="t01">
                <?=get_member_img($member['mb_id']);?>
                <div>
                    <p class="my_text"><span class="small"><?php echo get_text($member['mb_name']) ?>님의<br></span>회원등급은 <span class="pink">'<?=get_level_name($member['mb_id']);?>'</span> 입니다.</p>
                    <div class="my_btn">
                        <a href="<?=G5_URL;?>/shopping_profit.php" class="btn_style_pink">등급혜택보기<i class="fal fa-angle-right icon_arrow_right"></i></a>
                        <a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=register_form.php">회원정보수정</a>
                    </div>
                </div>
            </div>
            <div class="t02">
                <ul>
                    <li>
                        <?if($member['mb_level'] >= '4'){?><a href="/bbs/reseller_point.php"><?}?>
                        <img src="<?=G5_IMG_URL?>/info_ico1.png" alt="포인트">
                        <p>
                            포인트<br>
                            <span class="<?if($member['mb_level'] >= '4'){echo"pink";}else{echo"gray";}?> "><?=number_format($my_point);?>원</span>
                        </p>
                        <?if($member['mb_level'] >= '3'){?></a><?}?>
                    </li>
                    <li>
                        <? if($member['mb_9'] == 'attendance' && $member['mb_8'] >= G5_TIME_YMD){?><a href="<?=G5_URL;?>/attendance/" class="myinfo_btn bt_pink">
                        <?}else{?><a href="<?=G5_SHOP_URL;?>/attendance.php?it_id=1565925456" class="myinfo_btn bt_pink"><?}?>
                        <img src="<?=G5_IMG_URL?>/info_ico2.png" alt="출석">
                        <p>출석<br><? if($member['mb_9'] == 'attendance' && $member['mb_8'] >= G5_TIME_YMD){?><span class="pink">서비스 중</span><?}else{?><span class="gray">미신청</span><?}?></span></p>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div id="myinfo_table">
            <h3>내 정보</h3>
            <ul>
                <li>
                    <div class="form_name"><p>회원아이디</p></div>
                    <div class="form_text"><p><strong><?php echo $member['mb_id'] ?></strong></p></div>
                </li>
                <li>
                    <div class="form_name"><p>닉네임</p></div>
                    <div class="form_text"><p><?php echo isset($member['mb_nick'])?get_text($member['mb_nick']):''; ?></p></div>
                </li>
                <li>
                    <div class="form_name"><p>회원상태</p></div>
                    <div class="form_text "><p class="pink">피키샵 <?=get_level_name($member['mb_id']);?> <span class="gray">(리셀러 회원님들에게 드리는 특별한 혜택!)</span><?  if($member['mb_level'] == '2'){?><a href="<?=G5_SHOP_URL;?>/reseller.php?it_id=1562921863" class="myinfo_btn bt_pink">리셀러 신청하기</a><?}?></p>


                    </div>
                </li>
                <?  if($member['mb_level'] >= '4'){?>
                <li>
                    <div class="form_name va_t"><p>출석대박 서비스</p></div>
                    <div class="form_text">
                        <p>
                            회원님은 피키출석 서비스 <span class="pink">[<? if($member['mb_9'] == 'attendance' && $member['mb_8'] >= G5_TIME_YMD){?>서비스 중인<?}else{?>미신청<?}?>]</span> 상태입니다.
                            <? if($member['mb_9'] == 'attendance' && $member['mb_8'] >= G5_TIME_YMD){?><a href="<?=G5_URL;?>/attendance/" class="myinfo_btn bt_pink">출석현황 보기</a>
                            <?}else{?><a href="<?=G5_SHOP_URL;?>/attendance.php?it_id=1565925456" class="myinfo_btn bt_pink">서비스 신청하기</a><?}?>
                        </p>
                    </div>
                </li>
                <?}?>
                <li>
                    <div class="form_name"><p>접속횟수</p></div>
                    <?
                    $sql = " SELECT COUNT(*) AS `cnt` FROM g5_login_check WHERE wr_mb_id = '{$member['mb_id']}' ";
                    $row = sql_fetch($sql);
                    $attendance_day = $row['cnt'];
                    ?>
                    <div class="form_text"><p><?=$attendance_day;?>회</p></div>
                </li>
                <?  if($member['mb_level'] >= '4'){?>
                <li>
                    <div class="form_name"><p>피키샵 리셀러포인트</p></div>
                    <div class="form_text"><p><span class="pink"><?=number_format($member['mb_point2']);?>원</span><a href="<?=G5_BBS_URL;?>/write.php?bo_table=profit" class="myinfo_btn bt_pink">수익금 신청 +</a><!--<span class="gray">매일 오전 10시까지 10,000원 단위로 신청이 가능합니다.</span>--></p></div>
                </li>

                <li>
                    <div class="form_name"><p>피키샵 쇼핑포인트</p></div>
                    <div class="form_text"><p><span class="pink"><?=number_format($member['mb_point3']);?>원</span><a href="<?=G5_BBS_URL;?>/write.php?bo_table=profit2" class="myinfo_btn bt_pink">수익금 신청 +</a><!--<span class="gray">매일 오전 10시까지 10,000원 단위로 신청이 가능합니다.</span>--></p></div>
                </li>
                <? if($member['mb_9'] == 'attendance' && $member['mb_8'] >= G5_TIME_YMD){?>
                <li>
                    <div class="form_name"><p>피키샵 출석포인트</p></div>
                    <div class="form_text"><p><span class="pink"><?=number_format($member['mb_point4']);?>원</span><a href="<?=G5_BBS_URL;?>/write.php?bo_table=profit3" class="myinfo_btn bt_pink">수익금 신청 +</a><!--<span class="gray">매일 오전 10시까지 10,000원 단위로 신청이 가능합니다.</span>--></p></div>
                </li>
                <?}?>
                <?}?>
                <?  if($member['mb_level'] >= '4'){?>
                <li>
                    <div class="form_name"><p>수익 홍보코드</p></div>
                    <div class="form_text"><input type="text" value="<?=G5_URL;?>/members.php?id=<?php echo $member['mb_id'] ?>" id="c04"><a class="myinfo_btn bt_black myinfo_btn_sel" onclick="copy_to_clipboard('c04')">선택하여 복사하기</a></div>
                </li>
                <?}?>
                <li>
                    <div class="form_name"><p>회원가입일</p></div>
                    <div class="form_text"><p><?php echo $member['mb_datetime'] ?></p></div>
                </li>
            </ul>
        </div>

    </div>
    <div class="btn_confirm">
        <a href="#" class="btn_style_pink">홈으로</a>
    </div>


    <!--텍스트 홍보코드 복사-->
    <script>
        function copy_to_clipboard(selector){
            var copyText = document.getElementById(selector);
            copyText.select();
            document.execCommand("Copy");
            alert('복사가 완료되었습니다!');
        }
    </script>

<!-- } 내정보 끝 -->

