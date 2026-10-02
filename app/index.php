<?php

include_once __DIR__ . '/_common.php';
include_once G5_PATH . '/include/lotto_app_access.lib.php';

lottoAppRequirePaidMember();

$memberType = isset($member['mb_type'])
    ? trim((string) $member['mb_type'])
    : '';
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#ffffff">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="LottoGPT">
<link rel="manifest" href="/app/manifest.webmanifest">
<link rel="apple-touch-icon" href="/app/icons/apple-touch-icon.png">
<title>LottoGPT 회원앱</title>
</head>
<body>
<main style="max-width:520px;margin:40px auto;padding:24px;font-family:sans-serif;">
    <h1>LottoGPT 회원앱</h1>

    <p>
        유료회원 접근 확인이 완료되었습니다.
    </p>

    <section style="margin-top:24px;padding:20px;border:1px solid #ddd;border-radius:12px;">
        <strong>현재 회원등급</strong>
        <p>
            <?=htmlspecialchars($memberType, ENT_QUOTES, 'UTF-8')?>
        </p>
    </section>

    <section style="margin-top:20px;padding:20px;border:1px solid #ddd;border-radius:12px;">
        <strong>로또 당첨결과</strong>
        <p>최근 당첨번호와 회차별 당첨결과를 확인할 수 있습니다.</p>
        <p>
            <a href="<?=htmlspecialchars(G5_URL . '/app/results.php', ENT_QUOTES, 'UTF-8')?>">
                당첨결과 보기
            </a>
        </p>
    </section>

    <section style="margin-top:20px;padding:20px;border:1px solid #ddd;border-radius:12px;">
        <strong>로또 데이터 통계</strong>
        <p>저장된 당첨번호를 기준으로 번호 출현 통계를 확인할 수 있습니다.</p>
        <p>
            <a href="<?=htmlspecialchars(G5_URL . '/app/stats.php', ENT_QUOTES, 'UTF-8')?>">
                로또 통계 보기
            </a>
        </p>
    </section>

    <section style="margin-top:20px;padding:20px;border:1px solid #ddd;border-radius:12px;">
        <strong>내 조합 · 내 당첨결과</strong>
        <p>회원에게 제공된 회차별 조합과 당첨결과를 확인할 수 있습니다.</p>
        <p>
            <a href="<?=htmlspecialchars(G5_URL . '/app/my_lotto.php', ENT_QUOTES, 'UTF-8')?>">
                내 조합 확인하기
            </a>
        </p>
    </section>

    <section
        id="push-notification-section"
        style="margin-top:20px;padding:20px;border:1px solid #ddd;border-radius:12px;"
    >
        <strong>앱 알림</strong>
        <p id="push-status">알림 상태를 확인하고 있습니다.</p>
        <p>
            <button type="button" id="push-enable" disabled>
                알림 받기
            </button>
            <button type="button" id="push-disable" disabled hidden>
                알림 끄기
            </button>
        </p>
    </section>

    <section style="margin-top:20px;padding:20px;border:1px solid #ddd;border-radius:12px;">
        <strong>앱 기능 준비 중</strong>
        <p>
            앱 알림 발송 기능을 순서대로 연결하고 있습니다.
        </p>
    </section>
</main>

<script>
(function () {
    'use strict';

    var apiUrl = '/api/lotto/push_subscription.php';
    var statusElement = document.getElementById('push-status');
    var enableButton = document.getElementById('push-enable');
    var disableButton = document.getElementById('push-disable');

    function setStatus(message) {
        statusElement.textContent = message;
    }

    function setButtons(isSubscribed) {
        enableButton.hidden = isSubscribed;
        enableButton.disabled = isSubscribed;

        disableButton.hidden = !isSubscribed;
        disableButton.disabled = !isSubscribed;
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
        return navigator.serviceWorker.register(
            '/app/service-worker.js',
            {scope: '/app/'}
        );
    }

    async function refreshPushStatus() {
        if (
            !('serviceWorker' in navigator)
            || !('PushManager' in window)
            || !('Notification' in window)
        ) {
            setStatus('이 브라우저에서는 앱 알림을 사용할 수 없습니다.');
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
                throw new Error('알림 권한이 허용되지 않았습니다.');
            }

            var subscription =
                await registration.pushManager.getSubscription();

            if (!subscription) {
                subscription = await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey:
                        urlBase64ToUint8Array(config.public_key)
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

    disableButton.addEventListener('click', async function () {
        disableButton.disabled = true;
        setStatus('앱 알림을 해제하고 있습니다.');

        try {
            var config = await getApiConfig();
            var registration = await getRegistration();
            var subscription =
                await registration.pushManager.getSubscription();

            if (!subscription) {
                setStatus('앱 알림이 이미 꺼져 있습니다.');
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
    });

    window.addEventListener('load', refreshPushStatus);
})();
</script>
</body>
</html>
