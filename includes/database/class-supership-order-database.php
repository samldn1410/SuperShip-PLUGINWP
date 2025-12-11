<?php
if (!defined('ABSPATH')) exit;


class Order_Table {


    public static function install() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'supership_orders';
        $charset = $wpdb->get_charset_collate();
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "
        CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            wp_order_id BIGINT(20) UNSIGNED NOT NULL,
            supership_code VARCHAR(100) NULL,
            supership_shortcode VARCHAR(50) NULL,
            supership_soc VARCHAR(100) NULL,
            receiver_name VARCHAR(255) NULL,
            receiver_phone VARCHAR(30) NULL,
            receiver_address TEXT NULL,
            amount INT NULL DEFAULT 0,
            value INT NULL DEFAULT 0,
            weight INT NULL DEFAULT 0,
            fee INT NULL DEFAULT 0,
            insurance INT NULL DEFAULT 0,
            fee_return INT DEFAULT 0,           
            fee_barter INT DEFAULT 0,           
            fee_address INT DEFAULT 0,       
            payer  VARCHAR(50) NULL,    
            config VARCHAR(50) NULL,
            service VARCHAR(50) NULL,  
            barter VARCHAR(50) NULL,
            status_name VARCHAR(255) NULL,
            journeys LONGTEXT NULL,
            notes LONGTEXT NULL,
            raw_response LONGTEXT NULL,
            partial VARCHAR(50) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY supership_code (supership_code),
            KEY wp_order_id (wp_order_id)
        ) $charset;
        ";

        dbDelta($sql);
    }
}
/**
 * BẢNG LỊCH SỬ WEBHOOK
 */
class Webhook_Table {

    public static function install() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'supership_webhook_logs';
        $charset = $wpdb->get_charset_collate();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql = "
        CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            supership_code VARCHAR(100) NOT NULL,
            shortcode VARCHAR(50) NULL,
            type VARCHAR(50) NULL,
            status_name VARCHAR(255) NULL,
            reason TEXT NULL,
            raw_data LONGTEXT NULL,
            log_message VARCHAR(255) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY supership_code (supership_code),
            KEY type (type)
        ) $charset;
        ";
        dbDelta($sql);
    }
}
