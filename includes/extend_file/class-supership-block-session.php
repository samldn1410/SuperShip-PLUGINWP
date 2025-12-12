<?php
if (!defined('ABSPATH')) exit;

class Supership_Block_Session {

    public static function init() {
        add_filter(
            'woocommerce_store_api_checkout_update_order_from_request',
            [__CLASS__, 'save_session'],
            9,
            2
        );
    }

     public static function save_session($order, $request) {
        error_log("==== SUPERLOG: save_session RUN ====");
        error_log("REQUEST EXT: " . print_r($request['extensions'], true));

        $ext = $request['extensions']['supership'] ?? [];

        WC()->session->set('supership_address', [
            'address'  => $ext['address_detail'] ?? '',
            'province' => $ext['province'] ?? '',
            'district' => $ext['district'] ?? '',
            'commune'  => $ext['commune'] ?? '',
        ]);

        error_log("SESSION SAVED: " . print_r(WC()->session->get('supership_address'), true));
        return $order;
    }
}
