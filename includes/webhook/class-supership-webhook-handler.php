<?php
if (!defined('ABSPATH')) exit;
class Webhook_Handler {

    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'register_endpoint']);
    }
    public static function register_endpoint(){
        register_rest_route('supership/v1', '/webhook', [
            'methods'  => 'POST',
            'callback' => [__CLASS__, 'handle'],
            'permission_callback' => '__return_true'
        ]);
    }
    public static function handle($request) {
        $data = $request->get_json_params();
        // Log webhook raw data
        error_log("=== SuperShip Webhook Received ===");
        error_log(json_encode($data, JSON_UNESCAPED_UNICODE));
        global $wpdb;
        $table = $wpdb->prefix . 'supership_webhook_logs';
        $code = $data['code'] ?? '';
        $order_table = $wpdb->prefix . 'supership_orders';
        if (empty($code)) {
        return new WP_REST_Response(['status' => 'OK'], 200);
         }
         $order = $wpdb->get_row(
            $wpdb->prepare(
                    "SELECT * FROM {$order_table} WHERE supership_code = %s",
                    $code
            ),
                ARRAY_A
         );

        if (!$order) {
                return new WP_REST_Response(['status' => 'OK'], 200);
        }
        $update_data = [
        'supership_shortcode' => $data['shortcode'] ?? $order['supership_shortcode'],
        'supership_soc'       => $data['soc'] ?? $order['supership_soc'],
        'receiver_name'       => $data['name'] ?? $order['receiver_name'],
        'receiver_phone'      => $data['phone'] ?? $order['receiver_phone'],
        'receiver_address'    => $data['address'] ?? $order['receiver_address'],
        'amount'              => intval($data['amount'] ?? $order['amount']),
        'weight'              => intval($data['weight'] ?? $order['weight']),
        'fee'                 => intval($data['fshipment'] ?? $order['fee']),
        'insurance'           => intval($data['finsurance'] ?? $order['insurance']),
        'status_name'         => $data['status_name'] ?? $order['status_name'],
        'partial'             => $data['partial'] ?? $order['partial'],
        'barter'              => $data['barter'] ?? $order['barter'],
        'raw_response'        => json_encode($data, JSON_UNESCAPED_UNICODE),
        'updated_at'          => current_time('mysql')
        ];
        $wpdb->update(
            $order_table,
            $update_data,
            ['supership_code' => $code]
        );
        $type = $data['type'] ?? '';
        $log_message ="Webhook log";
        if($type =="update_status")
        {
            $old_status = $order['status_name'] ?? 'N/A';
            $new_status = $data['status_name'] ?? '';
            $log_message = 'Webhook update: Đơn hàng chuyển từ trạng thái "' .
                   $old_status . '" sang "' .
                   $new_status . '"';
            if (!empty($data['reason_text'])) {
            $log_message .= ' | Lý do: ' . $data['reason_text'];
        }

        }else{
            $old_weight = isset($order['weight']) ? intval($order['weight']) : 0;
            $new_weight = isset($data['weight']) ? intval($data['weight']) : $old_weight;

             $log_message .= ' Webhook update: Đơn hàng đã thay đổi khối lượng: ' .
                    $old_weight . 'g → ' .
                    $new_weight . 'g';
        }
        $wpdb->insert(
                    $table,
                    [
                        'supership_code' => $data['code'] ?? '',
                        'shortcode'      => $data['shortcode'] ?? '',
                        'type'           => $data['type'] ?? '',
                        'status_name'    => $data['status_name'] ?? '',
                        'reason'         => $data['reason_text'] ?? '',
                        'raw_data'       => json_encode($data, JSON_UNESCAPED_UNICODE),
                        'log_message'    => $log_message,
                    ]
        );

        if (!empty($order['wp_order_id'])) {
            $wc_status = self::supership_name_to_wc_status($data['status_name']);
            $wc_order = wc_get_order($order['wp_order_id']);
            if ($wc_order) {
                $wc_order->add_order_note('[SuperShip] ' . $log_message);
            }
            if ($wc_order && $wc_order->get_status() !== $wc_status) {
                $wc_order->update_status(
                    $wc_status );
            }
        }
        return [
            'status' => 'OK',
            'message' => 'Webhook received'
        ];
    }

   public static function supership_name_to_wc_status(string $status_name): string
    {
        $status_name = trim($status_name);

        $map = [
            'Chờ Duyệt'                 => 'on-hold',
            'Chờ Lấy Hàng'              => 'processing',
            'Đang Lấy Hàng'             => 'processing',
            'Đã Lấy Hàng'               => 'processing',
            'Đang Nhập Kho'             => 'processing',
            'Đã Nhập Kho'               => 'processing',
            'Đang Chuyển Kho Giao'      => 'processing',
            'Đã Chuyển Kho Giao'        => 'processing',
            'Đang Giao Hàng'            => 'processing',
            'Đang Vận Chuyển'           => 'processing',
            
            'Hoãn Lấy Hàng'             => 'processing',
            'Hoãn Giao Hàng'            => 'processing',
            'Hoãn Trả Hàng'             => 'processing',
            'Đang Chuyển Kho Trả'       => 'processing',
            'Đã Chuyển Kho Trả'         => 'processing',
            'Đang Trả Hàng'             => 'processing',
            
            'Đã Giao Hàng Một Phần'     => 'completed',
            'Đã Giao Hàng Toàn Bộ'      => 'completed',
            'Đã Đối Soát Giao Hàng'     => 'completed',

            'Đã Trả Hàng'               => 'refunded',
            'Xác Nhận Hoàn'             => 'refunded',
            'Đã Đối Soát Trả Hàng'      => 'refunded',
            'Đã Bồi Hoàn'               => 'refunded',

            'Huỷ'                       => 'cancelled',
            'Không Lấy Được'            => 'failed',
            'Không Giao Được'           => 'failed',
            'Hàng Thất Lạc'             => 'failed',
            'Không Trả Được'            => 'failed',
        ];

        return $map[$status_name] ?? 'processing';
    }
}
Webhook_Handler::init();
