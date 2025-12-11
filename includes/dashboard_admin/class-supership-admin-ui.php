<?php
if (!defined('ABSPATH')) exit;
use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
class Admin_UI {

    const AJAX_NONCE = 'modal_nonce';
    
    public static function init() {
        // add_action('woocommerce_order_item_add_action_buttons', [__CLASS__, 'render_order_buttons']);
        add_action('add_meta_boxes', [__CLASS__, 'add_supership_metabox']);
        // add_action('woocommerce_admin_order_data_after_order_details', [__CLASS__, 'render_order_buttons']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
        add_action('admin_footer', [__CLASS__, 'add_modal_html']);
        add_action('admin_init', function() {
        remove_action(
                'woocommerce_order_item_add_action_buttons',
                'woocommerce_order_item_add_action_buttons',
                10
            );
        });
        // Đăng ký AJAX handlers (cho người dùng đã đăng nhập)
        add_action('wp_ajax_load_config_modal', [__CLASS__, 'handle_ajax_load_config_modal']);
        add_action('wp_ajax_create_supership_order_ajax', [__CLASS__, 'handle_ajax_create_order']);
        add_action('wp_ajax_cancel_supership_order', [__CLASS__, 'ajax_cancel_supership_order']);
        add_action('wp_ajax_update_order_info', function() {
            check_ajax_referer('modal_nonce', 'security');
            $order_id = intval($_POST['order_id']); 
            global $wpdb;
            $table = $wpdb->prefix . 'supership_orders';

            $supership = $wpdb->get_row(
                $wpdb->prepare("SELECT supership_code FROM $table WHERE wp_order_id = %d", $order_id),
                ARRAY_A
            );

            $code = $supership['supership_code'] ?? '';

            if (!$code) {
                wp_send_json_error(['message' => 'Không tìm thấy mã đơn SuperShip để cập nhật.']);
            }
            // gọi API SuperShip
            $result = Order_Service::get_order_info($code);
            if ($result['status'] === 'Success') {
                Order_Service::update_supership_order_info($order_id, $result);
                wp_send_json_success(['message' => 'Cập nhật thông tin đơn thành công!']);
            }
            wp_send_json_error(['message' => 'Không cập nhật được thông tin']);
        });
    }
   
    private static function get_screen_id() {

        $is_hpos = class_exists(CustomOrdersTableController::class)
            && wc_get_container()->get(CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled();

        return $is_hpos
            ? wc_get_page_screen_id('shop-order')
            : 'shop_order';
    }

    public static function add_supership_metabox() {
     $screen = self::get_screen_id();
        add_meta_box(
            'supership_box',
            'SuperShip Actions',
            [__CLASS__, 'render_order_buttons'],
             $screen,
            'normal',      // vị trí: side, normal, advanced
            'high'
        );
    }

   public static function render_order_buttons($order)
    {
        if ($order instanceof WP_Post) {
            $order = wc_get_order($order->ID);
        }
        if (!$order) return;

        global $wpdb;
        $order_id = $order->get_id();
        $table = $wpdb->prefix . 'supership_orders';

        $row = $wpdb->get_row(
            $wpdb->prepare("SELECT supership_code, status_name FROM $table WHERE wp_order_id = %d", $order_id),
            ARRAY_A
        );

        // Wrapper UI đẹp
        echo '<div class="ss-order-actions" 
                style="margin-top:15px; 
                    display:flex; 
                    gap:10px; 
                    align-items:center;
                    flex-wrap:wrap;">';

        /** 1) Chưa tạo đơn → chỉ hiện nút tạo */
        if (!$row) {
            echo '<a href="#" 
                    class="button button-primary create-order-btn"
                    id="create-order-modal-btn"
                    data-order-id="' . $order_id . '">
                    Tạo đơn vận chuyển SuperShip
                </a>';

            echo '</div>';
            return;
        }

        /** Normalize */
        $code   = $row['supership_code'];
        $status = strtolower(trim($row['status_name']));

        /** 2) Đơn đã hủy → badge màu xám */
        if (in_array($status, ['hủy', 'huy', 'canceled', 'cancel'])) {

            echo '<span class="button" 
                    style="background:#777; 
                        color:white; 
                        cursor:default;">
                    Đơn đã hủy
                </span>';

            echo '</div>';
            return;
        }

        /** 3) Đã tạo đơn – Badge mã đơn */
        echo '<span class="button" 
                style="background:#28a745; 
                    color:white; 
                    cursor:default;">
                Đã tạo đơn: ' . esc_html($code) . '
            </span>';

        /** 4) Nút cập nhật (đẹp hơn và đồng bộ màu) */
        echo '<a href="#" 
                class="button update-order-info action-btn"
                data-order-id="' . $order_id . '" 
                style="background:#2271b1; 
                    color:white;">
                Cập nhật đơn
            </a>';

        /** 5) Nút hủy đơn nếu trạng thái cho phép */
        $pickup_states = [
            'chờ lấy hàng',
            'cho lay hang',
            'cho_lay_hang',
            'pending_pickup'
        ];

        if (in_array($status, $pickup_states)) {

            echo '<a href="#"
                    class="button cancel-order"
                    data-order-id="' . $order_id . '"
                    data-code="' . esc_attr($code) . '"
                    style="background:#ff4a4a; 
                        color:white;">
                    Hủy đơn
                </a>';
        }
        echo '</div>';
    }
    
    public static function ajax_cancel_supership_order()
    {
       check_ajax_referer('modal_nonce', 'security');

        $order_id = intval($_POST['order_id']);
        $supership_code  = sanitize_text_field($_POST['supership_code']);

        if (!$order_id || !$supership_code) {
            wp_send_json_error(['message' => 'Thiếu dữ liệu']);
        }

        $result = Order_Service::cancel_order($supership_code);

        if ($result['status'] === 'Success') {
            global $wpdb;
            $table = $wpdb->prefix . 'supership_orders';

            $wpdb->update(
                $table,
                ['status_name' => 'Hủy', 'updated_at' => current_time('mysql')],
                ['wp_order_id' => $order_id]
            );

            update_post_meta($order_id, 'order_canceled', 'yes');
            $wc_order = wc_get_order($order_id);
            if ($wc_order) {
                $wc_order->update_status(
                    'cancelled',
                    'Đơn SuperShip đã bị hủy.'
                );
            }
            wc_create_order_note($order_id, "Đơn".$result["code"]." đã được hủy vào lúc.");

            wp_send_json_success(['message' => 'Hủy thành công']);
        }

        wp_send_json_error(['message' => 'Hủy thất bại']);
    }
    // --- ENQUEUE & MODAL HTML ---

    public static function enqueue_assets($hook) {
            $screen = get_current_screen();

            if (!$screen) return;

            // WooCommerce HPOS screen
            $is_hpos = $screen->id === 'woocommerce_page_wc-orders';

            // Classic screen
            $is_classic = $hook === 'post.php' && $screen->post_type === 'shop_order';

            if (!$is_hpos && !$is_classic) {
                return;
            }

            wp_enqueue_style(
                'admin-modal-style',
                URL . 'assets/css/supership-modal.css',
                [],
                '1.0'
            );

            wp_enqueue_script(
                'admin-modal-script',
                URL . 'assets/js/supership-modal.js',
                ['jquery'],
                '1.0',
                true
            );

            wp_localize_script('admin-modal-script', 'modal_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce(self::AJAX_NONCE),
            ]);
        }
   public static function add_modal_html() {
        $screen = get_current_screen();
        if (!$screen) return;

        $order_id = 0;

        // HPOS
        if ($screen->id === 'woocommerce_page_wc-orders' && isset($_GET['id'])) {
            $order_id = intval($_GET['id']);
        }

        // Classic post.php
        if ($screen->post_type === 'shop_order') {
            global $post;
            if ($post && isset($post->ID)) {
                $order_id = intval($post->ID);
            }
        }

        // Nếu không lấy được order ID → không render modal (tránh lỗi)
        if (!$order_id) {
            return;
        }
        ?>
        <div id="config-modal" class="modal">
            <div class="modal-content">
                <span class="close-btn">&times;</span>

                <h3>Cấu Hình cho Đơn Hàng SuperShip#<?php echo $order_id; ?></h3>

                <div id="modal-body"></div>

                <div class="modal-footer">
                    <button id="modal-create-btn"
                            class="button button-primary"
                            data-order-id="<?php echo $order_id; ?>">
                        ✅ Tạo Đơn SuperShip
                    </button>

                    <button class="button close-btn">Đóng</button>
                </div>
            </div>
        </div>
        <?php
    }
    
    // --- AJAX HANDLERS ---
    
    /**
     * AJAX: Xử lý hiển thị form cấu hình trong Modal.
     */
    public static function handle_ajax_load_config_modal() {
        if (!check_ajax_referer(self::AJAX_NONCE, 'security', false) || !current_user_can('edit_shop_orders') || !isset($_POST['order_id'])) {
            wp_send_json_error(['message' => 'Lỗi bảo mật hoặc thiếu dữ liệu.']);
        }
        
        $order_id = intval($_POST['order_id']);
        $order = wc_get_order($order_id);
        
        if (!$order) {
            wp_send_json_error(['message' => 'Đơn hàng không tồn tại.']);
        }

        $data = self::get_modal_form_data($order_id, $order);
        
        ob_start();
        self::render_config_form($data, $order);
        $html_content = ob_get_clean();

        wp_send_json_success(['html' => $html_content]);
    }

    /**
     * AJAX: Xử lý tạo đơn hàng.
     */
    public static function handle_ajax_create_order() {
        if (!check_ajax_referer(self::AJAX_NONCE, 'security', false) || !current_user_can('edit_shop_orders') || !isset($_POST['order_id']) || !isset($_POST['config_data'])) {
            wp_send_json_error(['message' => 'Lỗi bảo mật hoặc thiếu dữ liệu.']);
        }

        $order_id = intval($_POST['order_id']);
        $config_data = $_POST['config_data']; 
        $wc_order = wc_get_order($order_id);
         
        if (!$wc_order) {
            wp_send_json_error(['message' => 'Đơn hàng không tồn tại!']);
        }
        
        if (!class_exists('WC_Custom_Fields') || !class_exists('Order_Creation_Handler')) {
             wp_send_json_error(['message' => 'Lớp xử lý nghiệp vụ hoặc Data Layer không tồn tại.']);
        }

        // 1. LƯU CẤU HÌNH TRƯỚC
        self::save_config_from_modal($order_id, $config_data);

        // 2. TẠO ĐƠN HÀNG (Gọi Handler nghiệp vụ)
        $result = Order_Creation_Handler::create_supership_order($wc_order);

        // 3. XỬ LÝ KẾT QUẢ
        if ($result['success']) {
            WC_Custom_Fields::save_field($order_id, 'order_code', $result['code']);
            $wc_order->add_order_note(sprintf('Đã tạo đơn SuperShip thành công. Mã đơn: %s', $result['code']));
            $wc_order->update_status(
                'processing',
                sprintf('Đơn SuperShip đã tạo thành công. Mã đơn: %s', $result['code'])
            );
            wp_send_json_success([
                'message' => '🎉 Tạo đơn SuperShip thành công!',
                'code' => $result['code'],
                'redirect_url' => admin_url('post.php?post=' . $order_id . '&action=edit&success=1')
            ]);
        } else {
            $wc_order->add_order_note(sprintf('Tạo đơn SuperShip thất bại. Lỗi: %s', $result['message']));
            wp_send_json_error([
                'message' => '❌ Tạo đơn thất bại!',
                'error_detail' => esc_html($result['message']),
                'raw_details' => $result['details']
            ]);
        }
    }

    // --- HELPER METHODS ---

    /**
     * Lấy dữ liệu cần thiết để điền vào Modal Form.
     */
    private static function get_modal_form_data($order_id, $order) {
        $data = [
            'pickup_code' => WC_Custom_Fields::get_field($order_id, 'pickup_code'),
            'config' => WC_Custom_Fields::get_field($order_id, 'config') ?: 1,
            'payer' => WC_Custom_Fields::get_field($order_id, 'payer') ?: 1,
            'service' => WC_Custom_Fields::get_field($order_id, 'service') ?: 1,
            'barter' => WC_Custom_Fields::get_field($order_id, 'barter') ?: '',
        ];
        $warehouses = class_exists('Warehouses_Service') ? Warehouses_Service::get_all() : [];
        $default_warehouse = class_exists('Warehouses_Service') ? Warehouses_Service::get_default() : null;
        $default_pickup_code = ($default_warehouse && isset($default_warehouse['code'])) ? $default_warehouse['code'] : '';
        // Ưu tiên giá trị đã lưu, nếu chưa có thì lấy default
        $data['current_pickup_code'] = empty($data['pickup_code']) ? $default_pickup_code : $data['pickup_code'];
        $data['warehouses'] = $warehouses;
        $data['default_pickup_code'] = $default_pickup_code;
        $data['is_order_created'] = !empty(WC_Custom_Fields::get_field($order_id, 'order_code'));
        $data['disabled_attr'] = $data['is_order_created'] ? 'disabled' : '';
        return $data;
    }

    /**
     * Render HTML Form Modal (Chỉ giữ lại 5 trường cấu hình SuperShip)
     */
    private static function render_config_form($data, $order) {
        extract($data); // Lấy biến từ mảng $data
        ?>
        <div class="config-form">
            <div class="row">
                <label>Kho Hàng:</label>
                <select name="select_pickup_code" <?php echo $disabled_attr; ?>>
                    <option value="">-- Mặc định: <?php echo esc_html($default_pickup_code ?: 'CHƯA CÓ'); ?> --</option>
                    <?php 
                    foreach ($warehouses as $w):
                        $is_selected = ($current_pickup_code === $w['code']) ? "selected" : "";
                        $is_default_label = (isset($w['primary']) && $w['primary'] == "1") ? " (Mặc định)" : "";
                    ?>
                        <option value="<?= esc_attr($w['code']) ?>" <?= $is_selected ?>><?= esc_html($w['name']) ?> (<?= esc_html($w['code']) ?>) <?= $is_default_label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="row"><label>Cho Xem/Thử Hàng:</label><select name="config" <?php echo $disabled_attr; ?>>
                <option value="1" <?php selected($config, 1); ?>>Cho Xem Hàng Nhưng Không Cho Thử Hàng</option>
                <option value="2" <?php selected($config, 2); ?>>Cho Thử Hàng</option>
                <option value="3" <?php selected($config, 3); ?>>Không Cho Xem Hàng</option>
            </select></div>
            
            <div class="row"><label>Người Trả Phí:</label><select name="payer" <?php echo $disabled_attr; ?>>
                <option value="1" <?php selected($payer, 1); ?>>Người Gửi</option>
                <option value="2" <?php selected($payer, 2); ?>>Người Nhận</option>
            </select></div>
            
            <div class="row"><label>Gói Dịch Vụ:</label><select name="service" <?php echo $disabled_attr; ?>>
                <option value="1" <?php selected($service, 1); ?>>Tốc Hành</option>
            </select></div>
            
            <div class="row"><label>Đổi/Lấy Hàng Về:</label><select name="barter" <?php echo $disabled_attr; ?>>
                <option value="" <?php selected($barter, ''); ?>>Không</option>
                <option value="1" <?php selected($barter, 1); ?>>Có</option>
            </select></div>
        </div>
        <?php
    }

    /**
     * Hàm lưu dữ liệu cấu hình từ Modal vào Meta Fields
     */
    private static function save_config_from_modal($order_id, $config_data) {
        // Lưu cấu hình SuperShip
        WC_Custom_Fields::save_field($order_id, 'pickup_code', sanitize_text_field($config_data['select_pickup_code'] ?? ''));
        WC_Custom_Fields::save_field($order_id, 'config', intval($config_data['config'] ?? 1));
        WC_Custom_Fields::save_field($order_id, 'payer', intval($config_data['payer'] ?? 1));
        WC_Custom_Fields::save_field($order_id, 'service', intval($config_data['service'] ?? 1));
        WC_Custom_Fields::save_field($order_id, 'barter', sanitize_text_field($config_data['barter'] ?? ''));
    }
}
Admin_UI::init();