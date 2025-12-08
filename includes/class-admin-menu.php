<?php
if (!defined('ABSPATH')) exit;

class SS_Admin_Menu {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register']);
    }

    public static function register() {
        add_menu_page(
            'SuperShip',
            'SuperShip',
            'manage_options',
            'ss-settings',
            '',
            'dashicons-location-alt'
        );

        add_submenu_page('ss-settings', 'Cài đặt', 'Cài đặt', 'manage_options', 'ss-settings', function () {
            include SS_DIR . 'views/settings.php';
        });

        add_submenu_page('ss-settings', 'Tạo đơn', 'Tạo đơn', 'manage_options', 'ss-create', function () {
            include SS_DIR . 'views/create-order.php';
        });
    }
}