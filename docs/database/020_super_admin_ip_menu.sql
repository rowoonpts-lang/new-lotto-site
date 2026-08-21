INSERT INTO l_menu (
    lm_cate1,
    lm_cate2,
    lm_name,
    lm_url,
    lm_php_name,
    lm_level,
    lm_order,
    lm_use
)
SELECT
    100,
    60,
    '최고관리자 접속IP',
    '/lpadmin/config/super.admin.ip.php',
    'super.admin.ip.php',
    '|10|',
    160,
    1
WHERE NOT EXISTS (
    SELECT 1
      FROM l_menu
     WHERE lm_cate1 = 100
       AND lm_cate2 = 60
       AND lm_url = '/lpadmin/config/super.admin.ip.php'
);
