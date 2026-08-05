<?php
include_once('_common.php');
include_once(G5_LADMIN_PATH.'/head.php');

$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$rows = 50;
$total_count = 0;
$total_page = 1;
$result = false;

$tableCheck = sql_fetch(
    "SHOW TABLES LIKE 'l_lucky_custom'",
    false
);

$tableExists = is_array($tableCheck) && count($tableCheck) > 0;

if ($tableExists) {
    $countRow = sql_fetch(
        'select count(*) as cnt from l_lucky_custom',
        false
    );

    $total_count = isset($countRow['cnt'])
        ? (int) $countRow['cnt']
        : 0;

    $total_page = max(1, (int) ceil($total_count / $rows));
    $from_record = ($page - 1) * $rows;

    $result = sql_query(
        "select lc_id,
                turn,
                num1,
                num2,
                num3,
                num4,
                num5,
                num6,
                num7,
                lc_datetime
           from l_lucky_custom
          order by turn desc, lc_datetime desc
          limit {$from_record}, {$rows}",
        false
    );
}
?>

<?php if (!$tableExists) { ?>
<div class="alert alert-danger">
    당첨번호 테이블이 없습니다.
    <code>docs/database/005_lucky_custom.sql</code>을 적용해주세요.
</div>
<?php } ?>

<div class="card card-default">
    <div class="card-body">
        <form
            id="frm"
            name="frm"
            action="lucky.custom.save.php"
            method="post"
            autocomplete="off"
            onSubmit="return fnSave()"
        >
            <div class="row">
                <div class="col-md-1">
                    <input type="number" class="form-control" name="turn" min="1" placeholder="회차" required>
                </div>

                <?php for ($index = 1; $index <= 6; $index++) { ?>
                <div class="col-md-1">
                    <input
                        type="number"
                        class="form-control"
                        name="num<?=$index?>"
                        min="1"
                        max="45"
                        placeholder="<?=$index?>번호"
                        required
                    >
                </div>
                <?php } ?>

                <div class="col-md-1">
                    <input
                        type="number"
                        class="form-control"
                        name="num7"
                        min="1"
                        max="45"
                        placeholder="보너스번호"
                        required
                    >
                </div>

                <div class="col-md-1">
                    <button
                        class="btn btn-block btn-primary"
                        <?=$tableExists ? '' : 'disabled'?>
                    >
                        저장
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    번호 등록 시 관련 회차 테이블이 있는 경우 당첨결과도 반영됩니다.
                </h3>
            </div>

            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                    <tr>
                        <th>NO</th>
                        <th>회차</th>
                        <th>당첨번호</th>
                        <th>보너스번호</th>
                        <th>생성일</th>
                        <th>삭제</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php
                    $index = 0;

                    if ($result) {
                        while ($row = sql_fetch_array($result)) {
                            $numberText = implode(',', array(
                                $row['num1'],
                                $row['num2'],
                                $row['num3'],
                                $row['num4'],
                                $row['num5'],
                                $row['num6'],
                            ));
                    ?>
                    <tr>
                        <td><?=$total_count - (($page - 1) * $rows) - $index?></td>
                        <td><?=number_format((int) $row['turn'])?></td>
                        <td><?=getBallStyle2($numberText, 1)?></td>
                        <td><?=getBallStyle2((string) $row['num7'], 1)?></td>
                        <td><?=htmlspecialchars((string) $row['lc_datetime'], ENT_QUOTES, 'UTF-8')?></td>
                        <td>
                            <button
                                class="btn btn-block btn-danger"
                                type="button"
                                onClick="fnProcDel(
                                    'l_lucky_custom',
                                    'lc_id',
                                    '<?=htmlspecialchars((string) $row['lc_id'], ENT_QUOTES, 'UTF-8')?>'
                                )"
                            >
                                삭제
                            </button>
                        </td>
                    </tr>
                    <?php
                            $index++;
                        }
                    }

                    if ($total_count < 1) {
                    ?>
                    <tr>
                        <td colspan="6">내역이 없습니다.</td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>

                <?php
                echo get_paging(
                    G5_IS_MOBILE
                        ? $config['cf_mobile_pages']
                        : $config['cf_write_pages'],
                    $page,
                    $total_page,
                    '?page='
                );
                ?>
            </div>
        </div>
    </div>
</div>

<script>
function fnSave() {
    return true;
}
</script>

<?php
include_once(G5_LADMIN_PATH.'/tail.php');
?>
