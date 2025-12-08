<?php
if (!defined('ABSPATH')) exit;

class SS_Warehouses_Service {

    const CACHE_KEY = 'ss_warehouses_cache';

    // ... (Các hàm static get_all, get_default, find không đổi)

    public static function get_all() {
        $cached = get_transient(self::CACHE_KEY);
        if ($cached) return $cached;

        $res = SS_API::get('/v1/partner/warehouses');

        if ($res && $res['status'] === 'Success') {
            $list = $res['results'];
            set_transient(self::CACHE_KEY, $list, 24 * HOUR_IN_SECONDS);
            return $list;
        }

        return [];
    }

    public static function get_default() {
        $warehouses = self::get_all();

        foreach ($warehouses as $w) {
            if (isset($w['primary']) && $w['primary'] == "1") {
                return $w;
            }
        }

        return null;
    }

    public static function find($code) {
        $warehouses = self::get_all();

        foreach ($warehouses as $w) {
            if ($w['code'] === $code) {
                return $w;
            }
        }
        return null;
    }
    
}