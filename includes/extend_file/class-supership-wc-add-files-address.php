<?php
if (!defined('ABSPATH')) exit;

class Store_Address_Extended {

    public static function init() {
        add_filter('woocommerce_general_settings', [__CLASS__, 'add_fields']);
    }
    public static function add_fields($settings) {
         $new_fields = [
            [
                'title'   => __('Địa chỉ chi tiết', 'supership'),
                'id'      => 'store_address',
                'type'    => 'text',
                'default' => '',
                'desc'    => __('Nhập địa chỉ chi tiết của cửa hàng.', 'supership'),
            ],
            [
                'title'   => __('Phường / Xã', 'supership'),
                'id'      => 'store_commune',
                'type'    => 'text',
                'default' => '',
                'desc'    => __('Nhập phường / xã của cửa hàng.', 'supership'),
            ],
            [
                'title'   => __('Quận / Huyện', 'supership'),
                'id'      => 'store_district',
                'type'    => 'text',
                'default' => '',
                'desc'    => __('Nhập quận / huyện của cửa hàng.', 'supership'),
            ],
            [
                'title'   => __('Tỉnh / Thành phố', 'supership'),
                'id'      => 'store_province',
                'type'    => 'text',
                'default' => '',
                'desc'    => __('Nhập tỉnh / thành phố của cửa hàng.', 'supership'),
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
            'province'  => get_option('store_province'),
        ];
    }
    public static function set_full_store_address($data = [])
    {
        if (!is_array($data)) {
            return false;
        }
        $map = [
            'address'  => 'store_address',
            'commune'  => 'store_commune',
            'district' => 'store_district',
            'province' => 'store_province',
        ];
        foreach ($map as $key => $option_name) {
            if (isset($data[$key])) {
                update_option(
                    $option_name,
                    sanitize_text_field($data[$key])
                );
            }
        }
        return true;
    }
}