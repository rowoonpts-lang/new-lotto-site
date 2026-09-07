<?php

include_once("./_common.php");

header('Content-Type: text/html; charset=utf-8');
header('X-Robots-Tag: noindex, nofollow, noarchive', true);
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$token = isset($_GET['k'])
    ? trim((string) $_GET['k'])
    : '';

if (
    $token === ''
    || !preg_match('/^[a-f0-9]{32}$/', $token)
) {
    http_response_code(404);
    exit('유효하지 않은 링크입니다.');
}

$tokenHash = hash('sha256', $token);
$tokenHashSql = sql_real_escape_string($tokenHash);

$linkRow = sql_fetch(
    "select l.mb_id,
            m.mb_name,
            m.mb_leave_date
       from l_lotto_share_link l
       inner join g5_member m
         on m.mb_id = l.mb_id
      where l.token_hash = '{$tokenHashSql}'
      limit 1",
    false
);

if (empty($linkRow['mb_id'])) {
    http_response_code(404);
    exit('유효하지 않은 링크입니다.');
}

if (trim((string) $linkRow['mb_leave_date']) !== '') {
    http_response_code(403);
    exit('사용할 수 없는 링크입니다.');
}

$mbIdSql = sql_real_escape_string($linkRow['mb_id']);

$draws = array();

$drawResult = sql_query(
    "select distinct draw_no
       from l_member_combination
      where mb_id = '{$mbIdSql}'
      order by draw_no desc",
    false
);

while ($drawRow = sql_fetch_array($drawResult)) {
    $drawNo = (int) $drawRow['draw_no'];

    if ($drawNo > 0) {
        $draws[] = $drawNo;
    }
}

$selectedDraw = isset($_GET['draw_no'])
    ? (int) $_GET['draw_no']
    : 0;

if (
    $selectedDraw < 1
    || !in_array($selectedDraw, $draws, true)
) {
    $selectedDraw = !empty($draws)
        ? (int) $draws[0]
        : 0;
}

$combinations = array();

if ($selectedDraw > 0) {
    $result = sql_query(
        "select num1,
                num2,
                num3,
                num4,
                num5,
                num6
           from l_member_combination
          where mb_id = '{$mbIdSql}'
            and draw_no = '{$selectedDraw}'
          order by distribution_seq asc, lmc_id asc",
        false
    );

    while ($row = sql_fetch_array($result)) {
        $combinations[] = array(
            (int) $row['num1'],
            (int) $row['num2'],
            (int) $row['num3'],
            (int) $row['num4'],
            (int) $row['num5'],
            (int) $row['num6'],
        );
    }
}

function lottoLinkHtml($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta
    name="viewport"
    content="width=device-width, initial-scale=1, viewport-fit=cover"
>
<title>추천번호 확인</title>
<style>
body {
    margin: 0;
    background: #f5f6f8;
    color: #222;
    font-family: Arial, "Apple SD Gothic Neo", sans-serif;
}
.wrap {
    max-width: 640px;
    margin: 0 auto;
    padding: 20px 14px 40px;
}
.card {
    background: #fff;
    border-radius: 12px;
    padding: 20px 16px;
    box-shadow: 0 2px 10px rgba(0,0,0,.06);
}
h1 {
    margin: 0 0 8px;
    font-size: 24px;
}
.member {
    margin-bottom: 18px;
    color: #666;
}
select {
    width: 100%;
    height: 46px;
    margin-bottom: 18px;
    padding: 0 10px;
    border: 1px solid #d7dbe0;
    border-radius: 8px;
    font-size: 16px;
    background: #fff;
}
.combo {
    display: flex;
    gap: 6px;
    align-items: center;
    margin: 10px 0;
    padding: 12px 10px;
    background: #f8f9fb;
    border-radius: 8px;
}
.no {
    min-width: 26px;
    font-weight: 700;
}
.ball {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    font-size: 14px;
    font-weight: 700;
    color: #fff;
}
.ball-yellow {
    background: #fbc400;
    color: #222;
}
.ball-blue {
    background: #69c8f2;
}
.ball-red {
    background: #ff7272;
}
.ball-gray {
    background: #aaa;
}
.ball-green {
    background: #b0d840;
    color: #222;
}
.empty {
    padding: 30px 0;
    text-align: center;
    color: #777;
}
</style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <h1>추천번호</h1>

        <div class="member">
            <?=lottoLinkHtml($linkRow['mb_name'])?>님
        </div>

        <?php if (!empty($draws)) { ?>
        <form method="get">
            <input
                type="hidden"
                name="k"
                value="<?=lottoLinkHtml($token)?>"
            >

            <select
                name="draw_no"
                onchange="this.form.submit();"
            >
                <?php foreach ($draws as $drawNo) { ?>
                <option
                    value="<?=(int) $drawNo?>"
                    <?php if ($selectedDraw === (int) $drawNo) { ?>
                    selected
                    <?php } ?>
                >
                    <?=(int) $drawNo?>회차
                </option>
                <?php } ?>
            </select>
        </form>

        <?php foreach ($combinations as $index => $numbers) { ?>
        <div class="combo">
            <span class="no"><?=(int) ($index + 1)?>.</span>

            <?php foreach ($numbers as $number) {
                $ballClass = 'ball-yellow';

                if ($number >= 11 && $number <= 20) {
                    $ballClass = 'ball-blue';
                } elseif ($number >= 21 && $number <= 30) {
                    $ballClass = 'ball-red';
                } elseif ($number >= 31 && $number <= 40) {
                    $ballClass = 'ball-gray';
                } elseif ($number >= 41) {
                    $ballClass = 'ball-green';
                }
            ?>
            <span class="ball <?=$ballClass?>">
                <?=str_pad((string) $number, 2, '0', STR_PAD_LEFT)?>
            </span>
            <?php } ?>
        </div>
        <?php } ?>

        <?php if (empty($combinations)) { ?>
        <div class="empty">
            해당 회차의 추천번호가 없습니다.
        </div>
        <?php } ?>

        <?php } else { ?>
        <div class="empty">
            아직 제공된 추천번호가 없습니다.
        </div>
        <?php } ?>
    </div>
</div>
</body>
</html>
