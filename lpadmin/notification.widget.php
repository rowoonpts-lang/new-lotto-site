<?php
$notification_widget_mb_id = isset($member['mb_id']) ? trim((string) $member['mb_id']) : '';
if ($notification_widget_mb_id === '') {
    return;
}
?>
<style>
#lotto-notification-stack {
    position: fixed;
    top: 70px;
    right: 18px;
    width: 360px;
    max-width: calc(100vw - 36px);
    max-height: calc(100vh - 90px);
    overflow-y: auto;
    z-index: 2000;
    pointer-events: none;
}
.lotto-notification-card {
    display: block;
    margin-bottom: 10px;
    padding: 14px 16px;
    border: 1px solid rgba(0, 0, 0, .12);
    border-left: 4px solid #ffc107;
    border-radius: 4px;
    background: #fff;
    color: #212529;
    box-shadow: 0 2px 8px rgba(0, 0, 0, .18);
    text-decoration: none !important;
    pointer-events: auto;
}
.lotto-notification-card:hover {
    background: #f8f9fa;
    color: #212529;
}
.lotto-notification-title {
    margin-bottom: 4px;
    font-weight: 700;
}
.lotto-notification-message {
    font-size: 13px;
    line-height: 1.45;
    white-space: normal;
}
.lotto-notification-time {
    margin-top: 5px;
    font-size: 11px;
    color: #6c757d;
}
@media (max-width: 576px) {
    #lotto-notification-stack {
        top: 60px;
        right: 8px;
        width: calc(100vw - 16px);
        max-width: none;
    }
}
</style>
<script>
(function($){
    'use strict';

    var notificationPollUrl = <?=json_encode(G5_LADMIN_URL.'/notification.poll.php', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)?>;
    var notificationTimer = null;
    var notificationLoading = false;

    function escapeHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function ensureNotificationStack() {
        var $stack = $('#lotto-notification-stack');
        if ($stack.length < 1) {
            $('body').append('<div id="lotto-notification-stack" aria-live="polite"></div>');
            $stack = $('#lotto-notification-stack');
        }
        return $stack;
    }

    function renderNotifications(items) {
        var $stack = ensureNotificationStack();
        var html = '';

        $.each(items || [], function(index, item){
            var title = escapeHtml(item.title || '알림');
            var message = escapeHtml(item.message || '');
            var createdAt = escapeHtml(item.created_at || '');
            var openUrl = item.open_url || '';

            if (openUrl) {
                html += '<a class="lotto-notification-card" href="' + escapeHtml(openUrl) + '">';
            } else {
                html += '<div class="lotto-notification-card">';
            }
            html += '<div class="lotto-notification-title">' + title + '</div>';
            html += '<div class="lotto-notification-message">' + message + '</div>';
            if (createdAt) {
                html += '<div class="lotto-notification-time">' + createdAt + '</div>';
            }
            html += openUrl ? '</a>' : '</div>';
        });

        $stack.html(html);
    }

    function loadNotifications() {
        if (notificationLoading) {
            return;
        }

        notificationLoading = true;
        $.ajax({
            url: notificationPollUrl,
            type: 'GET',
            dataType: 'json',
            cache: false
        }).done(function(response){
            if (response && response.ok === true) {
                renderNotifications(response.notifications || []);
            }
        }).always(function(){
            notificationLoading = false;
        });
    }

    $(function(){
        ensureNotificationStack();
        loadNotifications();
        notificationTimer = window.setInterval(loadNotifications, 5000);
    });

    $(window).on('beforeunload', function(){
        if (notificationTimer) {
            window.clearInterval(notificationTimer);
        }
    });
})(jQuery);
</script>
