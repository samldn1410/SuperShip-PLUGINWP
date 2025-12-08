<?php
/**
 * Plugin Name: SuperShip
 * Plugin URI:  https://example.com/supership
 * Description: WooCommerce integration for SuperShip
 * Version:     1.0.0
 * Author:      SuperShip Dev
 */
if (!defined('ABSPATH')) exit;

define('SS_DIR', plugin_dir_path(__FILE__));
define('SS_URL', plugin_dir_url(__FILE__));

// Load classes in correct order
require_once SS_DIR . 'includes/class-settings.php';
require_once SS_DIR . 'includes/class-api.php';
require_once SS_DIR . 'includes/class-location-service.php';
require_once SS_DIR . 'includes/class-order-service.php';
require_once SS_DIR . 'includes/class-ss-warehouses-service.php';
require_once SS_DIR . 'includes/class-admin-menu.php';
require_once SS_DIR . 'includes/class-admin-create-order.php';
require_once SS_DIR . 'includes/class-ajax.php';

add_action('plugins_loaded', function () {
    SS_Admin_Menu::init();
    SS_Admin_Create_Order::init();
    SS_Ajax::init();
});