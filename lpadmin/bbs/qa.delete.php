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

$qaId = isset($_POST['qa_id'])
    ? (int) $_POST['qa_id']
    : 0;

if ($qaId < 1) {
    alert(
        '삭제할 문의글을 선택해 주세요.',
        G5_LADMIN_URL.'/bbs/qa.list.php'
    );
    exit;
}

$question = sql_fetch(
    "select
        qa_id,
        qa_content,
        qa_file1,
        qa_file2
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

$deleted = array();
$tmpArray = array($qaId);

for ($k = 1; $k <= 2; $k++) {
    $fileName = isset($question['qa_file'.$k])
        ? (string) $question['qa_file'.$k]
        : '';

    if ($fileName !== '') {
        @unlink(
            G5_DATA_PATH.'/qa/'.clean_relative_paths($fileName)
        );

        if (
            preg_match(
                "/\.({$config['cf_image_extension']})$/i",
                $fileName
            )
        ) {
            delete_qa_thumbnail($fileName);
        }
    }
}

delete_editor_thumbnail($question['qa_content']);

$answerResult = sql_query(
    "select
        qa_id,
        qa_content,
        qa_file1,
        qa_file2
     from {$g5['qa_content_table']}
     where qa_type = 1
       and qa_parent = '{$qaId}'",
    false
);

if ($answerResult) {
    while ($answer = sql_fetch_array($answerResult)) {
        for ($k = 1; $k <= 2; $k++) {
            $fileName = isset($answer['qa_file'.$k])
                ? (string) $answer['qa_file'.$k]
                : '';

            if ($fileName !== '') {
                @unlink(
                    G5_DATA_PATH.'/qa/'.
                    clean_relative_paths($fileName)
                );

                if (
                    preg_match(
                        "/\.({$config['cf_image_extension']})$/i",
                        $fileName
                    )
                ) {
                    delete_qa_thumbnail($fileName);
                }
            }
        }

        delete_editor_thumbnail($answer['qa_content']);

        $deleted[] = (int) $answer['qa_id'];
    }
}

sql_query(
    "delete
       from {$g5['qa_content_table']}
      where qa_type = 1
        and qa_parent = '{$qaId}'"
);

sql_query(
    "delete
       from {$g5['qa_content_table']}
      where qa_id = '{$qaId}'
        and qa_type = 0"
);

$deleted[] = $qaId;

run_event(
    'qa_delete',
    $tmpArray,
    $deleted
);

alert(
    '1:1 상담을 삭제했습니다.',
    G5_LADMIN_URL.'/bbs/qa.list.php'
);
