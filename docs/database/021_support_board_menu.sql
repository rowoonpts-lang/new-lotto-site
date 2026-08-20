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
    300,
    10,
    '공지사항',
    '/lpadmin/bbs/notice.list.php',
    'notice.list.php',
    '|9|',
    310,
    1
WHERE NOT EXISTS (
    SELECT 1
      FROM l_menu
     WHERE lm_cate1 = 300
       AND lm_cate2 = 10
);

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
    300,
    20,
    '자주묻는 질문',
    '/lpadmin/bbs/faq.list.php',
    'faq.list.php',
    '|9|',
    320,
    0
WHERE NOT EXISTS (
    SELECT 1
      FROM l_menu
     WHERE lm_cate1 = 300
       AND lm_cate2 = 20
);

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
    300,
    30,
    '1:1 상담',
    '/lpadmin/bbs/qa.list.php',
    'qa.list.php',
    '|9|',
    330,
    0
WHERE NOT EXISTS (
    SELECT 1
      FROM l_menu
     WHERE lm_cate1 = 300
       AND lm_cate2 = 30
);
