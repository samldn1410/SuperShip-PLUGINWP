<?php
if (!defined('ABSPATH')) exit;

class Admin_Create_Order {

    public static function init() {
        add_action('admin_init', [__CLASS__, 'handle']);
        add_action('wp_ajax_create_order_ajax', [__CLASS__, 'handle_ajax_creation']);
    }

    public static function handle() {
        if (!isset($_POST['create_order']) || !wp_verify_nonce($_POST['nonce'], 'create_order')) {
            return;
        }
        $receiver_codes = [
            'province_code' => sanitize_text_field($_POST['province'] ?? ''), // CODE
            'district_code' => sanitize_text_field($_POST['district'] ?? ''), // CODE
            'commune_code' => sanitize_text_field($_POST['commune'] ?? ''), // CODE
        ];

        if (!$receiver_codes['province_code'] || !$receiver_codes['district_code']) {
            self::show_error('Vui long chon Tinh/Quan/Huyen nguoi nhan!');
            return;
        }
        $pickup_code_val = sanitize_text_field($_POST['pickup_code'] ?? '');
        $pickup_data = []; 

        if ($pickup_code_val) {
            $warehouse = Warehouses_Service::find($pickup_code_val);
            
            if (!$warehouse) {
                self::show_error('Kho lay hang khong hop le!');
                return;
            }

            // Lay cac truong co san cua kho hang de gui kem payload
            $pickup_data = [
                'code' => $warehouse['code'],
                // Cac truong nay co the rong neu API Kho hang khong tra ve
                'phone' => $warehouse['phone'] ?? '',
                'address' => $warehouse['address'] ?? '',
                'name' => $warehouse['name'] ?? '',
                
                // Khong parse address nua, de cac truong nay rong hoac lay du lieu goc neu co
                'province' => '', 
                'district' => '',
                'commune' => '',
            ];
            
        } else {
            // Truong hop 2: Nhap thu cong (Lay CODE tu form input)
            $pickup_data = [
                'code' => '',
                'province' => sanitize_text_field($_POST['pickup_province'] ?? ''), // CODE
                'district' => sanitize_text_field($_POST['pickup_district'] ?? ''), // CODE
                'commune' => sanitize_text_field($_POST['pickup_commune'] ?? ''), 
                'phone' => sanitize_text_field($_POST['pickup_phone'] ?? ''),
                'address' => sanitize_text_field($_POST['pickup_address'] ?? ''),
                'name' => sanitize_text_field($_POST['pickup_name'] ?? ''),
            ];
        }
      if (!$pickup_code_val && (!$pickup_data['province'] || !$pickup_data['district'] || !$pickup_data['commune'] || !$pickup_data['phone'] || !$pickup_data['address'] || !$pickup_data['name'])) {
            self::show_error('Vui long dien day du thong tin kho!');
            return;
        }

        $receiver_province_name = Order_Service::get_location_name($receiver_codes['province_code']);
        $receiver_district_name = Order_Service::get_location_name_district($receiver_codes['province_code'], $receiver_codes['district_code']);
        $receiver_commune_name = Location_Service::get_commune_name($receiver_codes['district_code'], $receiver_codes['commune_code']);

        // Chuyen CODE Nguoi gui sang TEN (Chi can thuc hien khi KHONG co pickup_code)
        $pickup_province_name = Order_Service::get_location_name($pickup_data['province']); // Neu chon kho: se la '' -> ''
        $pickup_district_name = Order_Service::get_location_name_district($pickup_data['province'], $pickup_data['district']); // Neu chon kho: se la '' -> ''
        $pickup_commune_name = $pickup_data['commune']; 

        // Chi kiem tra va bao loi khi nhap tay ma khong tim duoc Tên Tỉnh/Quận
        if (!$pickup_code_val && (!$pickup_province_name || !$pickup_district_name)) {
             self::show_error('Loi chuyen doi: Tinh/Quan lay hang khong hop le khi nhap thu cong!');
             return;
        }

        // 4. Xay dung Payload tao don
        $payload = [
            'name' => sanitize_text_field($_POST['name'] ?? ''),
            'phone' => sanitize_text_field($_POST['phone'] ?? ''),
            'address' => sanitize_text_field($_POST['address'] ?? ''),
            'province' => $receiver_province_name,
            'district' => $receiver_district_name,
            'commune' => $receiver_commune_name,
            'amount' => intval($_POST['amount'] ?? 0),
            'value' => intval($_POST['value'] ?? 0),
            'weight' => intval($_POST['weight'] ?? 0),
            'service' => '1', 
            'config' => intval($_POST['config'] ?? 1),
            'payer' => intval($_POST['payer'] ?? 1),
            'note' => sanitize_text_field($_POST['note'] ?? ''),
            'product' => sanitize_text_field($_POST['product'] ?? ''),
            'product_type' => '1',
        ];
        if ($pickup_code_val) {
            $payload['pickup_code'] = $pickup_code_val; 
        }
        
        $payload['pickup_name'] = $pickup_data['name'];
        $payload['pickup_phone'] = $pickup_data['phone'];
        $payload['pickup_address'] = $pickup_data['address'];
        $payload['pickup_province'] = $pickup_province_name; 
        $payload['pickup_district'] = $pickup_district_name; 
        $payload['pickup_commune'] = $pickup_commune_name; 
        
        // 5. Tao don hang
        $order = Order_Service::create_order($payload);

        // 6. Hien thi ket qua
        self::show_result($order);
    }
    
    // Ham hien thi loi
    private static function show_error($message) {
        add_action('admin_notices', function () use ($message) {
            echo "<div class='notice notice-error'><p>❌ Loi: " . esc_html($message) . "</p></div>";
        });
    }
    
    // Ham hien thi ket qua
    private static function show_result($order) {
        add_action('admin_notices', function () use ($order) {
            if ($order['status'] === 'Success') {
                $code = $order['results']['code'] ?? '';
                echo "<div class='notice notice-success' style='margin-top: 20px;'><p>✅ Tao don thanh cong! Ma don SuperShip: <strong>" . esc_html($code) . "</strong></p></div>";
            } else {
                $message = $order['message'] ?? 'Loi khong xac dinh';
                echo "<div class='notice notice-error' style='margin-top: 20px;'><p>❌ Tao don that bai! Loi: " . esc_html($message) . "</p></div>";
                echo "<pre style='background: #f5f5f5; padding: 10px; border-radius: 4px; font-size: 12px; color: red; max-height: 400px; overflow: auto;'>";
                echo htmlspecialchars(json_encode($order, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                echo "</pre>";
            }
        });


    }
    public static function handle_ajax_creation() {
        // 1. Kiểm tra Nonce và Quyền
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'create_order_nonce') || !current_user_can('edit_shop_orders')) {
            wp_send_json_error(['message' => 'Lỗi bảo mật hoặc không có quyền truy cập.'], 403);
        }
    
        $receiver_codes = [
            'province_code' => sanitize_text_field($_POST['province'] ?? ''), // CODE
            'district_code' => sanitize_text_field($_POST['district'] ?? ''), // CODE
            'commune_code' => sanitize_text_field($_POST['commune'] ?? ''), // CODE
        ];

        if (!$receiver_codes['province_code'] || !$receiver_codes['district_code']) {
            wp_send_json_error(['message' => 'Vui lòng chọn Tỉnh/Quận/Huyện người nhận!'], 400);
        }

        $pickup_code_val = sanitize_text_field($_POST['pickup_code'] ?? '');
       
        $payload = [
    
            'name' => sanitize_text_field($_POST['name'] ?? ''),
            'phone' => sanitize_text_field($_POST['phone'] ?? ''),
            'address' => sanitize_text_field($_POST['address'] ?? ''),
            'amount' => intval($_POST['amount'] ?? 0),
            'weight' => intval($_POST['weight'] ?? 0),
        ];
        $result = Order_Service::create_order($payload);
        

        // 6. Trả về kết quả
        if ($result['status'] === 'Success') {
            wp_send_json_success([
                'message' => 'Tạo đơn thành công! Mã đơn SuperShip: ' . esc_html($result['results']['code']),
                'code' => $result['results']['code'],
                'order_id' => intval($_POST['order_id']),
            ]);
        } else {
            $error_message = $result['message'] ?? 'Lỗi không xác định khi gọi API SuperShip.';
            wp_send_json_error([
                'message' => 'Tạo đơn thất bại! Lỗi: ' . esc_html($error_message),
                'details' => $result,
            ], 400);
        }
    }
}