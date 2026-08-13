<?php
include_once("_common.php");
include_once(G5_LADMIN_PATH."/head.sub.php");

$loginLevel = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if (!lottoCanCreateStaff($loginLevel)) {
    alert('엑셀 회원등록 권한이 없습니다.');
}
?>

<div style="padding:20px;">
    <h4>엑셀 일괄 회원등록</h4>

    <div class="alert alert-info">
        <strong>등록 형식</strong><br>
        회원명 / 연락처 / 아이디 / 담당자<br><br>

        - 연락처, 아이디는 필수입니다.<br>
        - 회원명, 담당자는 선택입니다.<br>
        - 모든 회원은 무료회원으로 등록됩니다.<br>
        - 비밀번호는 연락처 마지막 4자리로 자동 설정됩니다.<br>
        - 담당자는 직원 아이디 또는 직원 이름을 입력할 수 있습니다.<br>
        - 같은 이름의 직원이 여러 명이면 직원 아이디를 입력해야 합니다.<br>
        - 업로드 파일은 .xls 형식만 사용할 수 있습니다.
    </div>

    <p>
        <a
            href="./member.excel.sample.php"
            class="btn btn-success"
        >샘플 엑셀 다운로드</a>
    </p>

    <form
        method="post"
        action="./member.excel.upload.update.php"
        enctype="multipart/form-data"
        onsubmit="return fnExcelUploadCheck();"
    >
        <div class="form-group">
            <label>엑셀 파일</label>
            <input
                type="file"
                name="excelfile"
                class="form-control"
                accept=".xls"
                required
            >
        </div>

        <button type="submit" class="btn btn-primary">
            회원 일괄등록
        </button>
    </form>
</div>

<script>
function fnExcelUploadCheck()
{
    return confirm(
        '엑셀 파일의 회원을 일괄 등록하시겠습니까?\n' +
        '파일에 오류가 하나라도 있으면 아무도 등록되지 않습니다.'
    );
}
</script>
