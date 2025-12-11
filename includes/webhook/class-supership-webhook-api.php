<?php
if (!defined('ABSPATH')) exit;
class Webhook_API {
    public static function get_webhook() {
        // error_log("");
        return API::get('/v1/partner/webhooks');
    }
    
    public static function create_webhook($url) {

        if (!$url) {
            return [
                'status'  => 'Error',
                'message' => 'Thiếu URL webhook.'
            ];
        }
       
        return API::post('/v1/partner/webhooks/create', [
            'url' => $url
        ]);
    }
}
