<?php
if (!defined('ABSPATH')) exit;

class Store_Address_Extended {

    public static function init() {
        add_filter('woocommerce_general_settings', [__CLASS__, 'add_fields']);
    }
    public static function add_fields($settings) {
        $new_fields = [
             [
                'title' => 'Địa Chỉ Chi Tiết',
                'id'    => 'store_address',
                'type'  => 'text',
                'default' => '',
                'desc'  => 'Nhập địa chỉ chi tiết của cửa hàng.',
            ],
            [
                'title' => 'Phường / Xã',
                'id'    => 'store_commune',
                'type'  => 'text',
                'default' => '',
                'desc'  => 'Nhập phường / xã của cửa hàng.',
            ],
            [
                'title' => 'Quận / Huyện',
                'id'    => 'store_district',
                'type'  => 'text',
                'default' => '',
                'desc'  => 'Nhập quận / huyện của cửa hàng.',
            ],
           
        ];

        // Xác định vị trí của "Address line 2"
        $position = 0;
        foreach ($settings as $index => $setting) {
            if (isset($setting['id']) && $setting['id'] === 'woocommerce_store_address_2') {
                $position = $index + 1;
                break;
            }
        }

        // Chèn field vào ngay sau Address Line 2
        array_splice($settings, $position, 0, $new_fields);

        return $settings;
    }

    /**
     * Hàm tiện ích để lấy đầy đủ Store Address
     */
    public static function get_full_store_address() {
        return [
            'address' => get_option('store_address'),
            'commune'   => get_option('store_commune'),
            'district'  => get_option('store_district'),
            'province'  => get_option('woocommerce_store_city'),
        ];
    }
}