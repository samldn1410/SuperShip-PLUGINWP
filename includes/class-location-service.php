<?php
if (!defined('ABSPATH')) exit;

class Location_Service {

    const CACHE_P = 'provinces';
    const CACHE_D = 'districts_';
    const CACHE_C = 'communes_';

    public static function get_provinces() {
        $cached = get_transient(self::CACHE_P);
        if ($cached) return $cached;

        $res = API::get('/v1/partner/areas/province');

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

        $res = API::get('/v1/partner/areas/district?province=' . urlencode($province_code));

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

        $res = API::get('/v1/partner/areas/commune?district=' . urlencode($district_code));

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
  
    // Load address json
    private static $local = null;
    private static function load_local_data() {
        if (self::$local !== null) {
            return self::$local;
        }
        // Đường dẫn đến file JSON
        $file = plugin_dir_path(__FILE__) . '../../assets/data/address.json';

        if (file_exists($file)) {
            self::$local = json_decode(file_get_contents($file), true);
        } else {
            self::$local = [
                'provinces' => [],
                'districts' => [],
                'communes'  => []
            ];
        }
        return self::$local;
    }
    public static function get_provinces_local() {
        $data = self::load_local_data();
        return $data['provinces'] ?? [];
    }
    public static function get_districts_local($province_code) {
        $data = self::load_local_data();
        return $data['districts'][$province_code] ?? [];
    }
    public static function get_communes_local($district_code) {
        $data = self::load_local_data();
        return $data['communes'][$district_code] ?? [];
    }

}