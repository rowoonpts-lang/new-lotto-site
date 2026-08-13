<?php
include_once("_common.php");

$loginLevel = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if (!lottoCanCreateStaff($loginLevel)) {
    alert('엑셀 회원등록 권한이 없습니다.');
}

if (
    !isset($_SERVER['REQUEST_METHOD'])
    || $_SERVER['REQUEST_METHOD'] !== 'POST'
) {
    alert('잘못된 요청입니다.');
}

$uploadError = isset($_FILES['excelfile']['error'])
    ? (int) $_FILES['excelfile']['error']
    : UPLOAD_ERR_NO_FILE;

$uploadName = isset($_FILES['excelfile']['name'])
    ? basename((string) $_FILES['excelfile']['name'])
    : '';

$uploadTmp = isset($_FILES['excelfile']['tmp_name'])
    ? (string) $_FILES['excelfile']['tmp_name']
    : '';

$uploadSize = isset($_FILES['excelfile']['size'])
    ? (int) $_FILES['excelfile']['size']
    : 0;

$extension = strtolower(
    pathinfo($uploadName, PATHINFO_EXTENSION)
);

if (
    $uploadError !== UPLOAD_ERR_OK
    || $uploadTmp === ''
    || !is_uploaded_file($uploadTmp)
) {
    alert('업로드된 엑셀 파일이 없습니다.');
}

if ($extension !== 'xls') {
    alert('xls 파일만 업로드할 수 있습니다.');
}

if ($uploadSize <= 0 || $uploadSize > 10 * 1024 * 1024) {
    alert('엑셀 파일 크기는 10MB 이하만 가능합니다.');
}

include_once(G5_LIB_PATH.'/PHPExcel/IOFactory.php');

try {
    $reader = PHPExcel_IOFactory::createReader('Excel5');
    $excel = $reader->load($uploadTmp);
} catch (Throwable $e) {
    alert('엑셀 파일을 읽을 수 없습니다.');
}

$sheet = $excel->getSheet(0);
$highestRow = (int) $sheet->getHighestRow();

if ($highestRow < 2) {
    alert('등록할 회원 데이터가 없습니다.');
}

function lottoExcelCellValue($sheet, $column, $row)
{
    return trim(
        (string) $sheet
            ->getCellByColumnAndRow($column, $row)
            ->getFormattedValue()
    );
}

function lottoExcelNormalizePhone($value)
{
    $phone = preg_replace('/[^0-9]/', '', (string) $value);

    /*
     * Excel에서 숫자 형식으로 저장되어 맨 앞 0이 사라진
     * 1012345678 형태를 01012345678로 복원합니다.
     */
    if (
        strlen($phone) === 10
        && substr($phone, 0, 2) === '10'
    ) {
        $phone = '0'.$phone;
    }

    return $phone;
}

function lottoExcelFindStaff($staffValue)
{
    $staffValue = trim((string) $staffValue);

    if ($staffValue === '') {
        return array(
            'staff_mb_id' => '',
            'error' => '',
        );
    }

    $staffSql = sql_real_escape_string($staffValue);

    /*
     * 1순위: 직원 아이디 정확히 일치
     */
    $staffById = sql_fetch(
        "select mb_id, mb_name, mb_level
           from g5_member
          where mb_id = '{$staffSql}'
            and mb_level in (
                ".LOTTO_ROLE_STAFF1.",
                ".LOTTO_ROLE_STAFF2.",
                ".LOTTO_ROLE_TEAM_LEADER."
            )
          limit 1",
        false
    );

    if (!empty($staffById['mb_id'])) {
        return array(
            'staff_mb_id' => (string) $staffById['mb_id'],
            'error' => '',
        );
    }

    /*
     * 2순위: 직원 이름 정확히 일치
     */
    $staffResult = sql_query(
        "select mb_id, mb_name, mb_level
           from g5_member
          where mb_name = '{$staffSql}'
            and mb_level in (
                ".LOTTO_ROLE_STAFF1.",
                ".LOTTO_ROLE_STAFF2.",
                ".LOTTO_ROLE_TEAM_LEADER."
            )
          order by mb_id asc",
        false
    );

    $matchedStaff = array();

    if ($staffResult) {
        while ($staffRow = sql_fetch_array($staffResult)) {
            $matchedStaff[] = $staffRow;
        }
    }

    if (count($matchedStaff) === 1) {
        return array(
            'staff_mb_id' => (string) $matchedStaff[0]['mb_id'],
            'error' => '',
        );
    }

    if (count($matchedStaff) > 1) {
        return array(
            'staff_mb_id' => '',
            'error' => '동일한 이름의 담당자가 여러 명입니다. 직원 아이디를 입력해주세요.',
        );
    }

    return array(
        'staff_mb_id' => '',
        'error' => '등록된 담당자를 찾을 수 없습니다.',
    );
}

$rows = array();
$errors = array();

$fileIds = array();
$filePhones = array();

for ($rowNumber = 2; $rowNumber <= $highestRow; $rowNumber++) {
    $mbName = lottoExcelCellValue($sheet, 0, $rowNumber);
    $mbHp = lottoExcelNormalizePhone(
        lottoExcelCellValue($sheet, 1, $rowNumber)
    );
    $mbId = lottoExcelCellValue($sheet, 2, $rowNumber);
    $staffValue = lottoExcelCellValue($sheet, 3, $rowNumber);

    /*
     * 완전히 빈 행은 무시합니다.
     */
    if (
        $mbName === ''
        && $mbHp === ''
        && $mbId === ''
        && $staffValue === ''
    ) {
        continue;
    }

    $rowErrors = array();

    if ($mbHp === '') {
        $rowErrors[] = '연락처가 없습니다.';
    } elseif (!preg_match('/^01[0-9][0-9]{7,8}$/', $mbHp)) {
        $rowErrors[] = '연락처 형식이 올바르지 않습니다.';
    }

    if ($mbId === '') {
        $rowErrors[] = '아이디가 없습니다.';
    } elseif (strlen($mbId) > 20) {
        $rowErrors[] = '아이디는 20자 이하로 입력해주세요.';
    }

    if (strlen($mbName) > 255) {
        $rowErrors[] = '회원명이 너무 깁니다.';
    }

    if ($mbId !== '') {
        if (isset($fileIds[$mbId])) {
            $rowErrors[] =
                '엑셀 파일 안에서 아이디가 중복되었습니다. '
                .'(처음 나온 행: '.$fileIds[$mbId].'행)';
        } else {
            $fileIds[$mbId] = $rowNumber;
        }
    }

    if ($mbHp !== '') {
        if (isset($filePhones[$mbHp])) {
            $rowErrors[] =
                '엑셀 파일 안에서 연락처가 중복되었습니다. '
                .'(처음 나온 행: '.$filePhones[$mbHp].'행)';
        } else {
            $filePhones[$mbHp] = $rowNumber;
        }
    }

    if ($mbId !== '') {
        $mbIdSql = sql_real_escape_string($mbId);

        $duplicateId = sql_fetch(
            "select mb_id
               from {$g5['member_table']}
              where mb_id = '{$mbIdSql}'
              limit 1",
            false
        );

        if (!empty($duplicateId['mb_id'])) {
            $rowErrors[] = '이미 사용중인 아이디입니다.';
        }
    }

    if ($mbHp !== '') {
        $mbHpSql = sql_real_escape_string($mbHp);

        $duplicateHp = sql_fetch(
            "select mb_id
               from {$g5['member_table']}
              where mb_hp = '{$mbHpSql}'
              limit 1",
            false
        );

        if (!empty($duplicateHp['mb_id'])) {
            $rowErrors[] = '이미 사용중인 연락처입니다.';
        }
    }

    $staffResult = lottoExcelFindStaff($staffValue);

    if ($staffResult['error'] !== '') {
        $rowErrors[] = $staffResult['error'];
    }

    if (count($rowErrors) > 0) {
        $errors[] =
            $rowNumber.'행: '.implode(' ', $rowErrors);

        continue;
    }

    $rows[] = array(
        'row_number' => $rowNumber,
        'mb_name' => $mbName,
        'mb_hp' => $mbHp,
        'mb_id' => $mbId,
        'staff_mb_id' => $staffResult['staff_mb_id'],
    );
}

if (count($errors) > 0) {
    $message = "엑셀 내용을 확인해주세요.\n\n"
        .implode("\n", $errors);

    alert($message);
}

if (count($rows) < 1) {
    alert('등록할 회원 데이터가 없습니다.');
}

$loginMbId = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$loginMbIdSql = sql_real_escape_string($loginMbId);

$remoteAddr = isset($_SERVER['REMOTE_ADDR'])
    ? (string) $_SERVER['REMOTE_ADDR']
    : '';

$remoteAddrSql = sql_real_escape_string($remoteAddr);

$transactionStarted = false;

try {
    if (!sql_query('START TRANSACTION', false)) {
        throw new Exception('트랜잭션을 시작할 수 없습니다.');
    }

    $transactionStarted = true;

    foreach ($rows as $row) {
        $mbId = $row['mb_id'];
        $mbName = $row['mb_name'];
        $mbHp = $row['mb_hp'];
        $staffMbId = $row['staff_mb_id'];

        $mbIdSql = sql_real_escape_string($mbId);
        $mbNameSql = sql_real_escape_string($mbName);
        $mbHpSql = sql_real_escape_string($mbHp);

        $password = substr($mbHp, -4);
        $encryptedPassword = get_encrypt_string($password);
        $encryptedPasswordSql =
            sql_real_escape_string($encryptedPassword);

        $mbCode = fnNewMbCode();
        $mbCodeSql = sql_real_escape_string($mbCode);

        $nick = 'nick'.date('YmdHisB').$row['row_number'];
        $nickSql = sql_real_escape_string($nick);

        $email =
            date('YmdHisB')
            .$row['row_number']
            .'@lottopeak.co.kr';

        $emailSql = sql_real_escape_string($email);

        $insertMember = sql_query(
            "insert into {$g5['member_table']}
                set mb_id = '{$mbIdSql}',
                    mb_code = '{$mbCodeSql}',
                    mb_password = '{$encryptedPasswordSql}',
                    mb_name = '{$mbNameSql}',
                    mb_nick = '{$nickSql}',
                    mb_nick_date = '".G5_TIME_YMD."',
                    mb_email = '{$emailSql}',
                    mb_tel = '{$mbHpSql}',
                    mb_hp = '{$mbHpSql}',
                    mb_datetime = '".G5_TIME_YMDHIS."',
                    mb_ip = '{$remoteAddrSql}',
                    mb_level = '2',
                    mb_login_ip = '{$remoteAddrSql}',
                    mb_open_date = '".G5_TIME_YMD."',
                    mb_type = '무료회원'",
            false
        );

        if (!$insertMember) {
            throw new Exception(
                $row['row_number'].'행 회원 등록에 실패했습니다.'
            );
        }

        setEtcInfo($mbId, '');

        /*
         * setEtcInfo가 실제로 부가정보를 생성했는지 확인합니다.
         */
        $etcCheck = sql_fetch(
            "select mb_id
               from g5_member_etc
              where mb_id = '{$mbIdSql}'
              limit 1",
            false
        );

        if (empty($etcCheck['mb_id'])) {
            throw new Exception(
                $row['row_number'].'행 회원 부가정보 등록에 실패했습니다.'
            );
        }

        if ($staffMbId !== '') {
            $staffMbIdSql =
                sql_real_escape_string($staffMbId);

            $assignmentResult = sql_query(
                "insert into l_member_assignment (
                    mb_id,
                    staff_mb_id,
                    assigned_by,
                    assigned_at,
                    updated_at
                ) values (
                    '{$mbIdSql}',
                    '{$staffMbIdSql}',
                    '{$loginMbIdSql}',
                    now(),
                    now()
                )
                on duplicate key update
                    staff_mb_id = values(staff_mb_id),
                    assigned_by = values(assigned_by),
                    updated_at = now()",
                false
            );

            if (!$assignmentResult) {
                throw new Exception(
                    $row['row_number'].'행 담당자 배정에 실패했습니다.'
                );
            }
        }
    }

    if (!sql_query('COMMIT', false)) {
        throw new Exception('회원등록 저장에 실패했습니다.');
    }

    $transactionStarted = false;
} catch (Exception $e) {
    if ($transactionStarted) {
        sql_query('ROLLBACK', false);
    }

    alert(
        "회원 일괄등록 중 오류가 발생했습니다.\n"
        .$e->getMessage()
    );
}

fnSetLog(
    $loginMbId,
    '엑셀 일괄 무료회원 등록: '.count($rows).'명'
);
?>
<script>
alert("<?=count($rows)?>명의 회원이 정상적으로 등록되었습니다.");

if (window.opener && !window.opener.closed) {
    window.opener.location.reload();
}

window.close();
</script>
