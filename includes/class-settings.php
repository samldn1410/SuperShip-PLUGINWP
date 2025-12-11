<?php
if (!defined('ABSPATH')) exit;

class Settings {

    public static function get_token() {
        return get_option('access_token', '');
    }

    public static function save_token($token) {
        update_option('access_token', sanitize_text_field($token));
    }
}