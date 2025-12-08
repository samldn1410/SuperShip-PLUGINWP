<?php
if (!defined('ABSPATH')) exit;

class SS_Location_Service {

    const CACHE_P = 'ss_provinces';
    const CACHE_D = 'ss_districts_';
    const CACHE_C = 'ss_communes_';

    public static function get_provinces() {
        $cached = get_transient(self::CACHE_P);
        if ($cached) return $cached;

        $res = SS_API::get('/v1/partner/areas/province');

        if ($res['status'] === 'Success') {
            set_transient(self::CACHE_P, $res['results'], 24 * HOUR_IN_SECONDS);
            return $res['results'];
        }

        return [];
    }

    public static function get_districts($province_code) {
        if (!$province_code) return [];

        $key = self::CACHE_D . $province_code;
        $cached = get_transient($key);
        if ($cached) return $cached;

        $res = SS_API::get('/v1/partner/areas/district?province=' . urlencode($province_code));

        if ($res['status'] === 'Success') {
            set_transient($key, $res['results'], 24 * HOUR_IN_SECONDS);
            return $res['results'];
        }

        return [];
    }

    public static function get_communes($district_code) {
        if (!$district_code) return [];

        $key = self::CACHE_C . $district_code;
        $cached = get_transient($key);
        if ($cached) return $cached;

        $res = SS_API::get('/v1/partner/areas/commune?district=' . urlencode($district_code));

        if ($res['status'] === 'Success') {
            set_transient($key, $res['results'], 24 * HOUR_IN_SECONDS);
            return $res['results'];
        }

        return [];
    }

    public static function get_province_name($code) {
        if (!$code) return '';
        $provinces = self::get_provinces();
        foreach ($provinces as $p) {
            if ($p['code'] === $code) {
                return $p['name'];
            }
        }
        return '';
    }

    public static function get_district_name($province_code, $district_code) {
        if (!$district_code) return '';
        $districts = self::get_districts($province_code);
        foreach ($districts as $d) {
            if ($d['code'] === $district_code) {
                return $d['name'];
            }
        }
        return '';
    }

    public static function get_commune_name($district_code, $commune_code) {
        if (!$commune_code) return '';
        $communes = self::get_communes($district_code);
        foreach ($communes as $c) {
            if ($c['code'] === $commune_code) {
                return $c['name'];
            }
        }
        return '';
    }
   public static function parse_formatted_address($formatted) {
        $parts = array_map('trim', explode(',', $formatted));

        // Lấy từ phải sang trái theo đúng format Supership trả về
        $province = $parts[count($parts) - 1] ?? '';
        $district = $parts[count($parts) - 2] ?? '';
        $commune  = $parts[count($parts) - 3] ?? '';

        return [
            'province' => $province,
            'district' => $district,
            'commune'  => $commune,
        ];
   }

}