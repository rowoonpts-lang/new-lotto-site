<?php
if (!defined('_GNUBOARD_')) exit;

if (!function_exists('lottoGetSiteConfig')) {
    function lottoGetSiteConfig()
    {
        static $site_config = null;

        if ($site_config !== null) {
            return $site_config;
        }

        $site_config = array(
            'brand_name' => 'LottoGPT',
            'company_name' => '지오인터내셔널',
            'representative_name' => '김민지',
            'business_number' => '350-04-01576',
            'mail_order_number' => '2020-인천남동구-1821',
            'privacy_manager' => '김민지',
            'company_address' => '인천광역시 남동구 남동대로 777번길 43, 5층',
            'contact_phone' => '',
            'contact_email' => 'lottojoongsim@gmail.com',
            'contact_hours' => '평일·토요일 10:00 ~ 18:00',
            'contact_closed' => '일요일 및 공휴일 휴무',
            'common_notice' => 'LottoGPT의 분석 정보는 로또 번호 조합과 통계 정보를 제공하기 위한 참고 자료이며, 당첨을 보장하거나 확정하는 서비스가 아닙니다. 서비스 이용에 따른 최종 판단과 책임은 이용자 본인에게 있습니다.',
            'copyright_name' => 'GIO INTERNATIONAL'
        );

        $row = sql_fetch("
            select
                lsc_id,
                brand_name,
                company_name,
                representative_name,
                business_number,
                mail_order_number,
                privacy_manager,
                company_address,
                contact_phone,
                contact_email,
                contact_hours,
                contact_closed,
                common_notice,
                copyright_name
            from l_site_config
            where lsc_id = 1
            limit 1
        ", false);

        if (!empty($row['lsc_id'])) {
            foreach ($site_config as $key => $value) {
                if (array_key_exists($key, $row)) {
                    $site_config[$key] = (string) $row[$key];
                }
            }
        }

        return $site_config;
    }
}
?>
