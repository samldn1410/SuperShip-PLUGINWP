<?php
if (!defined('ABSPATH')) exit;

class API {

    private static function headers() {
        return [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . Settings::get_token()
        ];
    }

    public static function get($endpoint, $params = []) {

        $url = "https://api.mysupership.vn{$endpoint}";
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $res = wp_remote_get($url, ['headers' => self::headers()]);
        return self::handle($res);
    }

    public static function post($endpoint, $body = []) {

        $res = wp_remote_post("https://api.mysupership.vn{$endpoint}", [
            'headers' => array_merge(self::headers(), ['Content-Type' => 'application/json']),
            'body' => json_encode($body)
        ]);

        return self::handle($res);
    }

    private static function handle($res) {

        if (is_wp_error($res)) {
            return ['status' => 'Error', 'message' => $res->get_error_message()];
        }

        $body = json_decode(wp_remote_retrieve_body($res), true);
        return $body ?: ['status' => 'Error', 'message' => 'Invalid JSON'];
    }
}
