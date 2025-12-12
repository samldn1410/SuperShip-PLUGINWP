<?php
if (!defined('ABSPATH')) exit;

class Admin_Menu {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register'], 99);
    }

    public static function register() {


        if (!class_exists('WooCommerce')) {
            return;
        }

        add_submenu_page(
            'woocommerce',
            __('API Token', 'supership'),            
            __('API Token', 'supership'),         
            'manage_woocommerce',
            'supership-api-token',
            [__CLASS__, 'api_token_page']
        );

        add_submenu_page(
            'woocommerce',
            __('Cấu hình Webhook', 'supership'),
            __('Webhook', 'supership'),
            'manage_woocommerce',
            'supership-webhook',
            [__CLASS__, 'webhook_page']
        );

        add_submenu_page(
            'woocommerce',
            __('Nhật ký Webhook', 'supership'),
            __('Webhook Logs', 'supership'),
            'manage_woocommerce',
            'supership-webhook-logs',
            [__CLASS__, 'webhook_logs_page']
        );

        add_submenu_page(
            'woocommerce',
            __('Tạo đơn SuperShip', 'supership'),
            __('Tạo đơn SuperShip', 'supership'),
            'manage_woocommerce',
            'supership-create-order',
            [__CLASS__, 'create_order_page']
        );

        add_submenu_page(
            'woocommerce',
            __('Kho hàng', 'supership'),
            __('Kho hàng', 'supership'),
            'manage_woocommerce',
            'supership-warehouses',
            [__CLASS__, 'warehouses_page']
        );
    }

    public static function api_token_page() {
        include DIR . 'views/settings.php';
    }

    public static function webhook_page() {
        include DIR . 'views/webhook-settings.php';
    }

    public static function webhook_logs_page() {
        include DIR . 'views/webhook-logs.php';
    }

    public static function create_order_page() {
        include DIR . 'views/create-order.php';
    }

    public static function warehouses_page() {
        include DIR . 'views/warehouse.php';
    }
}
Admin_Menu::init();
