-- Lotto Platform SMS history admin menu

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
    10,
    'SMS 발송내역',
    '/lpadmin/config/sms.history.php',
    'sms.history.php',
    '|9||10|',
    270,
    1
WHERE NOT EXISTS (
    SELECT 1
      FROM `l_menu`
     WHERE `lm_url` = '/lpadmin/config/sms.history.php'
);
