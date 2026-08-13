-- Lotto Platform payment administration menus
-- Adds administrator-only payment approval and sales history menus under settings management.

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
    50,
    '결제승인',
    '/lpadmin/payment/payment.approval.php',
    'payment.approval.php',
    '|9|',
    250,
    1
WHERE NOT EXISTS (
    SELECT 1
      FROM `l_menu`
     WHERE `lm_url` = '/lpadmin/payment/payment.approval.php'
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
    200,
    60,
    '매출내역',
    '/lpadmin/payment/sales.list.php',
    'sales.list.php',
    '|9|',
    260,
    1
WHERE NOT EXISTS (
    SELECT 1
      FROM `l_menu`
     WHERE `lm_url` = '/lpadmin/payment/sales.list.php'
);
