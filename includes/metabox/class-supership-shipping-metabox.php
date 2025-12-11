<?php
if (!defined('ABSPATH')) exit;

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;

class SuperShip_Shipping_MetaBox {

    public static function init() {

        // Thêm meta box
        add_action('add_meta_boxes', [__CLASS__, 'register_metabox']);

        // Xoá box mặc định
        add_action('add_meta_boxes', [__CLASS__, 'remove_default_boxes'], 20);

        // Lưu dữ liệu
        add_action('woocommerce_process_shop_order_meta', [__CLASS__, 'save_number_id'], 20);
        add_action('add_meta_boxes', [__CLASS__, 'register_config_metabox']);
        add_action('add_meta_boxes', function() {
            remove_meta_box('woocommerce-order-actions', wc_get_page_screen_id('shop-order'), 'side');
        });
        add_action('add_meta_boxes', [__CLASS__, 'register_journey_and_note_metabox']);
        add_action('admin_enqueue_scripts', function($hook) {

                // Chỉ load ở trang Woo Order Edit
                if ($hook !== 'post.php' && $hook !== 'post-new.php') return;

                // Chỉ load khi post type là shop_order
                if (get_post_type() !== 'shop_order') return;

                wp_enqueue_style(
                    'supership-metabox-style',
                    URL . 'assets/css/supership-metabox.css',
                    [],
                    time()
                );
            });
    }

    private static function get_screen_id() {

        $is_hpos = class_exists(CustomOrdersTableController::class)
            && wc_get_container()->get(CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled();

        return $is_hpos
            ? wc_get_page_screen_id('shop-order')
            : 'shop_order';
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
    public static function register_journey_and_note_metabox() {
        $screen = self::get_screen_id(); // shop_order

        // Metabox: Hành Trình Vận Đơn
        add_meta_box(
            'order_journey',
            __('Hành Trình Vận Đơn', 'supership'),
            [__CLASS__, 'render_journey_metabox'],
            $screen,
            'side',
            'default'
        );

        // Metabox: Ghi Chú Đơn Hàng
        add_meta_box(
            'order_note',
            __('Ghi Chú Đơn Hàng', 'supership'),
            [__CLASS__, 'render_note_metabox'],
            $screen,
            'side',
            'default'
        );
    }


    public static function register_config_metabox() {
        $screen = self::get_screen_id();

        add_meta_box(
            'shipping_config_metabox',
            __('Cấu hình vận đơn', 'supership'),
            [__CLASS__, 'render_config_metabox'],
            $screen,
            'side',
            'default'
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
                <p style='color:#888;'> Đơn hàng chưa được đẩy sang SuperShip.</p>
                <p>Hãy tạo đơn tại mục <strong>SuperShip Order</strong> phía trên.</p>
            ";
            echo '</div>';
            return;
        }

        $tracking = $data['supership_code'];
        $trackingUrl = "https://tracking.supership.vn/?code={$tracking}";

        echo "<div class='row'>
                <strong>Mã vận đơn: </strong>&nbsp;&nbsp; " . esc_html($data['supership_code']) . "
            </div>";

        echo "<div class='row'>
                <strong>Shortcode: </strong>&nbsp;&nbsp; " . esc_html($data['supership_shortcode']) . "
            </div>";

        echo "<div class='row'>
                <strong>Trạng thái: </strong>&nbsp;&nbsp; <span class='status-badge'>" . esc_html($data['status_name']) . "</span>
            </div>";

        echo "<div class='row'>
                <strong>Trọng lượng: </strong>&nbsp;&nbsp; " . number_format($data['weight']) . " gr
            </div>";

        echo "<div class='row'>
                <strong>COD: </strong>&nbsp;&nbsp; " . number_format($data['amount']) . " đ
            </div>";

        echo "<div class='row'>
                <strong>Phí ship: </strong>&nbsp;&nbsp; " . number_format($data['fee']) . " đ
            </div>";

        echo "<div class='row'>
                <strong>Phí bảo hiểm: </strong> &nbsp;&nbsp;" . number_format($data['insurance']) . " đ
            </div>";
        echo "<a class='track-btn' href='". esc_url($trackingUrl) ."' target='_blank'>
                🔎 Theo dõi đơn hàng
              </a>";

        echo "</div>";
    }

    public static function render_config_metabox($post) {
            global $wpdb;

            $order_id = $post->ID;
            $table = $wpdb->prefix . 'supership_orders';

            $data = $wpdb->get_row(
                $wpdb->prepare("SELECT payer, service, barter, config, raw_response, insurance 
                                FROM $table WHERE wp_order_id = %d", $order_id),
                ARRAY_A
            );

            echo '<div class="box">';

            if (!$data) {
                echo "<p style='color:#888;'>Chưa có cấu hình vì đơn hàng chưa gửi sang SuperShip.</p>";
                echo '</div>';
                return;
            }
            $payer_map = [
                '1' => 'Người Gửi',
                '2' => 'Người Nhận'
            ];

            $config_map = [
                '1' => 'Cho xem hàng (Không thử)',
                '2' => 'Cho thử hàng',
                '3' => 'Không cho xem hàng'
            ];

            $service_map = [
                '1' => 'Tốc Hành',
            ];

            echo "<div class='row'>
                    <strong class='label'>Người trả phí: </span>&nbsp;&nbsp;
                    <span class='value'>" . ($payer_map[$data['payer']] ?? $data['payer']) . "</span>
                </div>";

            echo "<div class='row'>
                    <strong class='label'>Cho xem hàng: </span>&nbsp;&nbsp;
                    <span class='value'>" . ($config_map[$data['config']] ?? $data['config']) . "</span>
                </div>";

            echo "<div class='row'>
                    <strong class='label'>Gói dịch vụ: </span>&nbsp;&nbsp;
                    <span class='value'>" . ($service_map[$data['service']] ?? $data['service']) . "</span>
                </div>";

            echo "<div class='row'>
                    <strong class='label'>Đổi/Lấy Hàng về: </span>&nbsp;&nbsp;
                    <span class='value'>" . (!empty($data['barter']) ? 'Có' : 'Không có') . "</span>
                </div>";

            echo "</div>";
        }

        public static function render_journey_metabox($post) {
                global $wpdb;
                
                $order_id = $post->ID;
                $table = $wpdb->prefix . 'supership_orders';

                $data = $wpdb->get_row(
                    $wpdb->prepare("SELECT raw_response FROM $table WHERE wp_order_id = %d", $order_id),
                    ARRAY_A
                );

                echo "<div class='box'>";

                if (!$data) {
                    echo "<p style='color:#888;'>Chưa có dữ liệu hành trình vận đơn.</p></div>";
                    return;
                }

                $raw = json_decode($data['raw_response'], true);

                $journeys = $raw['results']['journeys'] ?? [];

                if (empty($journeys)) {
                    echo "<p style='color:#888;'>Không có lịch sử vận đơn.</p>";
                    echo "</div>";
                    return;
                }

                foreach ($journeys as $step) {
                    $time     = date("d/m/Y H:i", strtotime($step['time']));
                    $status   = $step['status'] ?? '';
                    $province = $step['province'] ?? '';
                    $district = $step['district'] ?? '';
                    $note     = $step['note'] ?? '';

                    echo "
                    <div class='journey-item'>
                        <div class='journey-time'>$time</div>
                        <div class='journey-status'><strong>$status</strong></div>
                        <div class='journey-location'>$district, $province</div>
                        <div class='journey-note'>$note</div>
                    </div>
                    <hr>
                    ";
                }

                echo "</div>";
            }

            public static function render_note_metabox($post) {
                global $wpdb;
                
                $order_id = $post->ID;
                $table = $wpdb->prefix . 'supership_orders';

                // Lấy dữ liệu notes trực tiếp từ DB
                $data = $wpdb->get_row(
                    $wpdb->prepare("SELECT notes FROM $table WHERE wp_order_id = %d", $order_id),
                    ARRAY_A
                );

                echo "<div class='box'>";

                if (!$data) {
                    echo "<p style='color:#888;'>Không có ghi chú.</p></div>";
                    return;
                }

                $notes = trim($data['notes'] ?? '');

                if ($notes !== '') {

                    // Xử lý JSON nếu notes đang lưu dạng JSON ARRAY
                    $decoded = json_decode($notes, true);

                    // Nếu là array (nhiều ghi chú)
                    if (is_array($decoded)) {
                        foreach ($decoded as $note) {

                            $time  = isset($note['time']) ? date("d/m/Y H:i", strtotime($note['time'])) : "";
                            $text  = $note['note'] ?? '';

                            echo "
                            <div class='row'>
                                <span class='label'>$time</span>
                                <span class='value'>$text</span>
                            </div>";
                        }
                    } 
                    else {
                        // Nếu chỉ là text bình thường
                        echo "<div class='row'>
                                <span class='label'>Ghi chú:</span>
                                <span class='value'>" . nl2br(esc_html($notes)) . "</span>
                            </div>";
                    }

                } else {
                    echo "<p style='color:#888;'>Không có ghi chú từ phía đơn hàng.</p>";
                }

                echo "</div>";
            }

    
    // public static function save_number_id($order_id) {
    //     if (isset($_POST['number_id'])) {
    //         $order = wc_get_order($order_id);
    //         $order->update_meta_data('number_id', sanitize_text_field($_POST['number_id']));
    //         $order->save();
    //     }
    // }

    /**
     * Xoá box mặc định của WooCommerce
     */
    public static function remove_default_boxes() {

        $screen = self::get_screen_id();

        remove_meta_box('woocommerce-order-attribution', $screen, 'side');
    }
}

SuperShip_Shipping_MetaBox::init();
