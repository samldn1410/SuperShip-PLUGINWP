<?php
if (!defined('ABSPATH')) exit;

class Checkout_Blocks {

    public static function init() {

        add_action('woocommerce_init', [__CLASS__, 'register_fields']);

        // Save changes from AJAX
      add_action('wp_ajax_blocks_load_districts', [__CLASS__, 'load_districts']);
    add_action('wp_ajax_nopriv_blocks_load_districts', [__CLASS__, 'load_districts']);

    add_action('wp_ajax_blocks_load_communes', [__CLASS__, 'load_communes']);
    add_action('wp_ajax_nopriv_blocks_load_communes', [__CLASS__, 'load_communes']);
    
     // FIX chính – REST API cho WooCommerce Blocks
        add_action('rest_api_init', [__CLASS__, 'register_rest_routes']);
       add_filter('woocommerce_get_country_locale', function( $locale ) {

	// Thay DZ → VN
	$locale['VN']['address_1'] = [
		'required' => false,
		'hidden'   => true,
	];

	$locale['VN']['postcode'] = [
		'required' => false,
		'hidden'   => true,
	];

	$locale['VN']['city'] = [
		'required' => false,
		'hidden'   => true,
	];

	$locale['VN']['company'] = [
		'required' => false,
		'hidden'   => true,
	];

	$locale['VN']['state'] = [
		'required' => false,
		'hidden'   => true,
	];

	// $locale['VN']['phone'] = [
	// 	'required' => false,
	// 	'hidden'   => true,
	// ];

	return $locale;
   });
        add_action('wp_ajax_load_communes', [__CLASS__, 'load_communes']);
        add_action('wp_ajax_nopriv_load_communes', [__CLASS__, 'load_communes']);
    }

    public static function register_fields() {

         // Address detail
        //  woocommerce_register_additional_checkout_field([
        //     'id'       => 'supership/name',
        //     'label'    => 'Họ và tên',
        //     'location' => 'address',
        //     'type'     => 'text',
        //     'required' => true,
        // ]);
        woocommerce_register_additional_checkout_field([
            'id'       => 'supership/address_detail',
            'label'    => 'Địa chỉ chi tiết',
            'location' => 'address',
            'type'     => 'text',
            'required' => true,
        ]);
         // Commune
        woocommerce_register_additional_checkout_field([
            'id'       => 'supership/commune',
            'label'    => 'Phường / Xã',
            'location' => 'address',
            'type'     => 'text',
            'required' => true,
            // 'options'  => ["Xã 1", "Xã 2"],
            // 'placeholder' => 'Chọn phường/xã'
        ]);
        // District
        woocommerce_register_additional_checkout_field([
            'id'          => 'supership/district',
            'label'       => 'Quận / Huyện',
            'location'    => 'address',
            'type'        => 'text',
            'required'    => true,
            // 'options'     => [],
            // 'placeholder' => 'Chọn quận/huyện'
        ]);

       

        // Province
        woocommerce_register_additional_checkout_field([
            'id'          => 'supership/province',
            'label'       => 'Tỉnh / Thành phố',
            'location'    => 'address',
            'type'        => 'select',
            'required'    => true,
            'options'     => self::province_options(),
            'placeholder' => 'Chọn tỉnh'
        ]);

        
       
    }

    private static function province_options() {
        $prov = Location_Service::get_provinces();
        $opt = [];

        foreach ($prov as $p){
            $opt[] = [
                'value' => $p['code'],
                'label' => $p['name']
            ];
        }
        return $opt;
    }
public static function load_districts() {
        $code = sanitize_text_field($_POST['province_code'] ?? '');
        $districts = Location_Service::get_districts($code);

        $options = [['value' => '', 'label' => 'Chọn quận/huyện']];
        foreach ($districts as $d) {
            $options[] = ['value' => $d['code'], 'label' => $d['name']];
        }

        wp_send_json(['options' => $options]);
    }

    public static function load_communes() {
        $code = sanitize_text_field($_POST['district_code'] ?? '');
        $communes = Location_Service::get_communes($code);

        $options = [['value' => '', 'label' => 'Chọn phường/xã']];
        foreach ($communes as $c) {
            $options[] = ['value' => $c['code'], 'label' => $c['name']];
        }

        wp_send_json(['options' => $options]);
    }


    /**
     * === REST API FOR CHECKOUT BLOCKS (CHUẨN, KHÔNG LỖI) ===
     */
    public static function register_rest_routes() {

        register_rest_route('supership/v1', '/districts', [
            'methods'  => 'GET',
            'callback' => function($req) {

                $code = sanitize_text_field($req->get_param('province_code'));
                $districts = Location_Service::get_districts($code);

                $options = [['value' => '', 'label' => 'Chọn quận/huyện']];
                foreach ($districts as $d) {
                    $options[] = [
                        'value' => $d['code'],
                        'label' => $d['name']
                    ];
                }

                return ['options' => $options];
            },
            'permission_callback' => '__return_true'
        ]);

        register_rest_route('supership/v1', '/communes', [
            'methods'  => 'GET',
            'callback' => function($req) {

                $code = sanitize_text_field($req->get_param('district_code'));
                $communes = Location_Service::get_communes($code);

                $options = [['value' => '', 'label' => 'Chọn phường/xã']];
                foreach ($communes as $c) {
                    $options[] = [
                        'value' => $c['code'],
                        'label' => $c['name']
                    ];
                }

                return ['options' => $options];
            },
            'permission_callback' => '__return_true'
        ]);
    }


}
