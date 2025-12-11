<?php
if (!defined('ABSPATH')) exit;

class Order_Service {
    public static function calc_weight($manual, $l, $w, $h, $product_sum = 0) {
        $manual = intval($manual);
        $vol = ($l * $w * $h) / 5; 
        return max($manual, $vol, $product_sum);
    }
    public static function create_order($payload) {
        return API::post('/v1/partner/orders/add', $payload); 
    }

    public static function cancel_order($code) {

        if (!$code) {
            return [
                'status' => 'Error',
                'message' => 'Thiếu mã đơn SuperShip (code).'
            ];
        }

        return API::post('/v1/partner/orders/cancel', [
            'code' => $code
        ]);
    }
    public static function get_order_info($code, $type = 1) {
        if (!$code) {
            return [
                'status' => 'Error',
                'message' => 'Thiếu mã đơn SuperShip (code).'
            ];
        }

        $params = [
            'code' => $code,
            'type' => $type, // 1 = mã SuperShip, 2 = mã SOC
        ];
        return API::get('/v1/partner/orders/info', $params);
    }

    public static function save_supership_order_create($wp_order_id, $response, $receiver,$config) {
        global $wpdb;
        $table = $wpdb->prefix . 'supership_orders';

        if (!isset($response['results'])) return false;

        $r = $response['results'];

        $data = [
            'wp_order_id'      => intval($wp_order_id),
            'supership_code'          => $r['code'] ?? '',
            'supership_shortcode'     => $r['shortcode'] ?? '',
            'supership_soc'           => $r['soc'] ?? '',
            'receiver_name'    => $receiver['name'] ?? '',
            'receiver_phone'   => $receiver['phone'] ?? '',
            'receiver_address' => $receiver['address'] ?? '',
            'amount'           => intval($r['amount'] ?? 0),
            'value'            => intval($r['value'] ?? 0),
            'weight'           => intval($r['weight'] ?? 0),
            'fee'              => intval($r['fee'] ?? 0),
            'payer'            => $config['payer'] ?? '',
            'service'       => $config['service'] ?? '',
            'barter'        => $config['barter'] ?? '',
            'config'           => $config['config'] ?? '',
            'insurance'        => intval($r['insurance'] ?? 0),
            'status_name'      => $r['status_name'] ?? '',
            'raw_response'     => json_encode($response, JSON_UNESCAPED_UNICODE),
        ];

        $wpdb->insert($table, $data);
        return true;
    }

    public static function update_supership_order_info($wp_order_id, $response) {
        global $wpdb;

        $table = $wpdb->prefix . 'supership_orders';

        if (!isset($response['results'])) {
            return false;
        }

        $r = $response['results'];
        $receiver = $r['receiver'] ?? [];
        $fee = $r['fee'] ?? [];
        $data = [
            'receiver_name'      => $receiver['name']  ?? '',
            'receiver_phone'     => $receiver['phone'] ?? '',
            'receiver_address'   => $receiver['address'] ?? '',
            'amount'             => intval($r['amount'] ?? 0),
            'value'              => intval($r['value'] ?? 0),
            'weight'             => intval($r['weight'] ?? 0),
            'fee'                => intval($fee['shipment'] ?? 0),
            'insurance'          => intval($fee['insurance'] ?? 0),
            'fee_return'         => intval($fee['return'] ?? 0),
            'fee_barter'         => intval($fee['barter'] ?? 0),
            'fee_address'        => intval($fee['address'] ?? 0),
            'payer'              => $r['payer'] ?? '',
            'config'             => $r['config'] ?? '',
            'barter'             => $r['barter'] ?? '',
            'status_name'        => $r['status_name'] ?? '',
            'partial'            => $r['partial'] ?? '',
            'journeys'           => json_encode($r['journeys'] ?? [], JSON_UNESCAPED_UNICODE),
            'notes'              => json_encode($r['notes'] ?? [], JSON_UNESCAPED_UNICODE),
            'raw_response'       => json_encode($response, JSON_UNESCAPED_UNICODE),
            'updated_at'         => current_time('mysql'),
        ];

        // Update dòng trong bảng theo mã đơn WP
        return $wpdb->update(
            $table,
            $data,
            ['wp_order_id' => intval($wp_order_id)]
        );
    }


    public static function get_location_name($value) {
        if (!$value) return '';
        if (is_numeric($value) && strlen($value) <= 3) {
            $name = Location_Service::get_province_name($value);
            return $name ?: $value; 
        }
        return $value;
    }

  
    public static function get_location_name_district($province_value, $district_value) {
        if (!$district_value) return '';

        $province_code = $province_value;
        if (!is_numeric($province_value) || strlen($province_value) > 3) {
            $province_code = self::get_province_code_by_name($province_value);
        }
        if (is_numeric($district_value) && strlen($district_value) <= 3) {
            $name = Location_Service::get_district_name($province_code, $district_value);
            return $name ?: $district_value; // Tra ve Ten hoac Code neu khong tim thay
        }
        return $district_value;
    }
    private static function get_province_code_by_name($name) {
        if (!$name) return null;
        
        $provinces = Location_Service::get_provinces();
        $search_name = strtolower(trim($name));
        
        foreach ($provinces as $p) {
            if (strtolower(trim($p['name'])) === $search_name) {
                return $p['code'];
            }
        }
        
        return null;
    }
    
}