<?php
if (!defined('ABSPATH')) exit;

class Admin_Menu {

    public static function init() {

        add_filter(
            'woocommerce_settings_tabs_array',[__CLASS__, 'add_tab'],
            50
        );
        add_filter(
            'woocommerce_get_sections_supership',[__CLASS__, 'add_sections']
        );
        add_action(
            'woocommerce_settings_supership',[__CLASS__, 'render']
        );
         add_action('admin_enqueue_scripts', [__CLASS__, 'hide_save_button']);
    }


    public static function add_tab($tabs) {
        $tabs['supership'] = __('SuperShip', 'supership');
        return $tabs;
    }

    public static function add_sections($sections) {
        return [
            'info'         => __('Thông tin', 'supership'),
            'api_token'    => __('API Token', 'supership'),
            // 'webhook'      => __('Webhook', 'supership'),
            // 'webhook_logs' => __('Webhook Logs', 'supership'),
            'warehouses'   => __('Kho hàng', 'supership'),
            // 'create_order' => __('Tạo đơn', 'supership'),
        ];
    }
    public static function render() {
        global $current_section;

        // Nav giống Shipping
        self::render_sections_nav();
        echo '<br class="clear" />';

        switch ($current_section ?: 'info') {

            case 'api_token':
                include DIR . 'views/settings.php'; 
                break;

            // case 'webhook':
            //     include DIR . 'views/webhook-settings.php';
            //     break;

            // case 'webhook_logs':
            //     include DIR . 'views/webhook-logs.php';
            //     break;

            case 'warehouses':
                include DIR . 'views/warehouse.php';
                break;

            // case 'create_order':
            //     include DIR . 'views/create-order.php';
            //     break;

            default:
                self::output_info();
                break;
        }
    }
    private static function render_sections_nav() {
        global $current_section;

        $sections = self::add_sections([]);
        echo '<ul class="subsubsub">';

        $last = array_key_last($sections);

        foreach ($sections as $id => $label) {
            $url = admin_url('admin.php?page=wc-settings&tab=supership&section=' . $id);
            $class = ($current_section === $id || (!$current_section && $id === 'info')) ? 'current' : '';

            echo '<li><a href="' . esc_url($url) . '" class="' . esc_attr($class) . '">' . esc_html($label) . '</a>';
            if ($id !== $last) echo ' | ';
            echo '</li>';
        }

        echo '</ul>';
    }
    public static function hide_save_button($hook) {
    if ($hook !== 'woocommerce_page_wc-settings') {
        return;
    }

    if (!isset($_GET['tab']) || $_GET['tab'] !== 'supership') {
        return;
    }
        $current_section = isset($_GET['section']) ? $_GET['section'] : 'info';
        $sections_without_save = ['info', 'webhook_logs', 'warehouses', 'create_order','api_token','webhook'];
        if (in_array($current_section, $sections_without_save)) {
            ?>
            <style type="text/css">
                .woocommerce-save-button {
                    display: none !important;
                }
            </style>
            <?php
        }
    }

    private static function output_info() {
        ?>
        <h2><?php esc_html_e('SuperShip – Kết nối vận chuyển thông minh', 'supership'); ?></h2>

        <p>
            <?php esc_html_e(
                'SuperShip giúp kết nối WooCommerce với hệ thống vận chuyển SuperShip, '
                . 'tự động tạo đơn, tính phí và theo dõi trạng thái.',
                'supership'
            ); ?>
        </p>

        <table class="widefat striped" style="max-width:800px">
            <tbody>
                <tr>
                    <th><?php esc_html_e('API Token', 'supership'); ?></th>
                    <td>
                        <?php echo Settings::get_token()
                            ? '<span style="color:green">✔ Đã cấu hình</span>'
                            : '<span style="color:red">✘ Chưa cấu hình</span>'; ?>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Kho hàng', 'supership'); ?></th>
                    <td>
                        <?php echo get_option('supership_shop_warehouse_created')
                            ? '<span style="color:green">✔ Đã tạo</span>'
                            : '<span style="color:red">✘ Chưa tạo</span>'; ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php
    }
}

Admin_Menu::init();
