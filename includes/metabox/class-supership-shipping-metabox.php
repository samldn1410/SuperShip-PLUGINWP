<?php
if (!defined('ABSPATH')) exit;

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

class SuperShip_Shipping_MetaBox {

    public static function init() {
        add_action('add_meta_boxes', [__CLASS__, 'register_metabox']);
        // add_action('add_meta_boxes', [__CLASS__, 'register_shipping_fee_boxes'], 10, 2);
        add_action('add_meta_boxes', [__CLASS__, 'remove_default_boxes'], 20);
        add_action('woocommerce_process_shop_order_meta', [__CLASS__, 'save_number_id'], 20);
        add_action('add_meta_boxes', function() {
            remove_meta_box('woocommerce-order-actions', wc_get_page_screen_id('shop-order'), 'side');
        });
         add_action('add_meta_boxes', [__CLASS__, 'register_config_metabox'], 10, 2);
        add_action('add_meta_boxes', [__CLASS__, 'register_journey_and_note_metabox'], 10, 2);
        add_action('admin_enqueue_scripts', function($hook) {
                if ($hook !== 'post.php' && $hook !== 'post-new.php') return;
                if (get_post_type() !== 'shop_order') return;
                wp_enqueue_style(
                    'supership-metabox-style',
                    URL . 'assets/css/supership-metabox.css',
                    [],
                    time()
                );
            });
    }
    private static function has_supership_order($order_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'supership_orders';

        $exists = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM $table WHERE wp_order_id = %d", $order_id)
        );
           error_log("SuperShip DEBUG - Order ID: $order_id - Exists: $exists");
        return $exists > 0;
    }
    private static function get_current_order_id($post) {

        // Classic editor
        if ($post instanceof WP_Post && $post->ID) {
            return $post->ID;
        }

        // HPOS (wc-orders)
        if (!empty($_GET['id'])) {
            return absint($_GET['id']);
        }

        if (!empty($_GET['order_id'])) {
            return absint($_GET['order_id']);
        }

        if (!empty($_GET['post'])) {
            return absint($_GET['post']);
        }

        return 0;
    }

    private static function get_screen_id() {
        $is_hpos = class_exists(CustomOrdersTableController::class)
            && wc_get_container()->get(CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled();
        return $is_hpos
            ? wc_get_page_screen_id('shop-order')
            : 'shop_order';
    }
    public static function remove_default_boxes() {
        $screen = self::get_screen_id();
        remove_meta_box('woocommerce-order-attribution', $screen, 'side');
    }
    public static function register_metabox() {
        $screen = self::get_screen_id();
        add_meta_box(
            'shipping_metabox',
            __('Thông tin vận chuyển', 'supership'),
            [__CLASS__, 'render_metabox'],
            $screen,
            'side',
            'high'
        );
    }
    public static function register_shipping_fee_boxes($post_type, $post)
    {
        $order_id = self::get_current_order_id($post);
        if (!$order_id) return;
        if (self::has_supership_order($order_id)) return;
        $screen = self::get_screen_id();
        add_meta_box(
            'shipping_fee',
            __('Cước phí đơn hàng', 'supership'),
            [__CLASS__, 'render_shipping_price_metabox'],
            $screen,
            'side',
            'high'
        );
    }
    public static function register_config_metabox($post_type, $post) {
       $order_id = self::get_current_order_id($post);

        if (!$order_id) return;
        if (!self::has_supership_order($order_id)) return;
        $screen = self::get_screen_id();
        add_meta_box(
            'shipping_config_metabox',
            __('Cấu hình vận đơn', 'supership'),
            [__CLASS__, 'render_config_metabox'],
            $screen,
            'side',
            'high'
        );
    }

    public static function register_journey_and_note_metabox($post_type, $post) {
         $order_id = self::get_current_order_id($post);
        if (!$order_id) return;
        if (!self::has_supership_order($order_id)) return;

        $screen = self::get_screen_id();

        add_meta_box(
            'order_journey',
            __('Hành trình vận đơn', 'supership'),
            [__CLASS__, 'render_journey_metabox'],
            $screen,
            'side',
            'high'
        );
    }
    /**
     * Render nội dung box
     */
    public static function render_metabox($post) {
        global $wpdb;
        $order_id = $post->ID;
        $table = $wpdb->prefix . 'supership_orders';

        // Lấy dữ liệu từ DB plugin
        $data = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE wp_order_id = %d", $order_id),
            ARRAY_A
        );

        echo '<div class="box">';

        if (!$data) {
            echo "
            <div style='text-align:center; margin-top:10px;'>
                <button 
                    type='button' 
                    class='create-order-btn'
                    id='create-order-modal-btn'
                    data-order-id='{$order_id}'
                >
                    " . esc_html__('Tạo đơn SuperShip', 'supership') . "
                </button>
            </div>
            ";
            echo "</div>";
            return;
        }
        $tracking = $data['supership_code'];
        $trackingUrl = "https://tracking.supership.vn/?code={$tracking}";
        $js_trackingUrl = esc_url($trackingUrl);

        $status = strtolower(trim($data['status_name']));
        $is_canceled = in_array($status, ['hủy']);

        $badge_class = 'status-badge';
        if ($is_canceled) {
            $badge_class .= ' status-badge-cancel';
        }

        echo "<div class='row'>";
        echo "<span class='label'>" . esc_html__('Được giao bởi:', 'supership') . "</span>";
        echo "<span class='value'>SuperShip</span>";
        echo "</div>";

        echo "<div class='row'>";
        echo "<span class='label'>" . esc_html__('Mã vận đơn:', 'supership') . "</span>";
        echo "<span class='value'>" . esc_html($data['supership_code']) . "</span>";
        echo "</div>";

        echo "<div class='row'>
            <span class='label'>" . esc_html__('Trạng thái vận đơn:', 'supership') . "</span>
            <span class='{$badge_class}'>" . esc_html($data['status_name']) . "</span>
        </div>";

        echo "<div class='divider-line'></div>";
        echo "<div class='action-buttons'>";

        /* Theo dõi đơn */
        echo "
        <button 
            type='button'
            class='action-btn track-btn'
            title='" . esc_attr__('Theo dõi đơn hàng', 'supership') . "'
            onclick=\"window.open('{$js_trackingUrl}', '_blank');\"
        >
            <i class='bi bi-truck'></i>
        </button>
        ";

        /* Làm mới */
        echo "
        <button 
            type='button'
            class='action-btn update-order-info'
            title='" . esc_attr__('Làm mới', 'supership') . "'
            data-order-id='{$order_id}'
        >
            <i class='bi bi-arrow-clockwise'></i>
        </button>
        ";

        /* Hủy đơn */
        $allow_cancel_states = ['chờ lấy hàng'];

        if (in_array($status, $allow_cancel_states)) {
            echo "
            <button 
                type='button'
                class='action-btn cancel-order'
                title='" . esc_attr__('Hủy đơn', 'supership') . "'
                data-order-id='{$order_id}'
                data-code='{$tracking}'
            >
                <i class='bi bi-x-lg'></i>
            </button>
            ";
        }

        echo "</div>";
        echo "</div>";
    }

    public static function render_config_metabox($post) {
        global $wpdb;

        $order_id = $post->ID;
        $table = $wpdb->prefix . 'supership_orders';

        $data = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT payer, service, barter, config, raw_response, insurance 
                FROM $table WHERE wp_order_id = %d",
                $order_id
            ),
            ARRAY_A
        );
        echo '<div class="box">';
        if (!$data) {
            return;
        }
        $payer_map = [
            '1' => __('Người gửi', 'supership'),
            '2' => __('Người nhận', 'supership'),
        ];
        $config_map = [
            '1' => __(' Cho Xem Hàng Nhưng Không Cho Thử Hàng', 'supership'),
            '2' => __('Cho Thử Hàng', 'supership'),
            '3' => __('Không Cho Xem Hàng', 'supership'),
        ];
        $service_map = [
            '1' => __('Tốc hành', 'supership'),
        ];
        echo "<div class='row'>
            <span class='label'>" . esc_html__('Người trả phí:', 'supership') . "</span>
            <span class='value'>" . esc_html($payer_map[$data['payer']] ?? $data['payer']) . "</span>
        </div>";
        echo "<div class='row'>
            <span class='label'>" . esc_html__('Xem/Thử Hàng:', 'supership') . "</span>
            <span class='value'>" . esc_html($config_map[$data['config']] ?? $data['config']) . "</span>
        </div>";;
        echo "<div class='row'>
            <span class='label'>" . esc_html__('Đổi / Lấy hàng về:', 'supership') . "</span>
            <span class='value'>" . esc_html(!empty($data['barter']) ? __('Có', 'supership') : __('Không', 'supership')) . "</span>
        </div>";
        echo "</div>";
    }
        public static function render_shipping_price_metabox($post) {
            $order_id = $post->ID;
            $result = self::get_price_preview_data($order_id);

            echo "<div class='box'>";

            if ($result['status'] !== 'Success') {
                echo "<p class='empty'>" . esc_html__($result['message'], 'supership') . "</p>";
                echo "</div>";
                return;
            }

            echo "
            <div class='row'>
                <span class='label'>" . esc_html__('Cước phí giao hàng:', 'supership') . "</span>
                <span class='value'>" . esc_html(number_format($result['fee'])) . " đ</span>
            </div>
            <div class='row'>
                <span class='label'>" . esc_html__('Cước phí bảo hiểm:', 'supership') . "</span>
                <span class='value'>" . esc_html(number_format($result['insurance'])) . " đ</span>
            </div>
            <div class='row'>
                <span class='label'>" . esc_html__('Thời gian dự kiến lấy hàng:', 'supership') . "</span>
                <span class='value'>" . esc_html($result['pickup']) . "</span>
            </div>
            <div class='row'>
                <span class='label'>" . esc_html__('Thời gian dự kiến giao hàng:', 'supership') . "</span>
                <span class='value'>" . esc_html($result['delivery']) . "</span>
            </div>
            ";

            echo "</div>";
        }
        public static function render_journey_metabox($post) {
            global $wpdb;
            
            $order_id = $post->ID;
            $table = $wpdb->prefix . 'supership_orders';

            $data = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT raw_response FROM $table WHERE wp_order_id = %d",
                    $order_id
                ),
                ARRAY_A
            );

            echo "<div class='box'>";

            if (!$data) {
                echo "<p class='empty'>" . esc_html__('Chưa có dữ liệu hành trình vận đơn.', 'supership') . "</p></div>";
                return;
            }

            $raw = json_decode($data['raw_response'], true);
            $journeys = $raw['results']['journeys'] ?? [];

            if (empty($journeys)) {
                echo "<p class='empty'>" . esc_html__('Không có lịch sử vận đơn.', 'supership') . "</p></div>";
                return;
            }

            echo "<div class='timeline'>";

            foreach ($journeys as $step) {
                $time     = !empty($step['time']) ? date("d/m/Y H:i", strtotime($step['time'])) : '';
                $status   = $step['status'] ?? '';
                $province = $step['province'] ?? '';
                $district = $step['district'] ?? '';
                $note     = $step['note'] ?? '';

                echo "
                <div class='timeline-item'>
                    <div class='timeline-point'></div>
                    <div class='timeline-content'>
                        <div class='time'>" . esc_html($time) . "</div>
                        <div class='status'>" . esc_html($status) . "</div>
                        <div class='location'>" . esc_html($district . ', ' . $province) . "</div>
                        " . (!empty($note)
                            ? "<div class='note'>" . esc_html($note) . "</div>"
                            : ""
                        ) . "
                    </div>
                </div>
                ";
            }

            echo "</div></div>";
        }


        public static function get_price_preview_data($order_id) {
            $order = wc_get_order($order_id);
            if (!$order) {
                return [
                    'status' => 'Error',
                    'message' => 'Order không tồn tại.'
                ];
            }
            if (!class_exists('Store_Address_Extended')) {
                return [
                    'status' => 'Error',
                    'message' => 'Chưa load Store_Address_Extended.'
                ];
            }
            $store = Store_Address_Extended::get_full_store_address();
            if (empty($store['province']) || empty($store['district'])) {
                return [
                    'status' => 'Error',
                    'message' => 'Chưa cấu hình tỉnh / quận cửa hàng.'
                ];
            }
            $receiver_province = 'Tỉnh Bình Định';
            $receiver_district = 'Huyện Tuy Phước';
            if (!$receiver_province || !$receiver_district) {
                return [
                    'status' => 'Error',
                    'message' => 'Thiếu địa chỉ người nhận.'
                ];
            }
            if (!class_exists('Order_Creation_Handler')) {
                return [
                    'status' => 'Error',
                    'message' => 'Không tìm thấy Order_Creation_Handler.'
                ];
            }
            $product_info = Order_Creation_Handler::calculate_product_weight($order);
            $weight = intval($product_info['weight_gram'] ?? 0);
            if ($weight <= 0) {
                return [
                    'status' => 'Error',
                    'message' => 'Không tính được trọng lượng đơn hàng.'
                ];
            }
            return Order_Service::get_shipping_price(
                $store['province'],
                $store['district'],
                $receiver_province,
                $receiver_district,
                $weight,
                intval($order->get_total())
            );
        }
}

SuperShip_Shipping_MetaBox::init();
