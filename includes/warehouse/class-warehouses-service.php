<?php
if (!defined('ABSPATH')) exit;

class Warehouses_Service {

     public static function get_all() {
        $res = API::get('/v1/partner/warehouses');
        if ($res && isset($res['status']) && $res['status'] === 'Success') {
            return $res['results'] ?? [];
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