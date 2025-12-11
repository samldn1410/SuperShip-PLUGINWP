<?php
if (!defined('ABSPATH')) exit;

class Shipping_Method extends WC_Shipping_Method {

    public function __construct() {
        $this->id                 = 'supership';
        $this->method_title       = __('SuperShip', 'supership');
        $this->method_description = __('Vận chuyển SuperShip', 'supership');
        $this->enabled            = 'yes';
        $this->title              = __('SuperShip', 'supership');

        $this->init();
    }

    public function init() {
        $this->init_form_fields();
        $this->init_settings();

        add_action('woocommerce_update_options_shipping_' . $this->id , 
            array($this, 'process_admin_options'));
    }

    // Cài đặt trong admin (WooCommerce → Shipping → SuperShip)
    public function init_form_fields() {
        $this->form_fields = [
            'enabled' => [
                'title'   => __('Bật/Tắt'),
                'type'    => 'checkbox',
                'label'   => __('Bật phương thức SuperShip'),
                'default' => 'yes',
            ],
            'cost' => [
                'title' => __('Phí vận chuyển mặc định'),
                'type'  => 'text',
                'desc'  => 'Nếu anh muốn tính phí cố định.',
                'default' => '0',
            ],
        ];
    }

    // Tính phí vận chuyển
    public function calculate_shipping($package = []) {

        // Ví dụ phí tạm: 20.000đ
        $cost = 20000;

        // Hoặc anh có thể gọi API Supership tính giá tại đây

        $this->add_rate([
            'id'    => $this->id,
            'label' => $this->title,
            'cost'  => $cost,
        ]);
    }
}
