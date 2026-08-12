-- Lotto result admin menu
-- Separates filter results from member winning results.
-- Filter results are available to administrators only.
-- Winning results are available to staff, managers, and administrators.

UPDATE `l_menu`
SET
    `lm_name` = '필터결과',
    `lm_url` = '/lpadmin/lucky/filter.result.php',
    `lm_php_name` = 'filter.result.php',
    `lm_level` = '|9|',
    `lm_order` = 230,
    `lm_use` = 1
WHERE `lm_cate1` = 200
  AND `lm_cate2` = 30;

UPDATE `l_menu`
SET
    `lm_name` = '당첨결과',
    `lm_cate1` = 200,
    `lm_cate2` = 40,
    `lm_php_name` = 'result.php',
    `lm_level` = '|5||6||7||9|',
    `lm_order` = 240,
    `lm_use` = 1
WHERE `lm_url` = '/lpadmin/lucky/result.php';

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
    40,
    '당첨결과',
    '/lpadmin/lucky/result.php',
    'result.php',
    '|5||6||7||9|',
    240,
    1
WHERE NOT EXISTS (
    SELECT 1
    FROM `l_menu`
    WHERE `lm_url` = '/lpadmin/lucky/result.php'
);
