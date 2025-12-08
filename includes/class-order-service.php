<?php
if (!defined('ABSPATH')) exit;

class SS_Order_Service {

    /**
     * Tinh trong luong cuoi cung (gram)
     */
    public static function calc_weight($manual, $l, $w, $h, $product_sum = 0) {
        $manual = intval($manual);
        // Khoi luong quy doi (D*R*C)/5
        $vol = ($l * $w * $h) / 5; 
        return max($manual, $vol, $product_sum);
    }
    
    /**
     * API tao don hang
     */
    public static function create_order($payload) {
        // Ghi log payload truoc khi gui (de debug)
        error_log("=== SS_Order_Service::create_order ===");
        error_log("Payload: " . json_encode($payload, JSON_UNESCAPED_UNICODE));

        // Endpoint: post/v1/partner/orders/add
        return SS_API::post('/v1/partner/orders/add', $payload); 
    }

   
    /**
     * Helper: Chuyen Province Code thanh Ten (hoac giu nguyen neu da la Ten)
     */
    public static function get_location_name($value) {
        if (!$value) return '';

        // Neu la CODE (thuong la so, 1-3 ky tu)
        if (is_numeric($value) && strlen($value) <= 3) {
            $name = SS_Location_Service::get_province_name($value);
            return $name ?: $value; // Tra ve Ten hoac Code neu khong tim thay
        }

        // Da la TEN, dung luon
        return $value;
    }

    /**
     * Helper: Chuyen District Code thanh Ten (hoac giu nguyen neu da la Ten)
     */
    public static function get_location_name_district($province_value, $district_value) {
        if (!$district_value) return '';

        $province_code = $province_value;
        
        // Neu province dang la TEN (dang chu), phai tim lai CODE cua tinh do
        if (!is_numeric($province_value) || strlen($province_value) > 3) {
            $province_code = self::get_province_code_by_name($province_value);
        }

        // Neu district la CODE
        if (is_numeric($district_value) && strlen($district_value) <= 3) {
            $name = SS_Location_Service::get_district_name($province_code, $district_value);
            return $name ?: $district_value; // Tra ve Ten hoac Code neu khong tim thay
        }
        return $district_value;
    }

    /**
     * Helper: Lay province code tu name (Dung noi bo)
     */
    private static function get_province_code_by_name($name) {
        if (!$name) return null;
        
        $provinces = SS_Location_Service::get_provinces();
        $search_name = strtolower(trim($name));
        
        foreach ($provinces as $p) {
            if (strtolower(trim($p['name'])) === $search_name) {
                return $p['code'];
            }
        }
        
        return null;
    }
}