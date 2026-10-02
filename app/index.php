<?php

include_once __DIR__ . '/_common.php';
include_once G5_PATH . '/include/lotto_app_access.lib.php';

lottoAppRequirePaidMember();

$memberId = isset($member['mb_id'])
    ? trim((string) $member['mb_id'])
    : '';

$memberName = isset($member['mb_name'])
    ? trim((string) $member['mb_name'])
    : '';

$memberType = isset($member['mb_type'])
    ? trim((string) $member['mb_type'])
    : '';

$safeMemberId = sql_real_escape_string($memberId);

$draws = array();

$drawResult = sql_query(
    "select distinct draw_no
       from l_member_combination
      where mb_id = '{$safeMemberId}'
      order by draw_no desc",
    false
);

if ($drawResult) {
    while ($drawRow = sql_fetch_array($drawResult)) {
        $drawNo = (int) $drawRow['draw_no'];

        if ($drawNo > 0) {
            $draws[] = $drawNo;
        }
    }
}

$selectedDraw = isset($_GET['turn'])
    ? (int) $_GET['turn']
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
    $combinationResult = sql_query(
        "select num1,
                num2,
                num3,
                num4,
                num5,
                num6
           from l_member_combination
          where mb_id = '{$safeMemberId}'
            and draw_no = '{$selectedDraw}'
          order by distribution_seq asc, lmc_id asc",
        false
    );

    if ($combinationResult) {
        while ($row = sql_fetch_array($combinationResult)) {
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
}

function lottoAppHomeHtml($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        'UTF-8'
    );
}

function lottoAppHomeBallClass($number)
{
    $number = (int) $number;

    if ($number <= 10) {
        return 'yellow';
    }

    if ($number <= 20) {
        return 'blue';
    }

    if ($number <= 30) {
        return 'red';
    }

    if ($number <= 40) {
        return 'gray';
    }

    return 'green';
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
<meta name="theme-color" content="#ffffff">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="LottoGPT">
<link rel="manifest" href="/app/manifest.webmanifest">
<link rel="apple-touch-icon" href="/app/icons/apple-touch-icon.png">
<title>LottoGPT</title>

<style>
* {
    box-sizing: border-box;
}

html,
body {
    margin: 0;
    min-height: 100%;
    background: #f4f6f9;
    color: #20252b;
    font-family: Arial, "Apple SD Gothic Neo", sans-serif;
}

.app-header {
    position: sticky;
    top: 0;
    z-index: 100;
    background: rgba(255, 255, 255, .97);
    border-bottom: 1px solid #e4e7eb;
}

.app-header-inner {
    max-width: 640px;
    margin: 0 auto;
}

.app-brand {
    padding: 14px 16px 10px;
    font-size: 21px;
    font-weight: 800;
}

.app-nav {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    border-top: 1px solid #f0f1f3;
}

.app-nav a {
    display: flex;
    min-height: 46px;
    align-items: center;
    justify-content: center;
    padding: 8px 4px;
    color: #60666d;
    font-size: 13px;
    font-weight: 700;
    text-align: center;
    text-decoration: none;
}

.app-nav a.active {
    color: #111827;
    border-bottom: 3px solid #111827;
}

.app-shell {
    max-width: 640px;
    margin: 0 auto;
    padding: 18px 14px 48px;
}

.app-card {
    margin-bottom: 14px;
    padding: 20px 16px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, .035);
}

.install-card {
    border: 2px solid #111827;
}

.install-title {
    margin: 0 0 8px;
    font-size: 19px;
}

.install-message {
    margin: 0 0 14px;
    color: #60666d;
    line-height: 1.6;
}

.install-button {
    width: 100%;
    min-height: 50px;
    border: 0;
    border-radius: 12px;
    background: #111827;
    color: #fff;
    font-size: 16px;
    font-weight: 800;
}

.install-button[disabled] {
    opacity: .45;
}

.ios-install-guide {
    margin-top: 14px;
    padding: 14px;
    background: #f8f9fb;
    border-radius: 10px;
    color: #40464d;
    line-height: 1.7;
}

.app-title {
    margin: 0 0 7px;
    font-size: 25px;
}

.member-info {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}

.draw-select {
    width: 100%;
    height: 48px;
    margin-top: 18px;
    padding: 0 12px;
    border: 1px solid #d7dbe0;
    border-radius: 10px;
    background: #fff;
    font-size: 16px;
}

.combo {
    margin-top: 12px;
    padding: 14px 10px;
    background: #f8f9fb;
    border-radius: 12px;
}

.combo-head {
    margin-bottom: 10px;
    color: #60666d;
    font-size: 13px;
    font-weight: 700;
}

.ball-row {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
}

.ball {
    display: inline-flex;
    width: 38px;
    height: 38px;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    color: #fff;
    font-size: 14px;
    font-weight: 800;
}

.ball.yellow {
    background: #d6a820;
}

.ball.blue {
    background: #3b82c4;
}

.ball.red {
    background: #dc5454;
}

.ball.gray {
    background: #71717a;
}

.ball.green {
    background: #2f9e68;
}

.empty {
    padding: 28px 0;
    color: #777;
    text-align: center;
}

.push-title {
    margin: 0 0 8px;
    font-size: 18px;
}

.push-status {
    margin: 0 0 14px;
    color: #60666d;
    line-height: 1.5;
}

.push-button {
    width: 100%;
    min-height: 46px;
    border: 0;
    border-radius: 10px;
    background: #111827;
    color: #fff;
    font-size: 15px;
    font-weight: 700;
}

.push-button[disabled] {
    opacity: .55;
}

.push-button.secondary {
    background: #e5e7eb;
    color: #20252b;
}

.push-button + .push-button {
    margin-top: 10px;
}
</style>
</head>

<body>

<header class="app-header">
    <div class="app-header-inner">
        <div class="app-brand">LottoGPT</div>

        <nav class="app-nav" aria-label="회원앱 메뉴">
            <a class="active" href="/app/">
                추천번호
            </a>

            <a href="/app/results.php">
                당첨결과
            </a>

            <a href="/app/stats.php">
                통계
            </a>

            <a href="/app/my_lotto.php">
                내 당첨
            </a>
        </nav>
    </div>
</header>

<main class="app-shell">

    <section
        class="app-card install-card"
        id="app-install-section"
    >
        <h2 class="install-title">
            LottoGPT 앱 설치
        </h2>

        <p
            class="install-message"
            id="app-install-message"
        >
            휴대폰 홈 화면에 LottoGPT 앱 아이콘을 만들 수 있습니다.
        </p>

        <button
            type="button"
            class="install-button"
            id="app-install-button"
            disabled
        >
            홈 화면에 앱 만들기
        </button>

        <div
            class="ios-install-guide"
            id="ios-install-guide"
            hidden
        >
            아이폰에서는 Safari 아래쪽의 공유 버튼을 누른 뒤
            <strong>홈 화면에 추가</strong>를 선택하고
            <strong>추가</strong>를 눌러주세요.
        </div>
    </section>

    <section class="app-card">
        <h1 class="app-title">추천번호</h1>

        <p class="member-info">
            <?=lottoAppHomeHtml($memberName)?>님
            <?php if ($memberType !== '') { ?>
            · <?=lottoAppHomeHtml($memberType)?>
            <?php } ?>
        </p>

        <?php if (!empty($draws)) { ?>

        <form method="get">
            <select
                class="draw-select"
                name="turn"
                onchange="this.form.submit();"
                aria-label="조회 회차"
            >
                <?php foreach ($draws as $drawNo) { ?>
                <option
                    value="<?=(int) $drawNo?>"
                    <?=$selectedDraw === (int) $drawNo
                        ? 'selected'
                        : ''?>
                >
                    <?=(int) $drawNo?>회차
                </option>
                <?php } ?>
            </select>
        </form>

        <?php foreach ($combinations as $index => $numbers) { ?>

        <article class="combo">
            <div class="combo-head">
                <?=number_format($index + 1)?>번째 조합
            </div>

            <div class="ball-row">
                <?php foreach ($numbers as $number) { ?>
                <span
                    class="ball <?=lottoAppHomeBallClass($number)?>"
                >
                    <?=str_pad(
                        (string) $number,
                        2,
                        '0',
                        STR_PAD_LEFT
                    )?>
                </span>
                <?php } ?>
            </div>
        </article>

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
    </section>

    <section
        class="app-card"
        id="push-notification-section"
    >
        <h2 class="push-title">앱 알림</h2>

        <p
            class="push-status"
            id="push-status"
        >
            알림 상태를 확인하고 있습니다.
        </p>

        <button
            type="button"
            class="push-button"
            id="push-enable"
            disabled
        >
            알림 받기
        </button>

        <button
            type="button"
            class="push-button secondary"
            id="push-disable"
            disabled
            hidden
        >
            알림 끄기
        </button>

        <button
            type="button"
            class="push-button secondary"
            id="push-test"
            disabled
            hidden
        >
            테스트 알림 보내기
        </button>
    </section>

</main>

<script>
(function () {
    'use strict';

    var deferredInstallPrompt = null;

    var installSection =
        document.getElementById('app-install-section');

    var installButton =
        document.getElementById('app-install-button');

    var installMessage =
        document.getElementById('app-install-message');

    var iosInstallGuide =
        document.getElementById('ios-install-guide');

    var apiUrl = '/api/lotto/push_subscription.php';
    var testApiUrl = '/api/lotto/push_test.php';
    var statusElement = document.getElementById('push-status');
    var enableButton = document.getElementById('push-enable');
    var disableButton = document.getElementById('push-disable');
    var testButton = document.getElementById('push-test');

    function isStandalone() {
        return window.matchMedia(
            '(display-mode: standalone)'
        ).matches
            || window.navigator.standalone === true;
    }

    function isIos() {
        return /iphone|ipad|ipod/i.test(
            window.navigator.userAgent
        );
    }

    function updateInstallUi() {
        if (isStandalone()) {
            installSection.hidden = true;
            return;
        }

        installSection.hidden = false;

        if (isIos()) {
            installButton.disabled = false;

            installMessage.textContent =
                '아이폰 홈 화면에 LottoGPT 앱 아이콘을 만들 수 있습니다.';

            return;
        }

        if (deferredInstallPrompt) {
            installButton.disabled = false;

            installMessage.textContent =
                '아래 버튼을 누르면 LottoGPT 앱 설치창이 열립니다.';
        } else {
            installButton.disabled = true;

            installMessage.textContent =
                '앱 설치 버튼을 준비하고 있습니다. '
                + '버튼이 활성화되지 않으면 Chrome 메뉴의 '
                + '앱 설치를 이용해주세요.';
        }
    }

    window.addEventListener(
        'beforeinstallprompt',
        function (event) {
            event.preventDefault();
            deferredInstallPrompt = event;
            updateInstallUi();
        }
    );

    window.addEventListener(
        'appinstalled',
        function () {
            deferredInstallPrompt = null;
            installSection.hidden = true;
        }
    );

    installButton.addEventListener(
        'click',
        async function () {
            if (isIos()) {
                iosInstallGuide.hidden = false;

                installMessage.textContent =
                    '아래 안내대로 홈 화면에 추가해주세요.';

                return;
            }

            if (!deferredInstallPrompt) {
                installMessage.textContent =
                    '브라우저 메뉴의 앱 설치 또는 홈 화면에 추가를 이용해주세요.';

                return;
            }

            installButton.disabled = true;

            try {
                await deferredInstallPrompt.prompt();

                var choice =
                    await deferredInstallPrompt.userChoice;

                deferredInstallPrompt = null;

                if (
                    choice
                    && choice.outcome === 'accepted'
                ) {
                    installMessage.textContent =
                        '앱을 설치하고 있습니다.';
                } else {
                    installMessage.textContent =
                        '앱 설치가 취소되었습니다. 다시 시도할 수 있습니다.';
                    updateInstallUi();
                }
            } catch (error) {
                console.error(error);
                deferredInstallPrompt = null;
                installButton.disabled = false;
                installMessage.textContent =
                    '설치창을 열지 못했습니다. '
                    + 'Chrome 메뉴의 앱 설치를 이용해주세요.';
            }
        }
    );

    function setStatus(message) {
        statusElement.textContent = message;
    }

    function setButtons(isSubscribed) {
        enableButton.hidden = isSubscribed;
        enableButton.disabled = isSubscribed;

        disableButton.hidden = !isSubscribed;
        disableButton.disabled = !isSubscribed;

        testButton.hidden = !isSubscribed;
        testButton.disabled = !isSubscribed;
    }

    function urlBase64ToUint8Array(value) {
        var padding = '='.repeat((4 - value.length % 4) % 4);
        var base64 = (value + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        var rawData = window.atob(base64);
        var output = new Uint8Array(rawData.length);

        for (var i = 0; i < rawData.length; i++) {
            output[i] = rawData.charCodeAt(i);
        }

        return output;
    }

    async function getApiConfig() {
        var response = await fetch(apiUrl, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json'
            }
        });

        var data = await response.json();

        if (
            !response.ok
            || !data.success
            || !data.public_key
            || !data.token
        ) {
            throw new Error(
                data.message || '앱 알림 설정을 불러오지 못했습니다.'
            );
        }

        return data;
    }

    async function saveSubscription(action, subscription, token) {
        var data = subscription.toJSON();

        var payload = {
            action: action,
            token: token,
            endpoint: subscription.endpoint
        };

        if (action === 'subscribe') {
            payload.keys = data.keys || {};
            payload.expirationTime =
                typeof data.expirationTime === 'undefined'
                    ? null
                    : data.expirationTime;
        }

        var response = await fetch(apiUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        var result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(
                result.message || '앱 알림 정보를 저장하지 못했습니다.'
            );
        }
    }

    async function getRegistration() {
        await navigator.serviceWorker.register(
            '/app/service-worker.js',
            {scope: '/app/'}
        );

        return navigator.serviceWorker.ready;
    }

    async function refreshPushStatus() {
        if (
            !('serviceWorker' in navigator)
            || !('PushManager' in window)
            || !('Notification' in window)
        ) {
            setStatus(
                '이 기기에서는 앱 알림을 사용할 수 없습니다.'
            );
            return;
        }

        try {
            var registration = await getRegistration();
            var subscription =
                await registration.pushManager.getSubscription();

            if (subscription) {
                setStatus('현재 앱 알림을 받고 있습니다.');
                setButtons(true);
            } else {
                setStatus('앱 알림이 꺼져 있습니다.');
                setButtons(false);
            }
        } catch (error) {
            console.error(error);
            setStatus('앱 알림 상태를 확인하지 못했습니다.');
        }
    }

    enableButton.addEventListener('click', async function () {
        enableButton.disabled = true;
        setStatus('앱 알림을 설정하고 있습니다.');

        try {
            var config = await getApiConfig();
            var registration = await getRegistration();

            var permission = await Notification.requestPermission();

            if (permission !== 'granted') {
                throw new Error(
                    '알림 권한이 허용되지 않았습니다.'
                );
            }

            var subscription =
                await registration.pushManager.getSubscription();

            if (!subscription) {
                subscription =
                    await registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey:
                            urlBase64ToUint8Array(
                                config.public_key
                            )
                    });
            }

            await saveSubscription(
                'subscribe',
                subscription,
                config.token
            );

            setStatus('앱 알림이 설정되었습니다.');
            setButtons(true);
        } catch (error) {
            console.error(error);

            setStatus(
                error && error.message
                    ? error.message
                    : '앱 알림 설정에 실패했습니다.'
            );

            enableButton.disabled = false;
        }
    });

    testButton.addEventListener(
        'click',
        async function () {
            testButton.disabled = true;
            setStatus('테스트 알림을 보내고 있습니다.');

            try {
                var config = await getApiConfig();

                var response = await fetch(testApiUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        token: config.token
                    })
                });

                var result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(
                        result.message
                        || '테스트 알림을 보내지 못했습니다.'
                    );
                }

                setStatus(
                    result.message
                    || '테스트 알림을 보냈습니다.'
                );
            } catch (error) {
                console.error(error);

                setStatus(
                    error && error.message
                        ? error.message
                        : '테스트 알림을 보내지 못했습니다.'
                );
            } finally {
                testButton.disabled = false;
            }
        }
    );

    disableButton.addEventListener(
        'click',
        async function () {
            disableButton.disabled = true;
            setStatus('앱 알림을 해제하고 있습니다.');

            try {
                var config = await getApiConfig();
                var registration = await getRegistration();

                var subscription =
                    await registration.pushManager
                        .getSubscription();

                if (!subscription) {
                    setStatus(
                        '앱 알림이 이미 꺼져 있습니다.'
                    );
                    setButtons(false);
                    return;
                }

                await saveSubscription(
                    'unsubscribe',
                    subscription,
                    config.token
                );

                await subscription.unsubscribe();

                setStatus('앱 알림이 해제되었습니다.');
                setButtons(false);
            } catch (error) {
                console.error(error);

                setStatus(
                    error && error.message
                        ? error.message
                        : '앱 알림 해제에 실패했습니다.'
                );

                disableButton.disabled = false;
            }
        }
    );

    window.addEventListener(
        'load',
        function () {
            updateInstallUi();
            refreshPushStatus();
        }
    );
})();
</script>

</body>
</html>
