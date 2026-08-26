CREATE TABLE IF NOT EXISTS `l_sms_config` (
    `lsc_id` tinyint unsigned NOT NULL,
    `sender_phone` varchar(20) NOT NULL DEFAULT '',
    `combination_header` text NOT NULL,
    `combination_footer` text NOT NULL,
    `winner_header` text NOT NULL,
    `winner_footer` text NOT NULL,
    `updated_by` varchar(20) NOT NULL DEFAULT '',
    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`lsc_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `l_sms_config` (
    `lsc_id`,
    `sender_phone`,
    `combination_header`,
    `combination_footer`,
    `winner_header`,
    `winner_footer`
)
SELECT
    1,
    '',
    '',
    '',
    '',
    ''
WHERE NOT EXISTS (
    SELECT 1
    FROM `l_sms_config`
    WHERE `lsc_id` = 1
);

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
    100,
    55,
    'SMS 관리',
    '/lpadmin/config/sms.config.php',
    'sms.config.php',
    '|9||10|',
    155,
    1
WHERE NOT EXISTS (
    SELECT 1
    FROM `l_menu`
    WHERE `lm_url` = '/lpadmin/config/sms.config.php'
);
