<?php
include_once('_common.php');
include_once(G5_PATH.'/head.sub.php');

$turn = isset($turn) ? (int) $turn : 0;
$endTurn = isset($endTurn) ? (int) $endTurn : 0;
$ver = isset($ver) ? (string) $ver : '1';

if ($endTurn < 1) {
    $calculatedTurn = getTurn();

    if ($calculatedTurn > 1) {
        $endTurn = $calculatedTurn - 1;
    }
}

if ($turn < 1 && $endTurn > 0) {
    $turn = $endTurn;
}

$list = getLuckyNum($turn);
$hasResult = isset($list['returnValue'])
    && $list['returnValue'] === 'success';

if (!$hasResult) {
?>
<div class="flex_center">
    <div class="product_li1_1">
        등록된 당첨번호가 없습니다.
    </div>
</div>
<?php
    return;
}

$listText = implode(',', array(
    $list['drwtNo1'],
    $list['drwtNo2'],
    $list['drwtNo3'],
    $list['drwtNo4'],
    $list['drwtNo5'],
    $list['drwtNo6'],
    $list['bnusNo'],
));
?>

<div class="flex_center">
    <div class="product_li1_1">
        <?=number_format($turn)?>&nbsp;<b>당첨번호</b>
        <em>
            추첨일 :
            <?=htmlspecialchars(
                date('Y.m.d', strtotime($list['drwNoDate'])),
                ENT_QUOTES,
                'UTF-8'
            )?>
        </em>
    </div>

    <?php if ($endTurn >= 700) { ?>
    <div class="product_li1_2">
        <select onChange="fnCngTurn2(this.value, '<?=htmlspecialchars($ver, ENT_QUOTES, 'UTF-8')?>')">
            <?php for ($index = $endTurn; $index >= 700; $index--) { ?>
            <option value="<?=$index?>" <?=$turn === $index ? 'selected' : ''?>>
                <?=$index?>회차
            </option>
            <?php } ?>
        </select>
    </div>
    <?php } ?>
</div>

<div class="product_li1_3">
    <ul class="flex_center">
        <?=getBallStyle2($listText, $ver)?>
    </ul>
</div>
