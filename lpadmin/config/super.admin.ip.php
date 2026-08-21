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
    || $loginLevel < LOTTO_ROLE_SUPER_ADMIN
) {
    alert(
        '최고관리자만 접근할 수 있습니다.',
        G5_LADMIN_URL
    );
    exit;
}

$deleteToken = lottoConfigTokenCreate();

$result = sql_query(
    "select
        lsail_id,
        mb_id,
        ip_address,
        first_access_at,
        last_access_at,
        access_count
     from l_super_admin_ip_log
     order by last_access_at desc, lsail_id desc",
    false
);

$ipRows = array();

if ($result) {
    while ($row = sql_fetch_array($result)) {
        $ipRows[] = $row;
    }
}

include_once(G5_LADMIN_PATH."/head.php");

function superAdminIpHtml($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES
    );
}
?>

<div class="row">
    <div class="col-12">
        <div class="card card-primary">

            <div class="card-header">
                <h3 class="card-title">
                    최고관리자 접속 IP
                </h3>
            </div>

            <div class="card-body">
                <p class="text-muted mb-0">
                    최고관리자가 관리자페이지에 로그인한 IP 기록입니다.
                    기록을 삭제해도 해당 IP가 차단되지는 않습니다.
                </p>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">

                    <table class="table table-bordered table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width:70px;">번호</th>
                                <th>접속 IP</th>
                                <th style="width:150px;">계정</th>
                                <th style="width:180px;">최초 접속</th>
                                <th style="width:180px;">최근 접속</th>
                                <th style="width:100px;">접속 횟수</th>
                                <th style="width:90px;">관리</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php if (count($ipRows) === 0) { ?>

                            <tr>
                                <td
                                    colspan="7"
                                    class="text-center text-muted py-4"
                                >
                                    기록된 최고관리자 접속 IP가 없습니다.
                                </td>
                            </tr>

                        <?php } else { ?>

                            <?php foreach ($ipRows as $row) { ?>

                                <tr>
                                    <td>
                                        <?=number_format((int) $row['lsail_id'])?>
                                    </td>

                                    <td>
                                        <?=superAdminIpHtml($row['ip_address'])?>
                                    </td>

                                    <td>
                                        <?=superAdminIpHtml($row['mb_id'])?>
                                    </td>

                                    <td>
                                        <?=superAdminIpHtml($row['first_access_at'])?>
                                    </td>

                                    <td>
                                        <?=superAdminIpHtml($row['last_access_at'])?>
                                    </td>

                                    <td>
                                        <?=number_format((int) $row['access_count'])?>
                                    </td>

                                    <td>
                                        <form
                                            method="post"
                                            action="super.admin.ip.delete.php"
                                            class="d-inline"
                                            onsubmit="return confirm('이 접속 IP 기록을 삭제하시겠습니까?');"
                                        >
                                            <input
                                                type="hidden"
                                                name="lsail_id"
                                                value="<?=(int) $row['lsail_id']?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="token"
                                                value="<?=superAdminIpHtml($deleteToken)?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-danger"
                                            >
                                                삭제
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                            <?php } ?>

                        <?php } ?>

                        </tbody>
                    </table>

                </div>
            </div>

        </div>
    </div>
</div>

<?php
include_once(G5_LADMIN_PATH."/tail.php");
?>
