-- Lotto Platform site configuration
--
-- Purpose:
-- Store public homepage company/brand/contact information separately
-- from the core GnuBoard g5_config table.
--
-- Existing g5_new_win is reused for homepage popup management.

CREATE TABLE IF NOT EXISTS `l_site_config` (
    `lsc_id` tinyint unsigned NOT NULL DEFAULT 1,

    `brand_name` varchar(100) NOT NULL DEFAULT '',
    `company_name` varchar(150) NOT NULL DEFAULT '',
    `representative_name` varchar(100) NOT NULL DEFAULT '',
    `business_number` varchar(50) NOT NULL DEFAULT '',
    `mail_order_number` varchar(100) NOT NULL DEFAULT '',
    `privacy_manager` varchar(100) NOT NULL DEFAULT '',
    `company_address` varchar(255) NOT NULL DEFAULT '',

    `contact_phone` varchar(50) NOT NULL DEFAULT '',
    `contact_email` varchar(150) NOT NULL DEFAULT '',
    `contact_hours` varchar(255) NOT NULL DEFAULT '',
    `contact_closed` varchar(255) NOT NULL DEFAULT '',

    `common_notice` text NOT NULL,
    `copyright_name` varchar(150) NOT NULL DEFAULT '',

    `updated_by` varchar(100) NOT NULL DEFAULT '',
    `updated_at` datetime DEFAULT NULL,

    PRIMARY KEY (`lsc_id`)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb3
  COLLATE=utf8mb3_general_ci;


INSERT INTO `l_site_config` (
    `lsc_id`,
    `brand_name`,
    `company_name`,
    `representative_name`,
    `business_number`,
    `mail_order_number`,
    `privacy_manager`,
    `company_address`,
    `contact_phone`,
    `contact_email`,
    `contact_hours`,
    `contact_closed`,
    `common_notice`,
    `copyright_name`,
    `updated_by`,
    `updated_at`
)
SELECT
    1,
    'LottoGPT',
    '지오인터내셔널',
    '김민지',
    '350-04-01576',
    '2020-인천남동구-1821',
    '김민지',
    '인천광역시 남동구 남동대로 777번길 43, 5층',
    '',
    'lottojoongsim@gmail.com',
    '평일·토요일 10:00 ~ 18:00',
    '일요일 및 공휴일 휴무',
    'LottoGPT의 분석 정보는 로또 번호 조합과 통계 정보를 제공하기 위한 참고 자료이며, 당첨을 보장하거나 확정하는 서비스가 아닙니다. 서비스 이용에 따른 최종 판단과 책임은 이용자 본인에게 있습니다.',
    'GIO INTERNATIONAL',
    '',
    NOW()
WHERE NOT EXISTS (
    SELECT 1
      FROM `l_site_config`
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
    20,
    '홈페이지 설정',
    '/lpadmin/config/site.config.php',
    'site.config.php',
    '|9|',
    120,
    1
WHERE NOT EXISTS (
    SELECT 1
      FROM `l_menu`
     WHERE `lm_url` = '/lpadmin/config/site.config.php'
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
    '중요공지 팝업',
    '/lpadmin/config/popup.list.php',
    'popup.list.php',
    '|9|',
    130,
    1
WHERE NOT EXISTS (
    SELECT 1
      FROM `l_menu`
     WHERE `lm_url` = '/lpadmin/config/popup.list.php'
);
