-- Lotto Platform consultation history admin menu

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
    20,
    '상담내역',
    '/lpadmin/config/consultation.history.php',
    'consultation.history.php',
    '|9||10|',
    280,
    1
WHERE NOT EXISTS (
    SELECT 1
      FROM `l_menu`
     WHERE `lm_url` = '/lpadmin/config/consultation.history.php'
);
