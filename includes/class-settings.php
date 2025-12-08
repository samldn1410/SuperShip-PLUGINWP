<?php
if (!defined('ABSPATH')) exit;

class SS_Settings {

    public static function get_token() {
        return get_option('ss_access_token', '');
    }

    public static function save_token($token) {
        update_option('ss_access_token', sanitize_text_field($token));
    }
}