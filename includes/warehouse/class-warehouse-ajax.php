<?php
if (!defined('ABSPATH')) exit;

class Warehouse_Ajax {

    public static function init() {
        add_action('wp_ajax_wh_create_warehouse', [__CLASS__, 'create']);
        add_action('wp_ajax_wh_update_warehouse', [__CLASS__, 'update']);

        add_action('wp_ajax_load_provinces', [__CLASS__, 'load_provinces']);
        add_action('wp_ajax_load_districts', [__CLASS__, 'load_districts']);
        add_action('wp_ajax_load_communes', [__CLASS__, 'load_communes']);
    }

    /** Lấy tỉnh */
    public static function load_provinces() {
        $provinces = Location_Service::get_provinces();
        wp_send_json(['provinces' => $provinces]);
    }

    /** Lấy quận */
    public static function load_districts() {
        $province_code = sanitize_text_field($_POST['province_code']);
        $districts = Location_Service::get_districts($province_code);
        wp_send_json(['districts' => $districts]);
    }

    /** Lấy phường */
    public static function load_communes() {
        $district_code = sanitize_text_field($_POST['district_code']);
        $communes = Location_Service::get_communes($district_code);
        wp_send_json(['communes' => $communes]);
    }

    /** Tạo kho */
    public static function create() {
        $data = [
            'name'     => sanitize_text_field($_POST['name']),
            'phone'    => sanitize_text_field($_POST['phone']),
            'contact'  => sanitize_text_field($_POST['contact']),
            'address'  => sanitize_text_field($_POST['address']),
            'province' => sanitize_text_field($_POST['province']),
            'district' => sanitize_text_field($_POST['district']),
            'commune'  => sanitize_text_field($_POST['commune']),
            'primary'  => sanitize_text_field($_POST['primary']),
        ];

        $res = Warehouse_API::create($data);

        wp_send_json([
            'success' => $res['status'] === 'Success',
            'message' => $res['status'] === 'Success' ? 'Tạo kho thành công!' : ($res['message'] ?? 'Lỗi tạo kho!')
        ]);
    }

    /** Update */
    public static function update() {
        $data = [
            'code'    => sanitize_text_field($_POST['code']),
            'name'    => sanitize_text_field($_POST['name']),
            'phone'   => sanitize_text_field($_POST['phone']),
            'contact' => sanitize_text_field($_POST['contact']),
        ];

        $res = Warehouse_API::update($data);

        wp_send_json([
            'success' => $res['status'] === 'Success',
            'message' => $res['status'] === 'Success' ? 'Cập nhật kho thành công!' : ($res['message'] ?? 'Lỗi cập nhật kho!')
        ]);
    }
}

Warehouse_Ajax::init();
