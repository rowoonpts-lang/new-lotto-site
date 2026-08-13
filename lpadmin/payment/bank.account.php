<?php
include_once("_common.php");

$login_level = isset($member['mb_level']) ? (int) $member['mb_level'] : 0;
if ($login_level < LOTTO_ROLE_ADMIN) {
    alert('관리자 이상만 접근할 수 있습니다.', G5_LADMIN_URL);
    exit;
}

include_once(G5_LADMIN_PATH."/head.php");

$accounts = array();
$result = sql_query(
    "select *
       from l_payment_bank_account
      order by sort_order asc, lpba_id asc"
);
while ($account = sql_fetch_array($result)) {
    $accounts[] = $account;
}
?>

<div class="row">
    <div class="col-lg-5">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">입금계좌 등록</h3>
            </div>
            <form method="post" action="bank.account.update.php" autocomplete="off">
                <input type="hidden" name="mode" value="create">
                <div class="card-body">
                    <div class="form-group">
                        <label for="bank_name">은행명</label>
                        <input type="text" class="form-control" id="bank_name" name="bank_name" maxlength="100" required>
                    </div>
                    <div class="form-group">
                        <label for="account_number">계좌번호</label>
                        <input type="text" class="form-control" id="account_number" name="account_number" maxlength="100" required>
                    </div>
                    <div class="form-group">
                        <label for="account_holder">예금주</label>
                        <input type="text" class="form-control" id="account_holder" name="account_holder" maxlength="100" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="sort_order">표시순서</label>
                                <input type="number" class="form-control" id="sort_order" name="sort_order" min="0" value="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_active">사용여부</label>
                                <select class="form-control" id="is_active" name="is_active">
                                    <option value="1">사용</option>
                                    <option value="0">사용중지</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">계좌 등록</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">등록된 입금계좌</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                    <tr>
                        <th>순서</th>
                        <th>은행명</th>
                        <th>계좌번호</th>
                        <th>예금주</th>
                        <th>상태</th>
                        <th>수정</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (count($accounts) < 1) { ?>
                    <tr>
                        <td colspan="6" class="text-center">등록된 입금계좌가 없습니다.</td>
                    </tr>
                    <?php } ?>
                    <?php foreach ($accounts as $account) { ?>
                    <tr>
                        <form method="post" action="bank.account.update.php" autocomplete="off">
                            <input type="hidden" name="mode" value="update">
                            <input type="hidden" name="lpba_id" value="<?=htmlspecialchars((string) $account['lpba_id'], ENT_QUOTES)?>">
                            <td style="width:90px">
                                <input type="number" class="form-control" name="sort_order" min="0" value="<?=htmlspecialchars((string) $account['sort_order'], ENT_QUOTES)?>">
                            </td>
                            <td><input type="text" class="form-control" name="bank_name" maxlength="100" value="<?=htmlspecialchars((string) $account['bank_name'], ENT_QUOTES)?>" required></td>
                            <td><input type="text" class="form-control" name="account_number" maxlength="100" value="<?=htmlspecialchars((string) $account['account_number'], ENT_QUOTES)?>" required></td>
                            <td><input type="text" class="form-control" name="account_holder" maxlength="100" value="<?=htmlspecialchars((string) $account['account_holder'], ENT_QUOTES)?>" required></td>
                            <td style="width:120px">
                                <select class="form-control" name="is_active">
                                    <option value="1" <?=((int) $account['is_active'] === 1) ? 'selected' : ''?>>사용</option>
                                    <option value="0" <?=((int) $account['is_active'] === 0) ? 'selected' : ''?>>사용중지</option>
                                </select>
                            </td>
                            <td style="width:90px"><button type="submit" class="btn btn-sm btn-primary">저장</button></td>
                        </form>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
include_once(G5_LADMIN_PATH."/tail.php");
?>
