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
        G5_LADMIN_URL.'/bbs/notice.list.php'
    );
    exit;
}

lottoBbsTokenCheck();

$w = isset($_POST['w'])
    ? trim((string) $_POST['w'])
    : '';

$wrId = isset($_POST['wr_id'])
    ? (int) $_POST['wr_id']
    : 0;

$writeTable = $g5['write_prefix'].'notice';
$boTable = 'notice';

$board = sql_fetch(
    "select *
       from {$g5['board_table']}
      where bo_table = '{$boTable}'
      limit 1",
    false
);

if (empty($board['bo_table'])) {
    alert(
        '공지사항 게시판 설정을 찾을 수 없습니다.',
        G5_LADMIN_URL.'/bbs/notice.list.php'
    );
    exit;
}

/*
 * 삭제
 */
if ($w === 'd') {

    if ($wrId < 1) {
        alert(
            '삭제할 공지사항을 선택해주세요.',
            G5_LADMIN_URL.'/bbs/notice.list.php'
        );
        exit;
    }

    $write = sql_fetch(
        "select *
           from {$writeTable}
          where wr_id = '{$wrId}'
            and wr_is_comment = 0
          limit 1",
        false
    );

    if (empty($write['wr_id'])) {
        alert(
            '공지사항을 찾을 수 없습니다.',
            G5_LADMIN_URL.'/bbs/notice.list.php'
        );
        exit;
    }

    /*
     * 기존 게시판 삭제 흐름에 맞춰
     * 첨부파일 및 썸네일을 함께 정리합니다.
     */
    $fileResult = sql_query(
        "select *
           from {$g5['board_file_table']}
          where bo_table = '{$boTable}'
            and wr_id = '{$wrId}'",
        false
    );

    if ($fileResult) {
        while ($file = sql_fetch_array($fileResult)) {

            $deleteFile = run_replace(
                'delete_file_path',
                G5_DATA_PATH
                    .'/file/'
                    .$boTable
                    .'/'
                    .str_replace('../', '', $file['bf_file']),
                $file
            );

            if (file_exists($deleteFile)) {
                @unlink($deleteFile);
            }

            if (
                preg_match(
                    "/\.({$config['cf_image_extension']})$/i",
                    $file['bf_file']
                )
            ) {
                delete_board_thumbnail(
                    $boTable,
                    $file['bf_file']
                );
            }
        }
    }

    delete_editor_thumbnail($write['wr_content']);

    sql_query(
        "delete
           from {$g5['board_file_table']}
          where bo_table = '{$boTable}'
            and wr_id = '{$wrId}'"
    );

    sql_query(
        "delete
           from {$writeTable}
          where wr_parent = '{$wrId}'"
    );

    sql_query(
        "delete
           from {$g5['board_new_table']}
          where bo_table = '{$boTable}'
            and wr_parent = '{$wrId}'"
    );

    sql_query(
        "delete
           from {$g5['scrap_table']}
          where bo_table = '{$boTable}'
            and wr_id = '{$wrId}'"
    );

    $boNotice = board_notice(
        $board['bo_notice'],
        $wrId
    );

    $boNoticeSql = sql_real_escape_string(
        $boNotice
    );

    sql_query(
        "update {$g5['board_table']}
            set bo_notice = '{$boNoticeSql}',
                bo_count_write =
                    if(bo_count_write > 0, bo_count_write - 1, 0)
          where bo_table = '{$boTable}'"
    );

    delete_cache_latest($boTable);

    run_event(
        'bbs_delete',
        $write,
        $board
    );

    if (function_exists('fnSetLog')) {
        fnSetLog(
            $loginMbId,
            '공지사항을 삭제하였습니다.'
        );
    }

    alert(
        '공지사항을 삭제했습니다.',
        G5_LADMIN_URL.'/bbs/notice.list.php'
    );

    exit;
}

/*
 * 등록 / 수정
 */
if ($w !== '' && $w !== 'u') {
    alert(
        '올바른 방법으로 이용해 주십시오.',
        G5_LADMIN_URL.'/bbs/notice.list.php'
    );
    exit;
}

$subject = isset($_POST['wr_subject'])
    ? trim((string) $_POST['wr_subject'])
    : '';

$content = isset($_POST['wr_content'])
    ? trim((string) $_POST['wr_content'])
    : '';

if ($subject === '') {
    alert('제목을 입력해주세요.');
    exit;
}

if ($content === '') {
    alert('내용을 입력해주세요.');
    exit;
}

$subjectSql = sql_real_escape_string($subject);
$contentSql = sql_real_escape_string($content);

$seoTitle = exist_seo_title_recursive(
    'bbs',
    generate_seo_title($subject),
    $writeTable,
    $w === 'u' ? $wrId : 0
);

$seoTitleSql = sql_real_escape_string(
    $seoTitle
);

$writerName = isset($member['mb_nick'])
    ? trim((string) $member['mb_nick'])
    : '';

if (
    isset($board['bo_use_name'])
    && $board['bo_use_name']
    && isset($member['mb_name'])
) {
    $writerName = trim(
        (string) $member['mb_name']
    );
}

$writerName = clean_xss_tags($writerName);

$writerNameSql = sql_real_escape_string(
    $writerName
);

$emailSql = sql_real_escape_string(
    isset($member['mb_email'])
        ? (string) $member['mb_email']
        : ''
);

$homepageSql = sql_real_escape_string(
    isset($member['mb_homepage'])
        ? clean_xss_tags(
            (string) $member['mb_homepage']
        )
        : ''
);

$loginMbIdSql = sql_real_escape_string(
    $loginMbId
);

$clientIp = lottoAdminGetClientIp();

$clientIpSql = sql_real_escape_string(
    $clientIp
);

if ($w === '') {

    $sql = "
        insert into {$writeTable}
        set
            wr_num = (
                select ifnull(min(sq.wr_num) - 1, -1)
                from {$writeTable} as sq
            ),
            wr_reply = '',
            wr_comment = 0,
            ca_name = '',
            wr_option = 'html1',
            wr_subject = '{$subjectSql}',
            wr_content = '{$contentSql}',
            wr_seo_title = '{$seoTitleSql}',
            wr_link1 = '',
            wr_link2 = '',
            wr_link1_hit = 0,
            wr_link2_hit = 0,
            wr_hit = 0,
            wr_good = 0,
            wr_nogood = 0,
            mb_id = '{$loginMbIdSql}',
            wr_password = '',
            wr_name = '{$writerNameSql}',
            wr_email = '{$emailSql}',
            wr_homepage = '{$homepageSql}',
            wr_datetime = '".G5_TIME_YMDHIS."',
            wr_last = '".G5_TIME_YMDHIS."',
            wr_ip = '{$clientIpSql}'
    ";

    $inserted = sql_query(
        $sql,
        false
    );

    if (!$inserted) {
        alert(
            '공지사항 등록에 실패했습니다.',
            G5_LADMIN_URL.'/bbs/notice.list.php'
        );
        exit;
    }

    $wrId = (int) sql_insert_id();

    sql_query(
        "update {$writeTable}
            set wr_parent = '{$wrId}'
          where wr_id = '{$wrId}'"
    );

    sql_query(
        "insert into {$g5['board_new_table']}
            (
                bo_table,
                wr_id,
                wr_parent,
                bn_datetime,
                mb_id
            )
         values
            (
                '{$boTable}',
                '{$wrId}',
                '{$wrId}',
                '".G5_TIME_YMDHIS."',
                '{$loginMbIdSql}'
            )"
    );

    sql_query(
        "update {$g5['board_table']}
            set bo_count_write = bo_count_write + 1
          where bo_table = '{$boTable}'"
    );

    delete_cache_latest($boTable);

    run_event(
        'write_update_after',
        $board,
        $wrId,
        '',
        '',
        get_pretty_url($boTable, $wrId)
    );

    if (function_exists('fnSetLog')) {
        fnSetLog(
            $loginMbId,
            '공지사항을 등록하였습니다.'
        );
    }

    alert(
        '공지사항을 등록했습니다.',
        G5_LADMIN_URL.'/bbs/notice.list.php'
    );

    exit;
}

/*
 * 수정
 */
if ($wrId < 1) {
    alert(
        '수정할 공지사항을 선택해주세요.',
        G5_LADMIN_URL.'/bbs/notice.list.php'
    );
    exit;
}

$write = sql_fetch(
    "select *
       from {$writeTable}
      where wr_id = '{$wrId}'
        and wr_is_comment = 0
      limit 1",
    false
);

if (empty($write['wr_id'])) {
    alert(
        '공지사항을 찾을 수 없습니다.',
        G5_LADMIN_URL.'/bbs/notice.list.php'
    );
    exit;
}

$updated = sql_query(
    "update {$writeTable}
        set wr_subject = '{$subjectSql}',
            wr_content = '{$contentSql}',
            wr_seo_title = '{$seoTitleSql}',
            wr_name = '{$writerNameSql}',
            wr_email = '{$emailSql}',
            wr_homepage = '{$homepageSql}'
      where wr_id = '{$wrId}'",
    false
);

if (!$updated) {
    alert(
        '공지사항 수정에 실패했습니다.',
        G5_LADMIN_URL.'/bbs/notice.list.php'
    );
    exit;
}

delete_cache_latest($boTable);

run_event(
    'write_update_after',
    $board,
    $wrId,
    'u',
    '',
    get_pretty_url($boTable, $wrId)
);

if (function_exists('fnSetLog')) {
    fnSetLog(
        $loginMbId,
        '공지사항을 수정하였습니다.'
    );
}

alert(
    '공지사항을 수정했습니다.',
    G5_LADMIN_URL.'/bbs/notice.list.php'
);
?>
