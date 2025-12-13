<?php
if (!defined('ABSPATH')) exit;
class Admin_UI {

    const AJAX_NONCE = 'modal_nonce';
    
    public static function init() {
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
        add_action('admin_footer', [__CLASS__, 'add_modal_html']);
        add_action('admin_init', function() {
        remove_action(
                'woocommerce_order_item_add_action_buttons',
                'woocommerce_order_item_add_action_buttons',
                10
            );
        });
        add_action('wp_ajax_preview_shipping_fee', [__CLASS__, 'ajax_preview_shipping_fee']);

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
            $result = Order_Service::get_order_info($code);
            if ($result['status'] === 'Success') {
                Order_Service::update_supership_order_info($order_id, $result);
                wp_send_json_success(['message' => 'Cập nhật thông tin đơn thành công!']);
            }
            wp_send_json_error(['message' => 'Không cập nhật được thông tin']);
        });
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

            wp_enqueue_script(
            'sweetalert2',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js',
            [],
            '11',
            true
        );

        wp_enqueue_style(
            'sweetalert2-css',
            'https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css',
            [],
            '11'
        );
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

                <h3>Cấu Hình cho Đơn Hàng SuperShip</h3>

                <div id="modal-body"></div>

               <div class="modal-footer">
                    <button class="button close-btn-footer">Đóng</button>

                    <button id="modal-create-btn"
                            class="button create-order-btn"
                            data-order-id="<?php echo $order_id; ?>">
                        Tạo Đơn
                    </button>
                </div>
            </div>
        </div>
        <?php
    }
    
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
        self::save_config_from_modal($order_id, $config_data);
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
                'message' => ' Tạo đơn thất bại!',
                'error_detail' => esc_html($result['message']),
                'raw_details' => $result['details']
            ]);
        }
    }
 
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
        // $data['disabled_attr'] = $data['is_order_created'] ? 'disabled' : '';
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
            <label><?php esc_html_e('Kho Hàng', 'supership'); ?>:</label>
            <select name="select_pickup_code" <?php echo $disabled_attr; ?>>
                <option value="">
                    <?php
                    printf(
                        esc_html($default_pickup_code ?: __('CHƯA CÓ', 'supership'))
                    );
                    ?>
                </option>
                <?php 
                foreach ($warehouses as $w):
                    $is_selected = ($current_pickup_code === $w['code']) ? "selected" : "";
                    $is_default_label = (isset($w['primary']) && $w['primary'] == "1")
                        ? ' (' . esc_html__('Mặc định', 'supership') . ')'
                        : '';
                ?>
                    <option value="<?php echo esc_attr($w['code']); ?>" <?php echo $is_selected; ?>>
                        <?php echo esc_html($w['name']); ?>
                        – <?php echo esc_html($w['formatted_address']); ?>
                        <?php echo $is_default_label; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="row">
            <label><?php esc_html_e('Người liên hệ', 'supership'); ?>:</label>
            <div class="static-field">
                <strong>Nguyễn Văn A</strong>
                <span class="sep">|</span>
                <span class="phone">0335 585 567</span>
            </div>
        </div>
        <div class="row">
            <label><?php esc_html_e('Xem / Thử Hàng', 'supership'); ?>:</label>
            <select name="config" <?php echo $disabled_attr; ?>>
                <option value="1" <?php selected($config, 1); ?>>
                    <?php esc_html_e('Cho xem hàng nhưng không cho thử hàng', 'supership'); ?>
                </option>
                <option value="2" <?php selected($config, 2); ?>>
                    <?php esc_html_e('Cho thử hàng', 'supership'); ?>
                </option>
                <option value="3" <?php selected($config, 3); ?>>
                    <?php esc_html_e('Không cho xem hàng', 'supership'); ?>
                </option>
            </select>
        </div>
        
        <div class="row">
            <label><?php esc_html_e('Người Trả Phí', 'supership'); ?>:</label>
            <select name="payer" <?php echo $disabled_attr; ?>>
                <option value="1" <?php selected($payer, 1); ?>>
                    <?php esc_html_e('Người gửi', 'supership'); ?>
                </option>
                <option value="2" <?php selected($payer, 2); ?>>
                    <?php esc_html_e('Người nhận', 'supership'); ?>
                </option>
            </select>
        </div>
        
        <div class="row">
            <label><?php esc_html_e('Đổi / Lấy Hàng Về', 'supership'); ?>:</label>
                <label style="font-weight: normal; cursor: pointer; ">
                    <input type="checkbox"
                        id="barter_checkbox"
                        value="1"
                        <?php checked($barter, 1); ?>
                        <?php echo $disabled_attr; ?>>
                    <?php esc_html_e('Có đổi / lấy hàng về', 'supership'); ?>
                </label>
            </div>

    <div id="barter_extra">

    <!-- LƯU Ý – full width -->
    <div class="barter-note">
        <strong><?php esc_html_e('Lưu ý:', 'supership'); ?></strong>
            <?php esc_html_e(
                'Bạn đã chọn đổi / lấy hàng về, vui lòng ghi rõ nội dung đổi (ví dụ: “Đổi về 2 áo”) trong ô Ghi chú khi giao.',
                'supership'
            ); ?>
        </div>

        <!-- Ghi chú giao hàng – vẫn là row -->
        <div class="row">
            <label><?php esc_html_e('Ghi chú giao hàng', 'supership'); ?>:</label>
            <textarea
                name="delivery_note"
                id="delivery_note"
                rows="3"
                placeholder="<?php esc_attr_e('Ví dụ: Đổi về 2 áo size M', 'supership'); ?>"
                <?php echo $disabled_attr; ?>
            ><?php echo esc_textarea($delivery_note ?? ''); ?></textarea>
        </div>

    </div>
    <div class="shipping-info">
        <div id="shipping_preview">
            <?php esc_html_e('Vui lòng chọn kho để xem phí...', 'supership'); ?>
        </div>
    </div>
    </div>
<?php
}
    private static function save_config_from_modal($order_id, $config_data) {
        WC_Custom_Fields::save_field($order_id, 'pickup_code', sanitize_text_field($config_data['select_pickup_code'] ?? ''));
        WC_Custom_Fields::save_field($order_id, 'config', intval($config_data['config'] ?? 1));
        WC_Custom_Fields::save_field($order_id, 'payer', intval($config_data['payer'] ?? 1));
        WC_Custom_Fields::save_field($order_id, 'service', intval($config_data['service'] ?? 1));
        WC_Custom_Fields::save_field($order_id, 'barter', sanitize_text_field($config_data['barter'] ?? ''));
    }

    public static function ajax_preview_shipping_fee() {
        check_ajax_referer(self::AJAX_NONCE, 'security');
        $order_id    = intval($_POST['order_id'] ?? 0);
        $pickup_code = sanitize_text_field($_POST['pickup_code'] ?? '');
        if (!$order_id || !$pickup_code) {
            wp_send_json_error(['message' => 'Thiếu dữ liệu']);
        }
        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error(['message' => 'Order không tồn tại']);
        }
        $warehouses = Warehouses_Service::get_all();
        $warehouse  = null;
        foreach ($warehouses as $w) {
            if ($w['code'] === $pickup_code) {
                $warehouse = $w;
                break;
            }
        }
        if (!$warehouse || empty($warehouse['formatted_address'])) {
            wp_send_json_error(['message' => 'Không tìm thấy địa chỉ kho']);
        }
        $address_parts = array_map('trim', explode(',', $warehouse['formatted_address']));
        $from_province = end($address_parts);
        $from_district = $address_parts[count($address_parts) - 2] ?? '';
        if (!$from_province || !$from_district) {
            wp_send_json_error(['message' => 'Không tách được địa chỉ kho']);
        }
        $to_province = 'Tỉnh Bình Định';
        $to_district = 'Huyện Tuy Phước';
        if (!class_exists('Order_Creation_Handler')) {
            wp_send_json_error(['message' => 'Thiếu Order_Creation_Handler']);
        }
        $product_info = Order_Creation_Handler::calculate_product_weight($order);
        $weight = intval($product_info['weight_gram'] ?? 0);
        if ($weight <= 0) {
            wp_send_json_error(['message' => 'Không tính được trọng lượng']);
        }
        $result = Order_Service::get_shipping_price(
            $from_province,
            $from_district,
            $to_province,
            $to_district,
            $weight,
            intval($order->get_total())
        );
        if ($result['status'] !== 'Success') {
            wp_send_json_error(['message' => $result['message'] ?? 'Không tính được phí']);
        }
        wp_send_json_success([
            'fee'       => number_format($result['fee']) . ' đ',
            'insurance' => number_format($result['insurance']) . ' đ',
            'pickup'    => $result['pickup'],
            'delivery'  => $result['delivery'],
        ]);
    }
}
Admin_UI::init();