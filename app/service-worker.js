self.addEventListener('install', function () {
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil(self.clients.claim());
});

var pushSubscriptionApiUrl =
    '/api/lotto/push_subscription.php';

function pushUrlBase64ToUint8Array(value) {
    var padding =
        '='.repeat((4 - value.length % 4) % 4);

    var base64 = (value + padding)
        .replace(/-/g, '+')
        .replace(/_/g, '/');

    var rawData = self.atob(base64);
    var output = new Uint8Array(rawData.length);

    for (var i = 0; i < rawData.length; i++) {
        output[i] = rawData.charCodeAt(i);
    }

    return output;
}

async function getPushSubscriptionConfig() {
    var response = await fetch(
        pushSubscriptionApiUrl,
        {
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                'Accept': 'application/json'
            }
        }
    );

    var result = await response.json();

    if (
        !response.ok
        || !result.success
        || !result.public_key
        || !result.token
    ) {
        throw new Error(
            result.message
            || '푸시 알림 설정을 불러오지 못했습니다.'
        );
    }

    return result;
}

async function savePushSubscription(
    action,
    subscription,
    token
) {
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

    var response = await fetch(
        pushSubscriptionApiUrl,
        {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        }
    );

    var result = await response.json();

    if (!response.ok || !result.success) {
        throw new Error(
            result.message
            || '푸시 알림 정보를 저장하지 못했습니다.'
        );
    }

    return result;
}

/*
 * 브라우저 또는 Push 서비스가 기존 구독을 교체하거나
 * 무효화한 경우 새 구독을 만들고 서버에 다시 등록한다.
 *
 * 이 이벤트를 지원하지 않는 브라우저는 앱 실행 시
 * index.php의 구독 재동기화가 보조 역할을 한다.
 */
self.addEventListener(
    'pushsubscriptionchange',
    function (event) {
        event.waitUntil(
            (async function () {
                var config =
                    await getPushSubscriptionConfig();

                var options = null;

                if (
                    event.oldSubscription
                    && event.oldSubscription.options
                ) {
                    options =
                        event.oldSubscription.options;
                } else {
                    options = {
                        userVisibleOnly: true,
                        applicationServerKey:
                            pushUrlBase64ToUint8Array(
                                config.public_key
                            )
                    };
                }

                var newSubscription =
                    await self.registration.pushManager
                        .subscribe(options);

                await savePushSubscription(
                    'subscribe',
                    newSubscription,
                    config.token
                );

                if (
                    event.oldSubscription
                    && event.oldSubscription.endpoint
                    && event.oldSubscription.endpoint
                        !== newSubscription.endpoint
                ) {
                    try {
                        await savePushSubscription(
                            'unsubscribe',
                            event.oldSubscription,
                            config.token
                        );
                    } catch (cleanupError) {
                        console.error(
                            '이전 Push 구독 정리에 실패했습니다.',
                            cleanupError
                        );
                    }
                }
            })().catch(function (error) {
                console.error(
                    'Push 구독 자동복구에 실패했습니다.',
                    error
                );
            })
        );
    }
);

self.addEventListener('fetch', function (event) {
    if (
        event.request.method !== 'GET'
        || event.request.mode !== 'navigate'
    ) {
        return;
    }

    event.respondWith(fetch(event.request));
});

self.addEventListener('push', function (event) {
    var targetUrl = '/app/';
    var title = 'LottoGPT';
    var body =
        '새로운 알림이 도착했습니다. 앱에서 확인해 주세요.';

    if (event.data) {
        try {
            var payload = event.data.json();

            if (
                payload
                && typeof payload.title === 'string'
                && payload.title.trim() !== ''
            ) {
                title = payload.title.trim();
            }

            if (
                payload
                && typeof payload.body === 'string'
                && payload.body.trim() !== ''
            ) {
                body = payload.body.trim();
            }

            if (
                payload
                && typeof payload.url === 'string'
                && payload.url !== ''
            ) {
                var candidateUrl = new URL(
                    payload.url,
                    self.location.origin
                );

                if (candidateUrl.origin === self.location.origin) {
                    targetUrl =
                        candidateUrl.pathname
                        + candidateUrl.search
                        + candidateUrl.hash;
                }
            }
        } catch (error) {
            targetUrl = '/app/';
        }
    }

    event.waitUntil(
        self.registration.showNotification(
            title,
            {
                body: body,
                icon: '/app/icons/icon-192.png',
                badge: '/app/icons/icon-192.png',
                data: {
                    url: targetUrl
                }
            }
        )
    );
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    var targetUrl = '/app/';

    if (
        event.notification.data
        && typeof event.notification.data.url === 'string'
    ) {
        targetUrl = event.notification.data.url;
    }

    var absoluteUrl = new URL(
        targetUrl,
        self.location.origin
    ).href;

    event.waitUntil(
        self.clients.matchAll({
            type: 'window',
            includeUncontrolled: true
        }).then(function (clientList) {
            for (var i = 0; i < clientList.length; i++) {
                var client = clientList[i];

                if (
                    client.url.indexOf(self.location.origin) === 0
                    && 'navigate' in client
                ) {
                    return client.navigate(absoluteUrl)
                        .then(function () {
                            return client.focus();
                        });
                }
            }

            if (self.clients.openWindow) {
                return self.clients.openWindow(absoluteUrl);
            }
        })
    );
});
