CREATE TABLE IF NOT EXISTS `l_lucky_custom` (
    `lc_id` int unsigned NOT NULL AUTO_INCREMENT,
    `turn` int unsigned NOT NULL,
    `num1` tinyint unsigned NOT NULL,
    `num2` tinyint unsigned NOT NULL,
    `num3` tinyint unsigned NOT NULL,
    `num4` tinyint unsigned NOT NULL,
    `num5` tinyint unsigned NOT NULL,
    `num6` tinyint unsigned NOT NULL,
    `num7` tinyint unsigned NOT NULL,
    `lc_datetime` datetime NOT NULL,
    PRIMARY KEY (`lc_id`),
    UNIQUE KEY `uq_lucky_custom_turn` (`turn`),
    KEY `idx_lucky_custom_datetime` (`lc_datetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
