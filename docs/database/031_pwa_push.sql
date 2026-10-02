-- Lotto Platform PWA push notification foundation
-- Push notifications are independent from SMS delivery.
-- A member may have multiple active browser/device subscriptions.

CREATE TABLE IF NOT EXISTS `l_push_subscription` (
    `lps_id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(20) NOT NULL,

    `endpoint_hash` char(64)
        CHARACTER SET ascii
        COLLATE ascii_bin
        NOT NULL,
    `endpoint` text NOT NULL,
    `p256dh_key` varchar(255) NOT NULL,
    `auth_key` varchar(255) NOT NULL,
    `expiration_time` bigint unsigned DEFAULT NULL,

    `user_agent` varchar(255) NOT NULL DEFAULT '',
    `is_active` tinyint(1) NOT NULL DEFAULT 1,

    `last_seen_at` datetime DEFAULT NULL,
    `last_success_at` datetime DEFAULT NULL,
    `last_failure_at` datetime DEFAULT NULL,
    `last_error` varchar(500) NOT NULL DEFAULT '',

    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`lps_id`),
    UNIQUE KEY `uq_push_endpoint_hash` (`endpoint_hash`),
    KEY `idx_push_member_active` (`mb_id`, `is_active`),
    KEY `idx_push_active_seen` (`is_active`, `last_seen_at`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `l_push_history` (
    `lph_id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `lps_id` bigint unsigned NOT NULL,
    `mb_id` varchar(20) NOT NULL DEFAULT '',

    `send_category` varchar(30) NOT NULL DEFAULT '',
    `draw_no` int unsigned DEFAULT NULL,

    `event_hash` char(64)
        CHARACTER SET ascii
        COLLATE ascii_bin
        NOT NULL,

    `target_url` varchar(255) NOT NULL DEFAULT '',
    `push_status` varchar(20) NOT NULL DEFAULT 'pending',

    `http_status` smallint unsigned DEFAULT NULL,
    `result_message` varchar(500) NOT NULL DEFAULT '',

    `queued_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `sent_at` datetime DEFAULT NULL,
    `updated_at` datetime NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (`lph_id`),
    UNIQUE KEY `uq_push_event` (`lps_id`, `event_hash`),
    KEY `idx_push_history_member` (`mb_id`, `queued_at`),
    KEY `idx_push_history_category` (
        `send_category`,
        `draw_no`,
        `push_status`
    )
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
