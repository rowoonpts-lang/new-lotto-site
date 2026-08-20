<?php
include_once("_common.php");

$login_level = isset($member['mb_level']) ? (int) $member['mb_level'] : 0;

if ($login_level < LOTTO_ROLE_ADMIN) {
    alert('관리자 이상만 접근할 수 있습니다.', G5_LADMIN_URL);
    exit;
}

$content = sql_fetch("
    select co_id, co_subject, co_content
      from g5_content
     where co_id = 'privacy'
     limit 1
", false);

if (empty($content['co_id'])) {
    alert('개인정보처리방침 정보를 찾을 수 없습니다.', G5_LADMIN_URL);
    exit;
}

$token = lottoConfigTokenCreate();

include_once(G5_LADMIN_PATH."/head.php");
?>

<div class="row">
    <div class="col-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">개인정보처리방침</h3>
            </div>

            <form method="post" action="content.policy.update.php">
                <input type="hidden" name="token" value="<?=htmlspecialchars((string) $token, ENT_QUOTES)?>">
                <input type="hidden" name="co_id" value="privacy">

                <div class="card-body">
                    <div class="form-group">
                        <label for="co_content">
                            <?=htmlspecialchars((string) $content['co_subject'], ENT_QUOTES)?>
                        </label>

                        <textarea
                            class="form-control"
                            id="co_content"
                            name="co_content"
                            rows="30"
                            required
                        ><?=htmlspecialchars((string) $content['co_content'], ENT_QUOTES)?></textarea>

                        <small class="form-text text-muted">
                            입력한 내용은 홈페이지 개인정보처리방침 페이지에 표시됩니다.
                        </small>
                    </div>
                </div>

                <div class="card-footer text-right">
                    <a
                        href="<?=G5_BBS_URL?>/content.php?co_id=privacy"
                        class="btn btn-secondary"
                        target="_blank"
                        rel="noopener noreferrer"
                    >홈페이지 확인</a>

                    <button type="submit" class="btn btn-primary">저장</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
include_once(G5_LADMIN_PATH."/tail.php");
?>
