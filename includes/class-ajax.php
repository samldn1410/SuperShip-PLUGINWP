<?php
if (!defined('ABSPATH')) exit;

class Ajax {

    public static function init() {
        add_action('wp_ajax_load_districts', [__CLASS__, 'load_districts']);
        add_action('wp_ajax_load_communes', [__CLASS__, 'load_communes']);
    }
    public static function load_districts() {
        $province_code = sanitize_text_field($_POST['province_code'] ?? '');
        $districts = Location_Service::get_districts($province_code);
        wp_send_json(['districts' => $districts]);
    }

    public static function load_communes() {
        $district_code = sanitize_text_field($_POST['district_code'] ?? '');
        $communes = Location_Service::get_communes($district_code);
        wp_send_json(['communes' => $communes]);
    }
}