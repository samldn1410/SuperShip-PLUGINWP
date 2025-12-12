<?php
if (!defined('ABSPATH')) exit;

class Order_Creation_Handler {

    public static function create_supership_order($wc_order) {
        $order_id = $wc_order->get_id();
        $data = self::get_order_data($wc_order, $order_id);

        if (!$data['success']) {
            return $data; 
        }

        $warehouse = $data['warehouse'];
        $payload = [
            'pickup_code' => $data['pickup_code'],
            'pickup_phone' => $warehouse['phone'] ?? '',
            'pickup_address' => $warehouse['address'] ?? '',
            'pickup_province' => $warehouse['province'] ?? '',
            'pickup_district' => $warehouse['district'] ?? '',
            'pickup_commune' => $warehouse['commune'] ?? '',
            'pickup_name' => $warehouse['name'] ?? '',
            'name' => $data['receiver']['name'],
            'phone' => $data['receiver']['phone'],
            'address' => $data['receiver']['address'],
            'province' => $data['receiver']['province'],
            'district' => $data['receiver']['district'],
            'commune' => $data['receiver']['commune'],
            'amount' => intval($wc_order->get_total()), 
            'value' => intval($wc_order->get_total()), 
            'weight' => $data['product_info']['weight_gram'],
            'products' => $data['product_info']['products_payload'],
            'product_type' => '2',
            'service' => $data['config']['service'],
            'config' => $data['config']['config'],
            'payer' => $data['config']['payer'],
            'soc' => 'WC-' . $order_id,
            'note' => 'Tạo từ WC Order #' . $order_id,
        ];

        if ($data['config']['barter']) {
            $payload['barter'] = $data['config']['barter'];
        }
        if (!class_exists('Order_Service')) {
            return ['success' => false, 'message' => 'Lớp dịch vụ API không tồn tại. (Order_Service)', 'details' => []];
        }

        $result = Order_Service::create_order($payload);

        if ($result['status'] === 'Success') {
            Order_Service::save_supership_order_create($order_id, $result,$data['receiver'],$data['config']);
            return [
                'success' => true,
                'code' => $result['results']['code'] ?? '',
                'message' => 'Tạo đơn thành công!'
            ];
        } else {
            return [
                'success' => false,
                'message' => $result['message'] ?? 'Lỗi không xác định từ API SuperShip',
                'details' => $result
            ];
        }
    }

    private static function get_order_data($wc_order, $order_id) {
        if (!class_exists('WC_Custom_Fields') || !class_exists('Warehouses_Service')) {
            return ['success' => false, 'message' => 'Lớp helper/dịch vụ cần thiết không tồn tại.', 'details' => []];
        }
        $receiver_name = trim($wc_order->get_shipping_first_name() . ' ' . $wc_order->get_shipping_last_name());
        $receiver_phone = trim($wc_order->get_billing_phone());
        $order = wc_get_order($order_id);
        $receiver_address = "219/4 Lê Văn Chí";
        $receiver_province_name  = "Thành Phố Hồ Chí Minh";
        $receiver_district_name  = "Quận Thủ Đức";
        $receiver_commune_name  = "Phường Linh Trung";
        if (!$receiver_name || !$receiver_phone || !$receiver_address || !$receiver_province_name || !$receiver_district_name || !$receiver_commune_name) {
            return ['success' => false, 'message' => 'Thông tin người nhận không đầy đủ. Vui lòng kiểm tra địa chỉ chi tiết.', 'details' => []];
        }
        $pickup_code = WC_Custom_Fields::get_pickup_code($order_id);
        if (!$pickup_code) {
            return ['success' => false, 'message' => 'Mã kho lấy hàng chưa được cấu hình.', 'details' => []];
        }
        $warehouse = Warehouses_Service::find($pickup_code);
        if (!$warehouse) {
            return ['success' => false, 'message' => 'Mã kho lấy hàng không hợp lệ.', 'details' => ['pickup_code' => $pickup_code]];
        }
        $product_info = self::calculate_product_weight($wc_order);
        $config = [
            'config' => WC_Custom_Fields::get_field($order_id, 'config') ?: 1,
            'payer' => WC_Custom_Fields::get_field($order_id, 'payer') ?: 1,
            'service' => WC_Custom_Fields::get_field($order_id, 'service') ?: 1,
            'barter' => WC_Custom_Fields::get_field($order_id, 'barter') ?: '',
        ];
        return [
            'success' => true,
            'pickup_code' => $pickup_code,
            'warehouse' => $warehouse,
            'receiver' => [
                'name' => $receiver_name, 'phone' => $receiver_phone, 'address' => $receiver_address, 
                'province' => $receiver_province_name, 'district' => $receiver_district_name, 'commune' => $receiver_commune_name
            ],
            'product_info' => $product_info,
            'config' => $config
        ];
    }

    public static function calculate_product_weight($wc_order) {
        $products_payload = [];
        $weight_gram = 0;

        foreach ($wc_order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            $qty = $item->get_quantity();
            $item_price = $item->get_subtotal() / $qty; 
            $item_weight_kg = ($product) ? floatval($product->get_weight()) : 0; 
            $item_weight_gram = $item_weight_kg * 1000;
            
            $weight_gram += ($item_weight_gram * $qty);

            $products_payload[] = [
                'sku' => $product ? $product->get_sku() : 'N/A',
                'name' => $item->get_name(),
                'price' => intval($item_price),
                'weight' => intval($item_weight_gram ?: 1),
                'quantity' => $qty,
            ];
        }
        $final_weight_gram = max(intval($weight_gram), 200);

        return [
            'weight_gram' => $final_weight_gram,
            'products_payload' => $products_payload
        ];
    }
}
