<?php
include_once('./_common.php');


if(defined('G5_THEME_PATH')) {
    require_once('./pc.php');
    return;
}

if (G5_IS_MOBILE) {
    include_once('./m.php');
    return;
}

?>

테마를 사용하지 않을 때 페이지1 내용

<?php

?>
