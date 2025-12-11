<?php
if (!defined('ABSPATH')) exit;

class Admin_Menu {

    public static function init() {
        add_action('admin_menu', [__CLASS__, 'register']);
    }

    public static function register() {
        add_menu_page(
            'SuperShip', // Page Title
            'SuperShip', // Menu Title
            'manage_options', // Quyền truy cập, chỉ admin mới được thấy
            'supership-settings', // Menu Slug
            '', // hàm hiển thị nội dung trnag
            'dashicons-location-alt' 
        );

        add_submenu_page('supership-settings',  // parent slug
        'Cài đặt', //Page Title
        'Cài đặt', // Menu Title
        'manage_options',
         'supership-settings',  
         function () {
            include DIR . 'views/settings.php'; // hàm hiển thị nội dung trang
        });
        add_submenu_page(
            'supership-settings',
            'Cấu hình Webhook',
            'Webhook',
            'manage_options',
            'supership-webhook',
             function () {
                include DIR . 'views/webhook-settings.php';
            }
        );
        add_submenu_page(
            'supership-settings',
            'Webhook Logs',
            'Webhook Logs',
            'manage_options',
            'supership-webhook-logs',
            function () {
                include DIR . 'views/webhook-logs.php';
            }
        );

        add_submenu_page('supership-settings', 'Tạo đơn', 'Tạo đơn', 'manage_options', 'supership-create', function () {
            include DIR . 'views/create-order.php';
        });

      
        add_submenu_page(
            'supership-settings',
            'Kho hàng',
            'Kho hàng',
            'manage_options',
            'supership-warehouses',
            [__CLASS__, 'view_list']
        );

    }
         public static function view_list() {
             include DIR . 'views/warehouse.php';
        }    
}