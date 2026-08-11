-- Lotto filter settings and admin menu
-- Created for the new lotto filter engine.

CREATE TABLE IF NOT EXISTS `l_filter_setting` (
    `lfs_id` int unsigned NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(100) NOT NULL,
    `setting_value` varchar(255) NOT NULL DEFAULT '',
    `updated_by` varchar(20) NOT NULL DEFAULT '',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`lfs_id`),
    UNIQUE KEY `uq_filter_setting_key` (`setting_key`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

INSERT INTO `l_filter_setting`
    (`setting_key`, `setting_value`, `updated_by`)
VALUES
    ('sum_min', '100', ''),
    ('sum_max', '180', '')
ON DUPLICATE KEY UPDATE
    `setting_key` = VALUES(`setting_key`);

INSERT INTO `l_menu` (
    `lm_cate1`,
    `lm_cate2`,
    `lm_name`,
    `lm_url`,
    `lm_php_name`,
    `lm_level`,
    `lm_order`,
    `lm_use`
)
SELECT
    200,
    30,
    '필터결과',
    '/lpadmin/lucky/filter.result.php',
    'filter.result.php',
    '|5||6||7||9|',
    230,
    1
WHERE NOT EXISTS (
    SELECT 1
      FROM `l_menu`
     WHERE `lm_url` = '/lpadmin/lucky/filter.result.php'
);
