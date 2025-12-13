<?php
/**
 * Plugin Name: SuperShip
 * Description: WooCommerce SuperShip Integration
 * Author: SuperTek
 * Version: 1.0.0
 * Requires Plugins: woocommerce
 * Text Domain: supership
 * Domain Path: /languages
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
add_action('admin_enqueue_scripts', function () {
    wp_enqueue_style(
        'bootstrap-icons',
        'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css',
        [],
        '1.11.1'
    );
});
add_action('woocommerce_shipping_init', function(){
    require_once DIR . 'includes/extend_file/class-supership-shipping.php';
});

add_filter('woocommerce_shipping_methods', function($methods){
    $methods['supership'] = 'Shipping_Method';
    return $methods;
});
add_action('plugins_loaded', function(){
    if (!did_action('woocommerce_loaded')) {
         add_action('admin_notices', function () {
            echo '<div class="notice notice-error">
                <p>
                    <strong>SuperShip</strong> requires <strong>WooCommerce</strong> to be installed and activated.
                </p>
            </div>';
        });
        return;
    }
    require_once DIR . 'includes/extend_file/class-supership-wc-add-files-address.php';
    require_once DIR . 'includes/dashboard_admin/class-supership-admin-ui.php';
    require_once DIR . 'includes/dashboard_admin/class-supership-order-creation-handler.php';
    require_once DIR . 'includes/dashboard_admin/class-supership-wc-custom-fields.php';
    require_once DIR . 'includes/metabox/class-supership-shipping-metabox.php';
    require_once DIR . 'includes/extend_file/class-supership-checkout-blocks.php';
    Store_Address_Extended::init();
    SuperShip_Shipping_MetaBox::init();
    Admin_Menu::init();
    Admin_Create_Order::init();
    Ajax::init();
    Supership_Checkout_Blocks::init(); 
});
add_action('plugins_loaded', function () {
    load_plugin_textdomain(
        'supership',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages'
    );
});
add_action('woocommerce_settings_save_general', function() {
    Store_Address_Extended::save_fields();
    Settings::maybe_create_warehouse_after_address_save();
}, 10);
add_action('admin_footer', function () {

    $msg = get_transient('supership_flash_message');
    if (!$msg) return;

    delete_transient('supership_flash_message');
    ?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            icon: '<?php echo esc_js($msg['type']); ?>',
            title: '<?php echo esc_js($msg['title']); ?>',
            text: '<?php echo esc_js($msg['text']); ?>',
            confirmButtonText: '<?php echo esc_js(__('OK', 'supership')); ?>',
            allowOutsideClick: true,
            allowEscapeKey: true
        });
    });
    </script>
    <?php
});
add_action('admin_enqueue_scripts', function() {
    wp_enqueue_style('supership-admin', URL . 'assets/css/admin.css');
    wp_enqueue_script('supership-warehouse', URL . 'assets/js/warehouse.js', ['jquery'], false, true);
    wp_localize_script('supership-warehouse', 'wh_ajax', [
        'ajaxurl' => admin_url('admin-ajax.php')
    ]);
});

add_action('admin_enqueue_scripts', function($hook) {
    $screen = get_current_screen();
    if (!$screen) return;
    // HPOS screen
    $is_hpos = ($screen->id === 'woocommerce_page_wc-orders');
    // Classic edit order screen
    $is_classic = ($screen->id === 'shop_order');
    if (!$is_hpos && !$is_classic) {
        return;
    }
    wp_enqueue_style(
        'supership-metabox-style',
        URL . 'assets/css/supership-metabox.css',
        [],
        time()
    );
});
add_action('admin_enqueue_scripts', function () {
    wp_enqueue_script(
        'sweetalert2',
        'https://cdn.jsdelivr.net/npm/sweetalert2@11',
        [],
        '11',
        true
    );
});


