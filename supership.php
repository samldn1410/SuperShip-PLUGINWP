<?php
/**
 * Plugin Name: SuperShip
 * Description: WooCommerce SuperShip Integration
 * Author: SuperTek
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) exit;

define('DIR', plugin_dir_path(__FILE__));
define('URL', plugin_dir_url(__FILE__));


require_once DIR . 'includes/class-settings.php';
require_once DIR . 'includes/class-api.php';
require_once DIR . 'includes/class-location-service.php';
require_once DIR . 'includes/orders/class-order-service.php';
require_once DIR . 'includes/warehouse/class-warehouses-service.php';
require_once DIR . 'includes/class-admin-menu.php';
require_once DIR . 'includes/orders/class-admin-create-order.php';
require_once DIR . 'includes/class-ajax.php';
require_once DIR . 'includes/webhook/class-supership-webhook-api.php';
require_once DIR . 'includes/webhook/class-supership-webhook-handler.php';
require_once DIR . 'includes/warehouse/class-supership-warehouse-api.php';
require_once DIR . 'includes/warehouse/class-warehouse-ajax.php';
register_activation_hook(__FILE__, function() {
    require_once DIR . 'includes/database/class-supership-order-database.php';
    Order_Table::install();
    Webhook_Table::install();
});

add_action('woocommerce_shipping_init', function(){
    require_once DIR . 'includes/extend_file/class-supership-shipping.php';
});

add_filter('woocommerce_shipping_methods', function($methods){
    $methods['supership'] = 'Shipping_Method';
    return $methods;
});


add_action('plugins_loaded', function(){
    if (!class_exists('WooCommerce')) return;
    require_once DIR . 'includes/extend_file/class-supership-wc-add-files-address.php';
    require_once DIR . 'includes/dashboard_admin/class-supership-admin-ui.php';
    require_once DIR . 'includes/dashboard_admin/class-supership-order-creation-handler.php';
    require_once DIR . 'includes/dashboard_admin/class-supership-wc-custom-fields.php';
    require_once DIR . 'includes/metabox/class-supership-shipping-metabox.php';
    Store_Address_Extended::init();
    SuperShip_Shipping_MetaBox::init();
    Admin_Menu::init();
    Admin_Create_Order::init();
    Ajax::init();
});

add_action('admin_enqueue_scripts', function() {
    wp_enqueue_style('supership-admin', URL . 'assets/css/admin.css');
    wp_enqueue_script('supership-warehouse', URL . 'assets/js/warehouse.js', ['jquery'], false, true);
    wp_localize_script('supership-warehouse', 'wh_ajax', [
        'ajaxurl' => admin_url('admin-ajax.php')
    ]);
});



