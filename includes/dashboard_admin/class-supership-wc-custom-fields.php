<?php
if (!defined('ABSPATH')) exit;


class WC_Custom_Fields {

    public static function init() {
        add_action('woocommerce_process_shop_order_meta', [__CLASS__, 'save_custom_fields_fallback']);
        add_action('rest_api_init', [__CLASS__, 'register_custom_fields_rest']);
    }
    public static function register_custom_fields_rest() {
        $fields = [
            'pickup_code' => 'pickup_code',
            'config' => 'config',
            'payer' => 'payer',
            'service' => 'service',
            'barter' => 'barter',
            'receiver_address' => 'receiver_address',
            'receiver_province' => 'receiver_province',
            'receiver_district' => 'receiver_district',
            'receiver_commune' => 'receiver_commune',
            'order_code' => 'order_code', 
        ];
        foreach ($fields as $meta_key => $rest_name) {
            register_rest_field('shop_order', $rest_name, [
                'get_callback' => function ($order) use ($meta_key) {
                    return get_post_meta($order->ID, $meta_key, true);
                },
                'update_callback' => function ($value, $order) use ($meta_key) {
                    update_post_meta($order->ID, $meta_key, $value);
                },
                'schema' => [
                    'type' => 'string',
                    'context' => ['view', 'edit'],
                ],
            ]);
        }
    }
    public static function get_field($order_id, $field_name) {
    return get_post_meta($order_id, $field_name, true);
    }
    public static function save_field($order_id, $field_name, $value) {
        update_post_meta($order_id, $field_name, $value);
    }
    public static function get_pickup_code($order_id) {
        $pickup_code = get_post_meta($order_id, 'pickup_code', true);
        if (!$pickup_code && class_exists('Warehouses_Service')) {
            $default_warehouse = Warehouses_Service::get_default();
            if ($default_warehouse) {
                $pickup_code = $default_warehouse['code'];
            }
        }
        return $pickup_code;
    }
}
WC_Custom_Fields::init();