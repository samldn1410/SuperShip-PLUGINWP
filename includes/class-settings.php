<?php
if (!defined('ABSPATH')) exit;

class Settings {

    public static function get_token() {
        return get_option('access_token', '');
    }

    
    public static function save_token($token) {
        update_option('access_token', sanitize_text_field($token));
        self::handle_after_save_token();
    }
    public static function handle_after_save_token()
    {
        $connected = self::auto_setup_webhook();
        if (!$connected) {
            return;
        }
        $address = Store_Address_Extended::get_full_store_address();
        $missing = false;
        foreach ($address as $value) {
        if (empty($value)) {
            update_option('supership_need_store_address', 1);
          set_transient('supership_flash_message', [
            'type'  => 'info',
            'title' => __('Chưa hoàn tất cấu hình', 'supership'),
            'text'  => __('Bạn cần nhập địa chỉ kho hàng để sử dụng dịch vụ của SuperShip.', 'supership'),
        ], 30);
            wp_safe_redirect(
                admin_url('admin.php?page=wc-settings&tab=general')
            );
            exit; 
          }
        }
        self::auto_create_shop_warehouse($address);
        return 'ok';
    }

    protected static function auto_setup_webhook() {
        // $webhook_url = home_url('/wp-json/supership/v1/webhook');
        $webhook_url ='https://the350f.com/'; //https://moho.com.vn/ https://the350f.com/
        $res = Webhook_API::create_webhook($webhook_url);
        if (
            ($res['status'] ?? '') === 'Error' &&
            (int)($res['code'] ?? 0) === 401
        ) {
            error_log('[SuperShip] Invalid or expired API Token.');
            return false;
        }
        update_option('supership_webhook_ready', 1);
        return true;
    }
    protected static function auto_create_shop_warehouse($address)
    {
        $data = [
            'name'     => $address['warehouse_name'],
            'phone'    => $address['contact_phone'],
            'contact'  => $address['warehouse_name'],
            'address'  => $address['address'],
            'province' => $address['province'],
            'district' => $address['district'],
            'commune'  => $address['commune'],
            'primary'  => 1,
        ];
        $res = Warehouse_API::create($data);
        if (($res['status'] ?? '') === 'Success') {
         update_option('supership_shop_warehouse_created', 1);
         delete_transient('supership_warehouses_list');
        } else {
            error_log('[SuperShip] Create warehouse failed: ' . ($res['message'] ?? 'Unknown error'));
        }
         if (!empty($res['results']['id'])) {
          update_option('supership_primary_warehouse_id', (int) $res['results']['id']);
        }
        return true;
    } 
    public static function maybe_create_warehouse_after_address_save()
    {
        if (!get_option('supership_need_store_address')) {
            return;
        }

        $token = self::get_token();
        if (empty($token)) {
            return;
        }

        $address = Store_Address_Extended::get_full_store_address();
        $required = [
            'warehouse_name',
            'contact_phone',
            'address',
            'commune',
            'district',
            'province',
        ];

        foreach ($required as $key) {
            if (empty($address[$key])) {
                return;
            }
        }
        $created = self::auto_create_shop_warehouse($address);
        if (!$created) {
            set_transient('supership_flash_message', [
                'type'  => 'error',
                'title' => __('Lỗi tạo kho', 'supership'),
                'text'  => __('Không thể tạo kho SuperShip. Vui lòng kiểm tra lại thông tin.', 'supership'),
            ], 30);
            return;
        }
        delete_option('supership_need_store_address');

        set_transient('supership_flash_message', [
            'type'  => 'success',
            'title' => __('Hoàn tất cấu hình', 'supership'),
            'text'  => __('Kho hàng SuperShip đã được tạo thành công.', 'supership'),
        ], 30);
    }
    
}