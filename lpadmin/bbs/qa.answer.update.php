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
        G5_LADMIN_URL.'/bbs/qa.list.php'
    );
    exit;
}

lottoBbsTokenCheck();

$w = isset($_POST['w'])
    ? trim((string) $_POST['w'])
    : '';

$qaId = isset($_POST['qa_id'])
    ? (int) $_POST['qa_id']
    : 0;

$answerId = isset($_POST['answer_id'])
    ? (int) $_POST['answer_id']
    : 0;

$subject = isset($_POST['qa_subject'])
    ? trim((string) $_POST['qa_subject'])
    : '';

$content = isset($_POST['qa_content'])
    ? trim((string) $_POST['qa_content'])
    : '';

$qaHtml = isset($_POST['qa_html'])
    ? (int) $_POST['qa_html']
    : 0;

if (!in_array($w, array('a', 'u'), true)) {
    alert(
        '올바른 처리 방식이 아닙니다.',
        G5_LADMIN_URL.'/bbs/qa.list.php'
    );
    exit;
}

if ($qaId < 1) {
    alert(
        '문의글을 선택해 주세요.',
        G5_LADMIN_URL.'/bbs/qa.list.php'
    );
    exit;
}

if ($subject === '') {
    alert('답변 제목을 입력해 주세요.');
    exit;
}

if ($content === '') {
    alert('답변 내용을 입력해 주세요.');
    exit;
}

$question = sql_fetch(
    "select *
       from {$g5['qa_content_table']}
      where qa_id = '{$qaId}'
        and qa_type = 0
      limit 1",
    false
);

if (empty($question['qa_id'])) {
    alert(
        '문의글을 찾을 수 없습니다.',
        G5_LADMIN_URL.'/bbs/qa.list.php'
    );
    exit;
}

$qaconfig = get_qa_config();

$subjectSql = sql_real_escape_string($subject);
$contentSql = sql_real_escape_string($content);
$loginMbIdSql = sql_real_escape_string($loginMbId);

$writerName = isset($member['mb_nick'])
    ? trim((string) $member['mb_nick'])
    : $loginMbId;

$writerNameSql = sql_real_escape_string($writerName);

$clientIp = function_exists('lottoAdminGetClientIp')
    ? lottoAdminGetClientIp()
    : (isset($_SERVER['REMOTE_ADDR'])
        ? $_SERVER['REMOTE_ADDR']
        : '');

$clientIpSql = sql_real_escape_string($clientIp);

if ($w === 'a') {

    $existing = sql_fetch(
        "select qa_id
           from {$g5['qa_content_table']}
          where qa_type = 1
            and qa_parent = '{$qaId}'
          limit 1",
        false
    );

    if (!empty($existing['qa_id'])) {
        alert(
            '이미 등록된 답변이 있습니다.',
            G5_LADMIN_URL.'/bbs/qa.view.php?qa_id='.$qaId
        );
        exit;
    }

    $qaCategorySql = sql_real_escape_string(
        (string) $question['qa_category']
    );

    $qaNum = (int) $question['qa_num'];
    $qaRelated = (int) $question['qa_related'];

    sql_query(
        "insert into {$g5['qa_content_table']}
            set qa_num        = '{$qaNum}',
                mb_id         = '{$loginMbIdSql}',
                qa_name       = '{$writerNameSql}',
                qa_email      = '',
                qa_hp         = '',
                qa_type       = 1,
                qa_parent     = '{$qaId}',
                qa_related    = '{$qaRelated}',
                qa_category   = '{$qaCategorySql}',
                qa_email_recv = 0,
                qa_sms_recv   = 0,
                qa_html       = '{$qaHtml}',
                qa_subject    = '{$subjectSql}',
                qa_content    = '{$contentSql}',
                qa_status     = 1,
                qa_ip         = '{$clientIpSql}',
                qa_datetime   = '".G5_TIME_YMDHIS."'"
    );

    $answerId = (int) sql_insert_id();

    if ($answerId < 1) {
        alert(
            '답변 등록에 실패했습니다.',
            G5_LADMIN_URL.'/bbs/qa.view.php?qa_id='.$qaId
        );
        exit;
    }

    sql_query(
        "update {$g5['qa_content_table']}
            set qa_status = 1
          where qa_id = '{$qaId}'
            and qa_type = 0"
    );

    run_event(
        'qawrite_update',
        $qaId,
        $question,
        'a',
        $qaconfig,
        $answerId
    );

    /*
     * 기존 Gnuboard qawrite_update.php와 동일하게
     * 답변 등록 알림을 회원에게 전송합니다.
     */

    if (
        $config['cf_sms_use'] === 'icode'
        && !empty($qaconfig['qa_use_sms'])
        && !empty($question['qa_sms_recv'])
        && trim((string) $question['qa_hp']) !== ''
    ) {
        $smsContent =
            $config['cf_title'].' '
            .$qaconfig['qa_title']
            .'에 답변이 등록되었습니다.';

        $sendNumber = preg_replace(
            '/[^0-9]/',
            '',
            $qaconfig['qa_send_number']
        );

        $recvNumber = preg_replace(
            '/[^0-9]/',
            '',
            $question['qa_hp']
        );

        if ($recvNumber) {

            if ($config['cf_sms_type'] === 'LMS') {

                include_once(G5_LIB_PATH.'/icode.lms.lib.php');

                $portSetting = get_icode_port_type(
                    $config['cf_icode_id'],
                    $config['cf_icode_pw']
                );

                if ($portSetting !== false) {
                    $strDest = array($recvNumber);

                    $SMS = new LMS;
                    $SMS->SMS_con(
                        $config['cf_icode_server_ip'],
                        $config['cf_icode_id'],
                        $config['cf_icode_pw'],
                        $portSetting
                    );

                    $res = $SMS->Add(
                        $strDest,
                        $sendNumber,
                        iconv_euckr(trim($config['cf_title'])),
                        '',
                        '',
                        iconv_euckr($smsContent),
                        '',
                        count($strDest)
                    );

                    if ($res) {
                        $SMS->Send();
                    }

                    $SMS->Init();
                }

            } else {

                include_once(G5_LIB_PATH.'/icode.sms.lib.php');

                $SMS = new SMS;
                $SMS->SMS_con(
                    $config['cf_icode_server_ip'],
                    $config['cf_icode_id'],
                    $config['cf_icode_pw'],
                    $config['cf_icode_server_port']
                );

                $SMS->Add(
                    $recvNumber,
                    $sendNumber,
                    $config['cf_icode_id'],
                    iconv(
                        'utf-8',
                        'euc-kr',
                        stripslashes($smsContent)
                    ),
                    ''
                );

                $SMS->Send();
            }
        }
    }

    if (
        !empty($question['qa_email_recv'])
        && trim((string) $question['qa_email']) !== ''
    ) {
        include_once(G5_LIB_PATH.'/mailer.lib.php');

        $mailSubject =
            $config['cf_title'].' '
            .$qaconfig['qa_title']
            .' 답변 알림 메일';

        $mailContent = nl2br(
            conv_unescape_nl(
                stripslashes($content)
            )
        );

        mailer(
            $config['cf_admin_email_name'],
            $config['cf_admin_email'],
            $question['qa_email'],
            $mailSubject,
            $mailContent,
            1
        );
    }

    if (function_exists('fnSetLog')) {
        fnSetLog(
            $loginMbId,
            '1:1 상담 답변을 등록하였습니다.'
        );
    }

    alert(
        '답변을 등록했습니다.',
        G5_LADMIN_URL.'/bbs/qa.view.php?qa_id='.$qaId
    );

    exit;
}


/*
 * 기존 답변 수정
 */
if ($answerId < 1) {
    alert(
        '수정할 답변을 찾을 수 없습니다.',
        G5_LADMIN_URL.'/bbs/qa.view.php?qa_id='.$qaId
    );
    exit;
}

$answer = sql_fetch(
    "select *
       from {$g5['qa_content_table']}
      where qa_id = '{$answerId}'
        and qa_type = 1
        and qa_parent = '{$qaId}'
      limit 1",
    false
);

if (empty($answer['qa_id'])) {
    alert(
        '수정할 답변을 찾을 수 없습니다.',
        G5_LADMIN_URL.'/bbs/qa.view.php?qa_id='.$qaId
    );
    exit;
}

sql_query(
    "update {$g5['qa_content_table']}
        set qa_subject = '{$subjectSql}',
            qa_content = '{$contentSql}',
            qa_html = '{$qaHtml}'
      where qa_id = '{$answerId}'
        and qa_type = 1
        and qa_parent = '{$qaId}'"
);

run_event(
    'qawrite_update',
    $answerId,
    $answer,
    'u',
    $qaconfig,
    null
);

if (function_exists('fnSetLog')) {
    fnSetLog(
        $loginMbId,
        '1:1 상담 답변을 수정하였습니다.'
    );
}

alert(
    '답변을 수정했습니다.',
    G5_LADMIN_URL.'/bbs/qa.view.php?qa_id='.$qaId
);
?>
