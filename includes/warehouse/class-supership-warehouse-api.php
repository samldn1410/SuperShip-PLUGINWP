<?php
if (!defined('ABSPATH')) exit;

class Warehouse_API {

    public static function get_all() {
        return API::get('/v1/partner/warehouses');
    }

    public static function create($data) {
        return API::post('/v1/partner/warehouses/create', $data);
    }

    public static function update($data) {
        return API::post('/v1/partner/warehouses/update', $data);
    }
}
