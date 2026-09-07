CREATE TABLE IF NOT EXISTS `l_lotto_share_link` (
    `lsl_id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(20) NOT NULL,
    `token_value` char(32) NOT NULL,
    `token_hash` char(64) NOT NULL,
    `created_by` varchar(20) NOT NULL DEFAULT '',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`lsl_id`),
    UNIQUE KEY `uq_lotto_share_link_member` (`mb_id`),
    UNIQUE KEY `uq_lotto_share_link_token` (`token_hash`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
