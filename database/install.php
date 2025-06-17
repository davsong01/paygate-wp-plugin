<?php

namespace PayGate\Database;

defined('ABSPATH') || exit;

class Installer
{
    public static function install()
    {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset_collate = $wpdb->get_charset_collate();

        $tables = [];

        // 1. Products Table
        $tables[] = "CREATE TABLE {$wpdb->prefix}paygate_products (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            sku VARCHAR(255) UNIQUE NOT NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            in_stock INT NOT NULL DEFAULT 0,
            status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;";

        // 2. Customers Table
        $tables[] = "CREATE TABLE {$wpdb->prefix}paygate_customers (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            wp_user_id BIGINT UNSIGNED NULL,
            name VARCHAR(255),
            email VARCHAR(255),
            phone VARCHAR(50),
            gender VARCHAR(10),
            address VARCHAR(255),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";

        // 3. Transactions Table
        $tables[] = "CREATE TABLE {$wpdb->prefix}paygate_transactions (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            customer_id BIGINT UNSIGNED NOT NULL,
            invoice_id VARCHAR(300) NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            status ENUM('initiated','paid','failed') DEFAULT 'initiated',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES {$wpdb->prefix}paygate_customers(id) ON DELETE CASCADE
        ) $charset_collate;";

        // 4. Transaction Items Table
        $tables[] = "CREATE TABLE {$wpdb->prefix}paygate_transaction_items (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            transaction_id BIGINT UNSIGNED NOT NULL,
            product_id BIGINT UNSIGNED NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            price DECIMAL(10,2) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (transaction_id) REFERENCES {$wpdb->prefix}paygate_transactions(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES {$wpdb->prefix}paygate_products(id) ON DELETE CASCADE
        ) $charset_collate;";

        // 5. Reserved Account Numbers Table
        $tables[] = "CREATE TABLE {$wpdb->prefix}paygate_reserved_account_numbers (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            customer_id BIGINT UNSIGNED NOT NULL,
            bank_name VARCHAR(50) NOT NULL,
            account_number VARCHAR(50) NOT NULL,
            account_name VARCHAR(255) NOT NULL,
            provider VARCHAR(100) NOT NULL,
            meta LONGTEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES {$wpdb->prefix}paygate_customers(id) ON DELETE CASCADE
        ) $charset_collate;";

        // 6. Settings Table (key-value store)
        $tables[] = "CREATE TABLE {$wpdb->prefix}paygate_settings (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            option_key VARCHAR(191) NOT NULL UNIQUE,
            option_value LONGTEXT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) $charset_collate;";
        foreach ($tables as $sql) {
            dbDelta($sql);
        }
    }
}
