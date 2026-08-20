-- Lotto Platform policy content management
--
-- Purpose:
-- Add Terms of Service and Privacy Policy content rows using
-- the existing GnuBoard g5_content table.
--
-- Mobile-only content is not used because the platform now
-- uses the responsive PC layout for all devices.

INSERT INTO `g5_content` (
    `co_id`,
    `co_html`,
    `co_subject`,
    `co_content`,
    `co_seo_title`,
    `co_mobile_content`,
    `co_skin`,
    `co_mobile_skin`,
    `co_tag_filter_use`,
    `co_hit`,
    `co_include_head`,
    `co_include_tail`
)
SELECT
    'provision',
    0,
    '이용약관',
    '',
    '',
    '',
    'basic',
    'basic',
    1,
    0,
    '',
    ''
WHERE NOT EXISTS (
    SELECT 1
      FROM `g5_content`
     WHERE `co_id` = 'provision'
);


INSERT INTO `g5_content` (
    `co_id`,
    `co_html`,
    `co_subject`,
    `co_content`,
    `co_seo_title`,
    `co_mobile_content`,
    `co_skin`,
    `co_mobile_skin`,
    `co_tag_filter_use`,
    `co_hit`,
    `co_include_head`,
    `co_include_tail`
)
SELECT
    'privacy',
    0,
    '개인정보처리방침',
    '',
    '',
    '',
    'basic',
    'basic',
    1,
    0,
    '',
    ''
WHERE NOT EXISTS (
    SELECT 1
      FROM `g5_content`
     WHERE `co_id` = 'privacy'
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
    30,
    '이용약관',
    '/lpadmin/config/terms.php',
    'terms.php',
    '|9|',
    130,
    1
WHERE NOT EXISTS (
    SELECT 1
      FROM `l_menu`
     WHERE `lm_url` = '/lpadmin/config/terms.php'
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
    40,
    '개인정보처리방침',
    '/lpadmin/config/privacy.php',
    'privacy.php',
    '|9|',
    140,
    1
WHERE NOT EXISTS (
    SELECT 1
      FROM `l_menu`
     WHERE `lm_url` = '/lpadmin/config/privacy.php'
);


UPDATE `l_menu`
   SET `lm_cate2` = 50,
       `lm_order` = 150
 WHERE `lm_url` = '/lpadmin/config/popup.list.php';
