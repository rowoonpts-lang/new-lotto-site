-- Lotto Platform payment bank account admin menu
-- Adds bank account management under Environment Settings (admin only).

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
    10,
    '입금계좌관리',
    '/lpadmin/payment/bank.account.php',
    'bank.account.php',
    '|9|',
    110,
    1
WHERE NOT EXISTS (
    SELECT 1
    FROM `l_menu`
    WHERE `lm_url` = '/lpadmin/payment/bank.account.php'
);
