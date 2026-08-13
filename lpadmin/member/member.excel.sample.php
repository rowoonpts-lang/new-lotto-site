<?php
include_once("_common.php");

$loginLevel = isset($member['mb_level'])
    ? (int) $member['mb_level']
    : 0;

if (!lottoCanCreateStaff($loginLevel)) {
    alert('엑셀 샘플 다운로드 권한이 없습니다.');
}

include_once(G5_LIB_PATH.'/PHPExcel.php');

$excel = new PHPExcel();
$sheet = $excel->setActiveSheetIndex(0);

$sheet->setTitle('회원일괄등록');

$sheet->setCellValue('A1', '회원명');
$sheet->setCellValue('B1', '연락처');
$sheet->setCellValue('C1', '아이디');
$sheet->setCellValue('D1', '담당자');

$sheet->setCellValue('A2', '홍길동');
$sheet->setCellValueExplicit(
    'B2',
    '01012345678',
    PHPExcel_Cell_DataType::TYPE_STRING
);
$sheet->setCellValue('C2', 'sample01');
$sheet->setCellValue('D2', 'teststaff1');

$sheet->setCellValue('A3', '');
$sheet->setCellValueExplicit(
    'B3',
    '01022223333',
    PHPExcel_Cell_DataType::TYPE_STRING
);
$sheet->setCellValue('C3', 'sample02');
$sheet->setCellValue('D3', '');

$sheet->getColumnDimension('A')->setWidth(18);
$sheet->getColumnDimension('B')->setWidth(18);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(20);

$filename = 'member_bulk_sample.xls';

header('Content-Type: application/vnd.ms-excel');
header(
    'Content-Disposition: attachment; filename="'.$filename.'"'
);
header('Cache-Control: max-age=0');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
$writer->save('php://output');
exit;
