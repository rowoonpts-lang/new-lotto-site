#!/usr/bin/env python3

from datetime import datetime, timedelta
from pathlib import Path
from subprocess import run, PIPE
from zipfile import ZipFile
from xml.etree import ElementTree as ET
import re
import sys


IMPORT_DIR = Path("data/import")
OUTPUT_SQL = IMPORT_DIR / "lotto_result_import.sql"
TABLE_NAME = "g5_lotto_result"

NS = {
    "main": "http://schemas.openxmlformats.org/spreadsheetml/2006/main",
    "rel": "http://schemas.openxmlformats.org/officeDocument/2006/relationships",
    "pkg": "http://schemas.openxmlformats.org/package/2006/relationships",
}


def fail(message):
    raise RuntimeError(message)


def column_number(cell_ref):
    match = re.match(r"([A-Z]+)", cell_ref or "")

    if not match:
        return 0

    result = 0

    for char in match.group(1):
        result = result * 26 + ord(char) - ord("A") + 1

    return result


def integer_value(value, field_name):
    if value is None:
        fail(f"{field_name} 값이 없습니다.")

    text = str(value).strip()
    text = text.replace(",", "")
    text = re.sub(r"\s*(명|원)$", "", text)

    try:
        return int(float(text))
    except ValueError as exc:
        raise RuntimeError(
            f"{field_name} 값을 숫자로 변환할 수 없습니다: {value}"
        ) from exc


def read_first_sheet_rows(xlsx_path):
    with ZipFile(xlsx_path) as archive:
        shared_strings = []

        if "xl/sharedStrings.xml" in archive.namelist():
            root = ET.fromstring(archive.read("xl/sharedStrings.xml"))

            for item in root.findall("main:si", NS):
                parts = [
                    node.text or ""
                    for node in item.iter(
                        "{http://schemas.openxmlformats.org/"
                        "spreadsheetml/2006/main}t"
                    )
                ]
                shared_strings.append("".join(parts))

        workbook_root = ET.fromstring(
            archive.read("xl/workbook.xml")
        )
        relation_root = ET.fromstring(
            archive.read("xl/_rels/workbook.xml.rels")
        )

        relationships = {
            relation.attrib["Id"]: relation.attrib["Target"]
            for relation in relation_root.findall(
                "pkg:Relationship",
                NS
            )
        }

        sheet = workbook_root.find(
            "main:sheets/main:sheet",
            NS
        )

        if sheet is None:
            fail("엑셀 시트를 찾지 못했습니다.")

        relation_id = sheet.attrib[
            "{http://schemas.openxmlformats.org/"
            "officeDocument/2006/relationships}id"
        ]

        target = relationships[relation_id].lstrip("/")
        sheet_path = (
            target
            if target.startswith("xl/")
            else "xl/" + target
        )

        sheet_root = ET.fromstring(archive.read(sheet_path))

        for row in sheet_root.findall(
            ".//main:sheetData/main:row",
            NS
        ):
            row_values = {}

            for cell in row.findall("main:c", NS):
                column = column_number(cell.attrib.get("r", ""))
                cell_type = cell.attrib.get("t")
                value_node = cell.find("main:v", NS)

                value = None

                if cell_type == "inlineStr":
                    text_nodes = cell.findall(".//main:t", NS)
                    value = "".join(
                        node.text or "" for node in text_nodes
                    )
                elif value_node is not None:
                    raw_value = value_node.text or ""

                    if cell_type == "s":
                        index = int(raw_value)
                        value = shared_strings[index]
                    else:
                        value = raw_value

                row_values[column] = value

            yield int(row.attrib.get("r", "0")), row_values


def get_database_anchor():
    command = [
        "sudo",
        "mysql",
        "-Nse",
        (
            "SELECT draw_no, DATE_FORMAT(draw_date, '%Y-%m-%d') "
            f"FROM lotto_dev.{TABLE_NAME} "
            "ORDER BY draw_no DESC LIMIT 1;"
        ),
    ]

    result = run(
        command,
        stdout=PIPE,
        stderr=PIPE,
        text=True,
        check=False,
    )

    if result.returncode != 0:
        fail(
            "DB 기준 회차 조회 실패: "
            + result.stderr.strip()
        )

    output = result.stdout.strip()
    parts = output.split("\t")

    if len(parts) != 2:
        fail(f"DB 기준 회차 결과가 올바르지 않습니다: {output}")

    anchor_draw = int(parts[0])
    anchor_date = datetime.strptime(
        parts[1],
        "%Y-%m-%d"
    ).date()

    return anchor_draw, anchor_date


def sql_quote(value):
    return "'" + str(value).replace("\\", "\\\\").replace("'", "''") + "'"


def main():
    xlsx_files = sorted(IMPORT_DIR.glob("*.xlsx"))

    if len(xlsx_files) != 1:
        fail(
            "data/import에는 가져올 xlsx 파일이 정확히 "
            f"1개 있어야 합니다. 현재 {len(xlsx_files)}개입니다."
        )

    xlsx_path = xlsx_files[0]
    anchor_draw, anchor_date = get_database_anchor()

    records = {}

    for row_number, row in read_first_sheet_rows(xlsx_path):
        if row_number == 1:
            continue

        if not row.get(2):
            continue

        draw_no = integer_value(
            row.get(2),
            f"{row_number}행 회차",
        )

        numbers = [
            integer_value(
                row.get(column),
                f"{row_number}행 당첨번호 {index}",
            )
            for index, column in enumerate(
                range(3, 9),
                start=1,
            )
        ]

        bonus_number = integer_value(
            row.get(9),
            f"{row_number}행 보너스번호",
        )
        rank1_winners = integer_value(
            row.get(11),
            f"{row_number}행 1등 당첨게임 수",
        )
        rank1_amount = integer_value(
            row.get(12),
            f"{row_number}행 1등 당첨금",
        )

        if draw_no in records:
            fail(f"{draw_no}회가 엑셀에 중복돼 있습니다.")

        if len(set(numbers)) != 6:
            fail(
                f"{draw_no}회 당첨번호에 중복이 있습니다: "
                f"{numbers}"
            )

        if any(number < 1 or number > 45 for number in numbers):
            fail(
                f"{draw_no}회 당첨번호 범위가 잘못됐습니다: "
                f"{numbers}"
            )

        if bonus_number < 1 or bonus_number > 45:
            fail(
                f"{draw_no}회 보너스번호 범위가 잘못됐습니다: "
                f"{bonus_number}"
            )

        if bonus_number in numbers:
            fail(
                f"{draw_no}회 보너스번호가 당첨번호와 중복됩니다."
            )

        draw_date = anchor_date - timedelta(
            weeks=anchor_draw - draw_no
        )

        records[draw_no] = {
            "draw_date": draw_date.isoformat(),
            "numbers": numbers,
            "bonus_number": bonus_number,
            "rank1_winners": rank1_winners,
            "rank1_amount": rank1_amount,
        }

    if not records:
        fail("엑셀에서 회차 데이터를 찾지 못했습니다.")

    minimum_draw = min(records)
    maximum_draw = max(records)

    if minimum_draw != 1:
        fail(
            f"엑셀의 최소 회차가 1회가 아닙니다: {minimum_draw}회"
        )

    if maximum_draw != anchor_draw:
        fail(
            "엑셀 최신 회차와 DB 최신 회차가 다릅니다. "
            f"엑셀 {maximum_draw}회, DB {anchor_draw}회"
        )

    expected_count = maximum_draw - minimum_draw + 1

    if len(records) != expected_count:
        missing_draws = [
            draw_no
            for draw_no in range(minimum_draw, maximum_draw + 1)
            if draw_no not in records
        ]

        fail(
            "엑셀에 누락 회차가 있습니다: "
            + ", ".join(map(str, missing_draws[:20]))
        )

    sql_lines = [
        "START TRANSACTION;",
        "",
    ]

    for draw_no in sorted(records):
        record = records[draw_no]
        numbers = record["numbers"]

        values = [
            str(draw_no),
            sql_quote(record["draw_date"]),
            *[str(number) for number in numbers],
            str(record["bonus_number"]),
            str(record["rank1_winners"]),
            str(record["rank1_amount"]),
            "0",
            "0",
            "0",
            "0",
            "0",
            "0",
            "0",
            "0",
            sql_quote("official_xlsx_import"),
            "NOW()",
        ]

        sql_lines.extend([
            (
                f"INSERT IGNORE INTO `{TABLE_NAME}` ("
                "`draw_no`, `draw_date`, "
                "`num_1`, `num_2`, `num_3`, "
                "`num_4`, `num_5`, `num_6`, "
                "`bonus_num`, "
                "`rank1_winners`, `rank1_amount`, "
                "`rank2_winners`, `rank2_amount`, "
                "`rank3_winners`, `rank3_amount`, "
                "`rank4_winners`, `rank4_amount`, "
                "`rank5_winners`, `rank5_amount`, "
                "`source_url`, `fetched_at`"
                ") VALUES ("
                + ", ".join(values)
                + ");"
            )
        ])

    sql_lines.extend([
        "",
        "COMMIT;",
        "",
    ])

    OUTPUT_SQL.write_text(
        "\n".join(sql_lines),
        encoding="utf-8",
    )

    print("검증 완료")
    print(f"파일: {xlsx_path.name}")
    print(f"회차 수: {len(records)}건")
    print(f"회차 범위: {minimum_draw}회 ~ {maximum_draw}회")
    print(f"DB 기준 회차: {anchor_draw}회")
    print(f"DB 기준 날짜: {anchor_date}")
    print(f"1회 계산 날짜: {records[1]['draw_date']}")
    print(f"생성 SQL: {OUTPUT_SQL}")


if __name__ == "__main__":
    try:
        main()
    except Exception as error:
        print(f"ERROR: {error}", file=sys.stderr)
        sys.exit(1)
