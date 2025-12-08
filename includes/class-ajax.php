<?php
if (!defined('ABSPATH')) exit;

class SS_Ajax {

    public static function init() {
        // Chi giu lai cac ham load dia chi
        add_action('wp_ajax_ss_load_districts', [__CLASS__, 'load_districts']);
        add_action('wp_ajax_ss_load_communes', [__CLASS__, 'load_communes']);
    }

    // Ham tinh phi (calc_fee) da duoc loai bo

    public static function load_districts() {
        $province_code = sanitize_text_field($_POST['province_code'] ?? '');
        $districts = SS_Location_Service::get_districts($province_code);
        wp_send_json(['districts' => $districts]);
    }

    public static function load_communes() {
        $district_code = sanitize_text_field($_POST['district_code'] ?? '');
        $communes = SS_Location_Service::get_communes($district_code);
        wp_send_json(['communes' => $communes]);
    }
}