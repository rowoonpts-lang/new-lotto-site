self.addEventListener('install', function () {
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil(self.clients.claim());
});

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
