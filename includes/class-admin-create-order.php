<?php
if (!defined('ABSPATH')) exit;

class SS_Admin_Create_Order {

    public static function init() {
        add_action('admin_init', [__CLASS__, 'handle']);
    }

    public static function handle() {
        if (!isset($_POST['ss_create_order']) || !wp_verify_nonce($_POST['ss_nonce'], 'ss_create_order')) {
            return;
        }

        // 1. Lay va Xac thuc thong tin Nguoi Nhan (Receiver) - Luon lay CODE
        $receiver_codes = [
            'province_code' => sanitize_text_field($_POST['province'] ?? ''), // CODE
            'district_code' => sanitize_text_field($_POST['district'] ?? ''), // CODE
            'commune_code' => sanitize_text_field($_POST['commune'] ?? ''), // CODE
        ];

        if (!$receiver_codes['province_code'] || !$receiver_codes['district_code']) {
            self::show_error('Vui long chon Tinh/Quan/Huyen nguoi nhan!');
            return;
        }

        // 2. Lay va Xac thuc thong tin Nguoi Gui (Pickup)
        $pickup_code_val = sanitize_text_field($_POST['pickup_code'] ?? '');
        $pickup_data = []; 

        if ($pickup_code_val) {
            // Truong hop 1: Chon Kho hang (UU TIEN PICKUP_CODE)
            $warehouse = SS_Warehouses_Service::find($pickup_code_val);
            
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

        // Xac thuc thong tin toi thieu (Chi can kiem tra truong hop nhap thu cong)
        // Chi khi KHONG co pickup_code moi bat buoc cac truong con lai phai day du
        if (!$pickup_code_val && (!$pickup_data['province'] || !$pickup_data['district'] || !$pickup_data['phone'] || !$pickup_data['address'] || !$pickup_data['name'])) {
            self::show_error('Vui long dien day du thong tin kho!');
            return;
        }

        // 3. Chuan bi Dia Chi Tinh/Quan/Huyen duoi dang TEN cho Payload API

        // Chuyen CODE Nguoi nhan sang TEN
        $receiver_province_name = SS_Order_Service::get_location_name($receiver_codes['province_code']);
        $receiver_district_name = SS_Order_Service::get_location_name_district($receiver_codes['province_code'], $receiver_codes['district_code']);
        $receiver_commune_name = SS_Location_Service::get_commune_name($receiver_codes['district_code'], $receiver_codes['commune_code']);

        // Chuyen CODE Nguoi gui sang TEN (Chi can thuc hien khi KHONG co pickup_code)
        $pickup_province_name = SS_Order_Service::get_location_name($pickup_data['province']); // Neu chon kho: se la '' -> ''
        $pickup_district_name = SS_Order_Service::get_location_name_district($pickup_data['province'], $pickup_data['district']); // Neu chon kho: se la '' -> ''
        $pickup_commune_name = $pickup_data['commune']; 

        // Chi kiem tra va bao loi khi nhap tay ma khong tim duoc Tên Tỉnh/Quận
        if (!$pickup_code_val && (!$pickup_province_name || !$pickup_district_name)) {
             self::show_error('Loi chuyen doi: Tinh/Quan lay hang khong hop le khi nhap thu cong!');
             return;
        }

        // 4. Xay dung Payload tao don
        $payload = [
            // Thong tin Nguoi Nhan (Yeu cau TEN)
            'name' => sanitize_text_field($_POST['name'] ?? ''),
            'phone' => sanitize_text_field($_POST['phone'] ?? ''),
            'address' => sanitize_text_field($_POST['address'] ?? ''),
            'province' => $receiver_province_name,
            'district' => $receiver_district_name,
            'commune' => $receiver_commune_name,
            
            // Thong tin Hang hoa & Thanh toan
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

        // Thong tin Nguoi Gui (Pickup)
        if ($pickup_code_val) {
            // Uu tien dung pickup_code neu co
            $payload['pickup_code'] = $pickup_code_val; 
        }
        
        // Luon gui day du thong tin gui hang (Du co pickup_code hay khong)
        $payload['pickup_name'] = $pickup_data['name'];
        $payload['pickup_phone'] = $pickup_data['phone'];
        $payload['pickup_address'] = $pickup_data['address'];
        
        // Gui TEN (Neu nhap tay) hoac RONG (Neu chon kho)
        $payload['pickup_province'] = $pickup_province_name; 
        $payload['pickup_district'] = $pickup_district_name; 
        $payload['pickup_commune'] = $pickup_commune_name; 
        
        // 5. Tao don hang
        $order = SS_Order_Service::create_order($payload);

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
}