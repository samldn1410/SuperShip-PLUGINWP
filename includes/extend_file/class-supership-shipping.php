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

        add_action(
            'woocommerce_update_options_shipping_' . $this->id,
            [$this, 'process_admin_options']
        );
    }

    public function init_form_fields() {
        $this->form_fields = [
            'enabled' => [
                'title'   => __('Bật / Tắt', 'supership'),
                'type'    => 'checkbox',
                'label'   => __('Bật phương thức SuperShip', 'supership'),
                'default' => 'yes',
            ],
        ];
    }

    /**
     * CALCULATE SHIPPING FEE
     */
    // public function calculate_shipping($package = []) {
    //     // if ($this->enabled !== 'yes') return;
    //     error_log("==== SUPERLOG: calculate_shipping RUN ====");
    //     // Lấy địa chỉ từ Blocks Session đúng chuẩn
    //     $addr = WC()->session->get('supership_address') ?? [];
    //     error_log("SESSION IN SHIPPING: " . print_r($addr, true));
    //     if (!$addr) {
    //     error_log("NO SESSION DATA -> Cannot calculate fee");
    //     }
    //     $receiver_province = sanitize_text_field($addr['province'] ?? '');
    //     $receiver_district = sanitize_text_field($addr['district'] ?? '');

    //     // Nếu chưa chọn địa chỉ → chỉ hiển thị phương thức, không tính phí
    //     if (!$receiver_province || !$receiver_district) {
    //         $this->add_rate([
    //             'id'    => $this->get_rate_id(),
    //             'label' => $this->title,
    //             'cost'  => 0,
    //         ]);
    //         return;
    //     }

    //     // Tính trọng lượng
    //     $weight = 0;
    //     foreach (WC()->cart->get_cart() as $item) {
    //         $w = (float) $item['data']->get_weight();
    //         $weight += $w * $item['quantity'];
    //     }
    //     if ($weight < 50) $weight = 50;

    //     // Call API
    //     $price = Order_Service::get_shipping_price(
    //         'Thành Phố Hồ Chí Minh',  // sender province
    //         'Quận 1',                 // sender district
    //         $receiver_province,
    //         $receiver_district,
    //         $weight,
    //         WC()->cart->cart_contents_total
    //     );
    //     error_log("API RESPONSE: " . print_r($price, true));
    //     // Debug logger
    //     $logger = wc_get_logger();
    //     $logger->info("SuperShip API response: " . print_r($price, true), ['source' => 'supership']);

    //     if ($price['status'] !== 'Success') return;

    //     $this->add_rate([
    //         'id'    => $this->get_rate_id(),
    //         'label' => "{$this->title} – {$price['service']} (Giao: {$price['delivery']})",
    //         'cost'  => $price['fee'],
    //     ]);
    // }

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
