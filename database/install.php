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
            firstname VARCHAR(255),
            surname VARCHAR(255) NULL,
            middlename VARCHAR(255) NULL,
            title VARCHAR(15) NULL,
            email VARCHAR(255),
            phone VARCHAR(50) NULL,
            gender VARCHAR(10),
            address_line_1 VARCHAR(255) NULL,
            address_line_2 VARCHAR(255) NULL,
            dob DATE NULL,
            country_id BIGINT UNSIGNED NULL,
            state_id BIGINT UNSIGNED NULL,
            city VARCHAR(255) NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP

        ) $charset_collate;";

        // 3. Transactions Table
        $tables[] = "CREATE TABLE {$wpdb->prefix}paygate_transactions (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            customer_id BIGINT UNSIGNED NULL,
            request_ref VARCHAR(80) NULL,
            transaction_ref VARCHAR(80) NULL,
            request_type VARCHAR(100) NULL,
            status VARCHAR(10) NOT NULL DEFAULT 'pending',
            raw_dump LONGTEXT NULL,
            query_dump LONGTEXT NULL,
            total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            account_number VARCHAR(10) NULL,
            mock_mode VARCHAR(50) NULL,
            originator_details TEXT (100) NULL,

            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (customer_id) REFERENCES {$wpdb->prefix}paygate_customers(id) ON DELETE CASCADE
        ) $charset_collate;";


        // 4. Transaction Items Table
        // $tables[] = "CREATE TABLE {$wpdb->prefix}paygate_transaction_items (
        //     id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        //     transaction_id BIGINT UNSIGNED NOT NULL,
        //     product_id BIGINT UNSIGNED NOT NULL,
        //     quantity INT NOT NULL DEFAULT 1,
        //     price DECIMAL(10,2) NOT NULL,
        //     created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        //     FOREIGN KEY (transaction_id) REFERENCES {$wpdb->prefix}paygate_transactions(id) ON DELETE CASCADE,
        //     FOREIGN KEY (product_id) REFERENCES {$wpdb->prefix}paygate_products(id) ON DELETE CASCADE
        // ) $charset_collate;";

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
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
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

        // 7. Countries Table
        $tables[] = "CREATE TABLE {$wpdb->prefix}paygate_countries (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            code VARCHAR(10) DEFAULT NULL,
            mobile_code VARCHAR(10) DEFAULT NULL,
            currency_symbol VARCHAR(10) DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";


        // 8. States Table
        $tables[] = "CREATE TABLE {$wpdb->prefix}paygate_states (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            country_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (country_id) REFERENCES {$wpdb->prefix}paygate_countries(id) ON DELETE CASCADE
        ) $charset_collate;";


        foreach ($tables as $sql) {
            dbDelta($sql);
        }

        $country_table = $wpdb->prefix . 'paygate_countries';
        if ((int) $wpdb->get_var("SELECT COUNT(*) FROM {$country_table}") === 0) {
            self::seedCountriesAndStates();
        }

        // 9. Add 'accountant' role (if not exists)
        if (!get_role('accountant')) {
            add_role('accountant', 'Accountant', [
                'read' => true,
            ]);
        }

        // 10. Add custom capability to both accountant and administrator roles
        $custom_cap = 'manage_paygate';

        // Assign to accountant
        $accountant_role = get_role('accountant');
        if ($accountant_role && !$accountant_role->has_cap($custom_cap)) {
            $accountant_role->add_cap($custom_cap);
        }

        // Assign to administrator
        $admin_role = get_role('administrator');
        if ($admin_role && !$admin_role->has_cap($custom_cap)) {
            $admin_role->add_cap($custom_cap);
        }

        // 11. Create default user with 'accountant' role
        if (!username_exists('paygate_accountant') && !email_exists('accountant@biopharma.com')) {
            $user_id = wp_create_user('paygate_accountant', '#&securepassword123', 'accountant@biopharma.com');
            if (!is_wp_error($user_id)) {
                $user = new WP_User($user_id);
                $user->set_role('accountant');
            }
        }
    }

    public static function seedCountriesAndStates(){
        global $wpdb;
        $country_table = $wpdb->prefix . 'paygate_countries';
        $state_table = $wpdb->prefix . 'paygate_states';
        $countries = [
            [
                'name' => 'Nigeria',
                'code' => 'NG',
                'currency_symbol' => '₦',
                'mobile_code' => '+234',
            ],
        ];
        // 1. Insert Countries
        $country_id_map = [];

        foreach ($countries as $country) {
            $wpdb->insert($country_table, [
                'name' => $country['name'],
                'currency_symbol' => $country['currency_symbol'],
                'mobile_code' => $country['mobile_code'],
                'code' => strtoupper($country['code']),
                'created_at' => current_time('mysql'),
            ]);
            $country_id_map[strtoupper($country['code'])] = $wpdb->insert_id;
        }

        $states = self::states();
        // 2. Insert States (mapped to countries via code)
        foreach ($states as $state) {
            $wpdb->insert($state_table, [
                'country_id' => $state['country_id'],
                'name' => $state['name'],
                'created_at' => current_time('mysql'),
            ]);
        }
    }

    
    public static function states(){
        return [
            [
                "id" => "1",
                "name" => "Abia State",
                "status" => "1",
                "country_id" => "1",
                "created_at" => null,
                "updated_at" =>
                "2025-04-09 07=>02=>56"
            ],
            ["id" => "2", "name" => "Adamawa State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "3", "name" => "Akwa Ibom State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "4", "name" => "Anambra State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "5", "name" => "Bauchi State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "6", "name" => "Bayelsa State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "7", "name" => "Benue State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "8", "name" => "Borno State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "9", "name" => "Cross River State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "10", "name" => "Delta State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "11", "name" => "Ebonyi State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "12", "name" => "Edo State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "13", "name" => "Ekiti State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "14", "name" => "Enugu State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "15", "name" => "FCT", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "16", "name" => "Gombe State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "17", "name" => "Imo State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "18", "name" => "Jigawa State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "19", "name" => "Kaduna State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "20", "name" => "Kano State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "21", "name" => "Katsina State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "22", "name" => "Kebbi State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "23", "name" => "Kogi State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "24", "name" => "Kwara State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "25", "name" => "Lagos State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "26", "name" => "Nasarawa State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "27", "name" => "Niger State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "28", "name" => "Ogun State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "29", "name" => "Ondo State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "30", "name" => "Osun State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "31", "name" => "Oyo State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "32", "name" => "Plateau State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "33", "name" => "Rivers State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "34", "name" => "Sokoto State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "35", "name" => "Taraba State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "36", "name" => "Yobe State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"],
            ["id" => "37", "name" => "Zamfara State", "status" => "1", "country_id" => "1", "created_at" => null, "updated_at" => "2025-04-09 07=>02=>56"]
        ];
    }
}
