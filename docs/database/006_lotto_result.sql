CREATE TABLE IF NOT EXISTS `g5_lotto_result` (
    `draw_no` int unsigned NOT NULL,
    `draw_date` date NOT NULL,
    `num_1` tinyint unsigned NOT NULL,
    `num_2` tinyint unsigned NOT NULL,
    `num_3` tinyint unsigned NOT NULL,
    `num_4` tinyint unsigned NOT NULL,
    `num_5` tinyint unsigned NOT NULL,
    `num_6` tinyint unsigned NOT NULL,
    `bonus_num` tinyint unsigned NOT NULL,
    `rank1_winners` int unsigned NOT NULL DEFAULT 0,
    `rank1_amount` bigint unsigned NOT NULL DEFAULT 0,
    `rank2_winners` int unsigned NOT NULL DEFAULT 0,
    `rank2_amount` bigint unsigned NOT NULL DEFAULT 0,
    `rank3_winners` int unsigned NOT NULL DEFAULT 0,
    `rank3_amount` bigint unsigned NOT NULL DEFAULT 0,
    `rank4_winners` int unsigned NOT NULL DEFAULT 0,
    `rank4_amount` bigint unsigned NOT NULL DEFAULT 0,
    `rank5_winners` int unsigned NOT NULL DEFAULT 0,
    `rank5_amount` bigint unsigned NOT NULL DEFAULT 0,
    `source_url` varchar(255) NOT NULL DEFAULT '',
    `fetched_at` datetime NOT NULL,
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`draw_no`),
    KEY `idx_draw_date` (`draw_date`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
