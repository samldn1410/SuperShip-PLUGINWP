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
        // Lấy trạng thái cũ từ supership_orders
        $old_status = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT status_name FROM {$order_table} WHERE supership_code = %s",
                $code
            )
        );
        $new_status = $data['status_name'] ?? '';
        // Lưu log webhook vào database
       $wpdb->insert(
                $table,
                [
                    'supership_code' => $data['code'] ?? '',
                    'shortcode'      => $data['shortcode'] ?? '',
                    'type'           => $data['type'] ?? '',
                    'status_name'    => $data['status_name'] ?? '',
                    'reason'         => $data['reason_text'] ?? '',
                    'raw_data'       => json_encode($data, JSON_UNESCAPED_UNICODE),
                    'log_message'    => 'Webhook update: Đơn hàng chuyển từ trạng thái "' .
                                        ($old_status ?: 'N/A') . '" sang "' .
                                        ($data['status_name'] ?? 'N/A') . '"'
                ]
            );
        // Đồng thời update trạng thái bản ghi trong bảng supership_orders
       

        $wpdb->update(
            $order_table,
            [
                'status_name' => $data['status_name'] ?? '',
                'updated_at'  => current_time('mysql')
            ],
            ['supership_code' => $data['code']]
        );
        // Trả về để SuperShip biết là webhook thành công
        return [
            'status' => 'OK',
            'message' => 'Webhook received'
        ];
    }
}
Webhook_Handler::init();
