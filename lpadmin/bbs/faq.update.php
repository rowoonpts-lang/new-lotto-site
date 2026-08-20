<?php

include_once("_common.php");

$loginMbId = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$loginLevel = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if (
    $loginMbId === ''
    || $loginLevel < LOTTO_ROLE_TEAM_LEADER
) {
    alert(
        '팀장 이상만 처리할 수 있습니다.',
        G5_LADMIN_URL
    );
    exit;
}

if (
    !isset($_SERVER['REQUEST_METHOD'])
    || $_SERVER['REQUEST_METHOD'] !== 'POST'
) {
    alert(
        '올바른 방법으로 이용해 주십시오.',
        G5_LADMIN_URL.'/bbs/faq.list.php'
    );
    exit;
}

lottoBbsTokenCheck();

$w = isset($_POST['w'])
    ? trim((string) $_POST['w'])
    : '';

$fmId = isset($_POST['fm_id'])
    ? (int) $_POST['fm_id']
    : 0;

$faId = isset($_POST['fa_id'])
    ? (int) $_POST['fa_id']
    : 0;

if ($fmId !== 1) {
    alert(
        '올바른 FAQ 그룹이 아닙니다.',
        G5_LADMIN_URL.'/bbs/faq.list.php'
    );
    exit;
}

$faqMaster = sql_fetch(
    "select fm_id
       from {$g5['faq_master_table']}
      where fm_id = '{$fmId}'
      limit 1",
    false
);

if (empty($faqMaster['fm_id'])) {
    alert(
        'FAQ 기본 그룹을 찾을 수 없습니다.',
        G5_LADMIN_URL.'/bbs/faq.list.php'
    );
    exit;
}

if ($w === 'd') {

    if ($faId < 1) {
        alert(
            '삭제할 FAQ를 선택해 주세요.',
            G5_LADMIN_URL.'/bbs/faq.list.php'
        );
        exit;
    }

    $faq = sql_fetch(
        "select fa_id
           from {$g5['faq_table']}
          where fa_id = '{$faId}'
            and fm_id = '{$fmId}'
          limit 1",
        false
    );

    if (empty($faq['fa_id'])) {
        alert(
            'FAQ 항목을 찾을 수 없습니다.',
            G5_LADMIN_URL.'/bbs/faq.list.php'
        );
        exit;
    }

    sql_query(
        "delete
           from {$g5['faq_table']}
          where fa_id = '{$faId}'
            and fm_id = '{$fmId}'"
    );

    run_event(
        'admin_faq_item_deleted',
        $faId,
        $fmId
    );

    if (function_exists('fnSetLog')) {
        fnSetLog(
            $loginMbId,
            'FAQ 항목을 삭제하였습니다.'
        );
    }

    alert(
        'FAQ를 삭제했습니다.',
        G5_LADMIN_URL.'/bbs/faq.list.php'
    );

    exit;
}

if ($w !== '' && $w !== 'u') {
    alert(
        '올바른 방법으로 이용해 주십시오.',
        G5_LADMIN_URL.'/bbs/faq.list.php'
    );
    exit;
}

$subject = isset($_POST['fa_subject'])
    ? trim((string) $_POST['fa_subject'])
    : '';

$content = isset($_POST['fa_content'])
    ? trim((string) $_POST['fa_content'])
    : '';

$order = isset($_POST['fa_order'])
    ? (int) $_POST['fa_order']
    : 0;

if ($subject === '') {
    alert('질문을 입력해 주세요.');
    exit;
}

if ($content === '') {
    alert('답변을 입력해 주세요.');
    exit;
}

$subjectSql = sql_real_escape_string($subject);
$contentSql = sql_real_escape_string($content);

if ($w === '') {

    sql_query(
        "insert into {$g5['faq_table']}
            set fm_id = '{$fmId}',
                fa_subject = '{$subjectSql}',
                fa_content = '{$contentSql}',
                fa_order = '{$order}'"
    );

    $faId = (int) sql_insert_id();

    run_event(
        'admin_faq_item_created',
        $faId,
        $fmId
    );

    if (function_exists('fnSetLog')) {
        fnSetLog(
            $loginMbId,
            'FAQ 항목을 등록하였습니다.'
        );
    }

    alert(
        'FAQ를 등록했습니다.',
        G5_LADMIN_URL.'/bbs/faq.list.php'
    );

    exit;
}

if ($faId < 1) {
    alert(
        '수정할 FAQ를 선택해 주세요.',
        G5_LADMIN_URL.'/bbs/faq.list.php'
    );
    exit;
}

$faq = sql_fetch(
    "select fa_id
       from {$g5['faq_table']}
      where fa_id = '{$faId}'
        and fm_id = '{$fmId}'
      limit 1",
    false
);

if (empty($faq['fa_id'])) {
    alert(
        'FAQ 항목을 찾을 수 없습니다.',
        G5_LADMIN_URL.'/bbs/faq.list.php'
    );
    exit;
}

sql_query(
    "update {$g5['faq_table']}
        set fa_subject = '{$subjectSql}',
            fa_content = '{$contentSql}',
            fa_order = '{$order}'
      where fa_id = '{$faId}'
        and fm_id = '{$fmId}'"
);

run_event(
    'admin_faq_item_updated',
    $faId,
    $fmId
);

if (function_exists('fnSetLog')) {
    fnSetLog(
        $loginMbId,
        'FAQ 항목을 수정하였습니다.'
    );
}

alert(
    'FAQ를 수정했습니다.',
    G5_LADMIN_URL.'/bbs/faq.list.php'
);
?>
