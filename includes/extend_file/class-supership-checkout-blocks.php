<?php
if (!defined('ABSPATH')) exit;

class Supership_Checkout_Blocks {

    public static function init() {

        // Đăng ký field cho WooCommerce Blocks
        add_action('woocommerce_init', [__CLASS__, 'register_fields']);

        // Ẩn field mặc định VN
        add_filter('woocommerce_get_country_locale',  [__CLASS__, 'customize_vn_locale']);
        //  add_action('woocommerce_store_api_checkout_update_order_from_request', [__CLASS__, 'validate_fields'], 10, 2);
        // AJAX load huyện
        add_action('wp_ajax_load_districts',        [__CLASS__, 'load_districts']);
        add_action('wp_ajax_nopriv_load_districts', [__CLASS__, 'load_districts']);

        add_action('wp_ajax_load_communes',        [__CLASS__, 'load_communes']);
        add_action('wp_ajax_nopriv_load_communes', [__CLASS__, 'load_communes']);
    }

    /**
     * 1. Ẩn các field mặc định của WooCommerce VN
     */
    public static function customize_vn_locale($locale) {

        $fields_to_hide = ['address_1', 'postcode', 'city', 'company', 'state'];

        foreach ($fields_to_hide as $f) {
            $locale['VN'][$f] = [
                'required' => false,
                'hidden'   => true,
            ];
        }

        return $locale;
    }
//     public static function validate_fields($order, $request) {
//     $district = $request['extensions']['supership/district'] ?? '';
//     $commune = $request['extensions']['supership/commune'] ?? '';
    
//     if (empty($district)) {
//         throw new \Exception('Vui lòng chọn Quận/Huyện');
//     }
//     if (empty($commune)) {
//         throw new \Exception('Vui lòng chọn Phường/Xã');
//     }
// }
    /**
     * 2. Đăng ký Field cho Checkout Blocks
     */
    public static function register_fields() {

        // Địa chỉ chi tiết
         woocommerce_register_additional_checkout_field([
            'id'       => 'supership/address_detail',
            'label'    => __('Địa chỉ chi tiết', 'supership'),
            'location' => 'address',
            'type'     => 'text',
            'required' => true,
        ]);

        // Tỉnh / Thành phố
        woocommerce_register_additional_checkout_field([
            'id'       => 'supership/province',
            'label'    => __('Tỉnh / Thành phố', 'supership'),
            'location' => 'address',
            'type'     => 'select',
            'required' => true,
            'options'  => self::province_options(),
        ]);

        // Quận / Huyện
        woocommerce_register_additional_checkout_field([
            'id'        => 'supership/district',
            'namespace' => 'supership',
            'label'     => __('Quận / Huyện', 'supership'),
            'location'  => 'address',
            'type'      => 'text', 
            'required'  => true,
            'attributes' => [
                'readonly' => true,
            ],
        ]);

        // Phường / Xã
        woocommerce_register_additional_checkout_field([
            'id'        => 'supership/commune',
            'namespace' => 'supership',
            'label'     => __('Phường / Xã', 'supership'),
            'location'  => 'address',
            'type'      => 'text', 
            'required'  => true,
            'attributes' => [
                'readonly' => true,
            ],
        ]);
    }

    /**
     * 3. Load Tỉnh (Province)
     */
    private static function province_options() {
        $prov = Location_Service::get_provinces();
        $opt  = [];

        foreach ($prov as $p){
            $opt[] = [
                'value' => $p['code'],
                'label' => $p['name']
            ];
        }
        return $opt;
    }

    /**
     * 4. AJAX: Load Quận/Huyện
     */
    public static function load_districts() {
        $province = sanitize_text_field($_POST['province_code'] ?? '');
        wp_send_json([
            'districts' => Location_Service::get_districts($province)
        ]);
    }

    /**
     * 5. AJAX: Load Phường/Xã
     */
    public static function load_communes() {
        $district = sanitize_text_field($_POST['district_code'] ?? '');
        wp_send_json([
            'communes' => Location_Service::get_communes($district)
        ]);
    }
}
