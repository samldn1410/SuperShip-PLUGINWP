<?php
// Nếu không phải WP gọi trực tiếp → chặn
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// Danh sách bảng cần xóa
$tables = [
    $wpdb->prefix . 'supership_orders',
    $wpdb->prefix . 'supership_webhook_logs',
];

// Xóa bảng nếu tồn tại
foreach ($tables as $table) {
    $wpdb->query("DROP TABLE IF EXISTS $table");
}

// Xóa option plugin nếu có
delete_option('ss_db_version');
delete_option('supership_access_token');

