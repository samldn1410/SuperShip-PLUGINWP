<?php
if (!defined('ABSPATH')) exit;

class Store_Address_Extended {

    public static function init() {
        add_filter('woocommerce_general_settings', [__CLASS__, 'add_fields']);
        // add_action( 'woocommerce_update_options_general', [__CLASS__, 'save_fields']);
    }
    public static function add_fields($settings) {
      $new_fields = [
            [
                'title' => __('Kho Hàng Mặc Định', 'supership'),
                'type'  => 'title',
                'id'    => 'supership_default_warehouse_section',
            ],
            [
                'title'   => __('Tên kho hàng', 'supership'),
                'id'      => 'store_warehouse_name',
                'type'    => 'text',
                'default' => get_bloginfo('name'),
            ],
            [
                'title'   => __('Tên người liên hệ', 'supership'),
                'id'      => 'store_contact_name',
                'type'    => 'text',
                'default' => get_bloginfo('name'),
            ],
            [
                'title'   => __('Số điện thoại liên hệ', 'supership'),
                'id'      => 'store_contact_phone',
                'type'    => 'text',
                'default' => get_option('woocommerce_store_phone', ''),
            ],
            [
                'title' => __('Địa chỉ chi tiết', 'supership'),
                'id'    => 'store_address',
                'type'  => 'text',
            ],
            [
                'title' => __('Phường / Xã', 'supership'),
                'id'    => 'store_commune',
                'type'  => 'text',
            ],
            [
                'title' => __('Quận / Huyện', 'supership'),
                'id'    => 'store_district',
                'type'  => 'text',
            ],
            [
                'title' => __('Tỉnh / Thành phố', 'supership'),
                'id'    => 'store_province',
                'type'  => 'text',
            ],
            [
                'type' => 'sectionend',
                'id'   => 'supership_default_warehouse_section',
            ],
        ];
        $position = 0;
            foreach ($settings as $index => $setting) {
                if (
                    isset($setting['id'], $setting['type']) &&
                    $setting['id'] === 'woocommerce_store_address' &&
                    $setting['type'] === 'title'
                ) {
                    $position = $index;
                    break;
                }
            }
        array_splice($settings, $position, 0, $new_fields);

        return $settings;
    }

    /**
     * Hàm tiện ích để lấy đầy đủ Store Address
     */
    public static function get_full_store_address() {
        return [
            'contact_name' => get_option('store_contact_name'),
            'contact_phone' =>  get_option('store_contact_phone'),
            'warehouse_name' => get_option('store_warehouse_name'),
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
            'warehouse_name' => 'store_warehouse_name',
            'contact_name'   => 'store_contact_name',
            'contact_phone'  => 'store_contact_phone',
            'address'        => 'store_address',
            'commune'        => 'store_commune',
            'district'       => 'store_district',
            'province'       => 'store_province',
        ];

        foreach ($map as $key => $option_name) {
            if (!isset($data[$key])) {
                continue;
            }

            $value = $data[$key];
            switch ($key) {
                case 'contact_phone':

                    $value = preg_replace('/[^0-9]/', '', $value);
                    break;

                default:
                    $value = sanitize_text_field($value);
                    break;
            }

            update_option($option_name, $value);
        }

        return true;
    }
    public static function save_fields() {
         if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'woocommerce-settings')) {
        return;
    }
        $fields = [
            'store_warehouse_name',
            'store_contact_name',
            'store_contact_phone',
            'store_address',
            'store_commune',
            'store_district',
            'store_province',
        ];

        foreach ($fields as $field) {
            if (!isset($_POST[$field])) {
                continue;
            }
            $value = wp_unslash($_POST[$field]);
            if ($field === 'store_contact_phone') {
                $value = preg_replace('/[^0-9]/', '', $value);
            } else {
                $value = sanitize_text_field($value);
            }

            update_option($field, $value);
        }
    }
}