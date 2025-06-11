<?php

if (!defined('ABSPATH')) {
    exit;
}

class Paygate_Activator
{
    public static function activate()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $transactions_table = $wpdb->prefix . 'paygate_transactions';
        $shortcodes_table   = $wpdb->prefix . 'paygate_shortcodes';
        $settings_table     = $wpdb->prefix . 'paygate_settings';

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $sql_transactions = "CREATE TABLE {$transactions_table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            transaction_id VARCHAR(40) NOT NULL UNIQUE,
            product_name VARCHAR(255) DEFAULT NULL,
            customer_email VARCHAR(100) NULL,
            customer_phone VARCHAR(20) NULL,
            customer_id BIGINT(20) UNSIGNED DEFAULT NULL,
            status ENUM('initiated', 'in-progress', 'completed', 'failed') DEFAULT 'initiated',
            account_number VARCHAR(20) DEFAULT NULL,
            account_name VARCHAR(100) DEFAULT NULL,
            bank_name VARCHAR(100) DEFAULT NULL,
            pauyload_json LONGTEXT DEFAULT NULL,
            response_json LONGTEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        $sql_shortcodes = "CREATE TABLE {$shortcodes_table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            slug VARCHAR(100) NOT NULL UNIQUE,
            button_text VARCHAR(255) NULL,
            initiation_endpoint VARCHAR(255) NULL,
            product_name VARCHAR(255) DEFAULT NULL,
            additional_payload TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        $sql_settings = "CREATE TABLE {$settings_table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            option_key VARCHAR(100) NOT NULL UNIQUE,
            option_value TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        dbDelta($sql_transactions);
        dbDelta($sql_shortcodes);
        dbDelta($sql_settings);
    }
}
