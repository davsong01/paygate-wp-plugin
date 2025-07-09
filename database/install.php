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
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            sku VARCHAR(255) UNIQUE NOT NULL,
            price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            in_stock INT NOT NULL DEFAULT 0,
            status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // 2. Customers Table
        $tables[] = "CREATE TABLE {$wpdb->prefix}paygate_customers (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            wp_user_id BIGINT UNSIGNED NULL,
            firstname VARCHAR(255),
            surname VARCHAR(255),
            middlename VARCHAR(255),
            title VARCHAR(15),
            email VARCHAR(255),
            phone VARCHAR(50),
            gender VARCHAR(10),
            address_line_1 VARCHAR(255),
            address_line_2 VARCHAR(255),
            dob DATE,
            country_id BIGINT UNSIGNED,
            state_id BIGINT UNSIGNED,
            city VARCHAR(255),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // 3. Transactions Table
        $tables[] = "CREATE TABLE {$wpdb->prefix}paygate_transactions (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            customer_id BIGINT UNSIGNED,
            request_ref VARCHAR(80),
            transaction_ref VARCHAR(80),
            request_type VARCHAR(100),
            status VARCHAR(10) NOT NULL DEFAULT 'pending',
            raw_dump LONGTEXT,
            query_dump LONGTEXT,
            total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            account_number VARCHAR(10),
            mock_mode VARCHAR(50),
            originator_details TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            FOREIGN KEY (customer_id) REFERENCES {$wpdb->prefix}paygate_customers(id) ON DELETE CASCADE
        ) $charset_collate;";

        // 4. Reserved Account Numbers Table
        $tables[] = "CREATE TABLE {$wpdb->prefix}paygate_reserved_account_numbers (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            customer_id BIGINT UNSIGNED NOT NULL,
            bank_name VARCHAR(50) NOT NULL,
            bank_code VARCHAR(20),
            account_number VARCHAR(50) NOT NULL,
            account_name VARCHAR(255) NOT NULL,
            provider VARCHAR(100) NOT NULL,
            meta LONGTEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            FOREIGN KEY (customer_id) REFERENCES {$wpdb->prefix}paygate_customers(id) ON DELETE CASCADE
        ) $charset_collate;";

        // 5. Settings Table
        $tables[] = "CREATE TABLE {$wpdb->prefix}paygate_settings (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            option_key VARCHAR(191) NOT NULL UNIQUE,
            option_value LONGTEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // 6. Countries Table
        $tables[] = "CREATE TABLE {$wpdb->prefix}paygate_countries (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            code VARCHAR(10),
            mobile_code VARCHAR(10),
            currency_symbol VARCHAR(10),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // 7. States Table
        $tables[] = "CREATE TABLE {$wpdb->prefix}paygate_states (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            country_id BIGINT UNSIGNED NOT NULL,
            name VARCHAR(255) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            FOREIGN KEY (country_id) REFERENCES {$wpdb->prefix}paygate_countries(id) ON DELETE CASCADE
        ) $charset_collate;";

        // Run all table creations
        foreach ($tables as $sql) {
            dbDelta($sql);
        }

        // Seed countries and states if empty
        $country_table = $wpdb->prefix . 'paygate_countries';
        if ((int) $wpdb->get_var("SELECT COUNT(*) FROM {$country_table}") === 0) {
            self::seedCountriesAndStates();
        }

        // 8. Create accountant role if it doesn't exist
        if (!get_role('accountant')) {
            add_role('accountant', 'Accountant', ['read' => true]);
        }

        // 9. Add 'view_paygate' capability to both accountant and administrator
        $rolesToUpdate = ['accountant', 'administrator'];
        foreach ($rolesToUpdate as $roleName) {
            $role = get_role($roleName);
            if ($role && !$role->has_cap('view_paygate')) {
                $role->add_cap('view_paygate');
            }
        }

        // 10. Create default accountant user
        if (!username_exists('paygate_accountant') && !email_exists('accountant@biopharma.com')) {
            $user_id = wp_create_user('paygate_accountant', '#&securepassword123', 'accountant@biopharma.com');
            if (!is_wp_error($user_id)) {
                $user = new \WP_User($user_id);
                $user->set_role('accountant');
            }
        }

        // 11. Seed additional settings and customers
        self::seedSettings();
        self::seedCustomers();
    }


    public static function seedSettings()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'paygate_settings';

        $defaultSettings = [
            [
                'option_key' => 'base_url',
                'option_value' => 'https://api.paygateplus.ng',
            ],
            [
                'option_key' => 'default_country_code',
                'option_value' => 'NG',
            ],
            [
                'option_key' => 'default_currency',
                'option_value' => '₦',
            ],
            [
                'option_key' => 'api_secret',
                'option_value' => 'Wui2qX8B7Xm3DFhm',
            ],
            [
                'option_key' => 'api_key',
                'option_value' => 'VCNtcZScX7GPT61d8djp_eaddf0df24764a5db2bd53f6041199c5',
            ],
            
            [
                'option_key' => 'application_mode',
                'option_value' => 'development',
            ],
        ];

        foreach ($defaultSettings as $setting) {
            $exists = $wpdb->get_var(
                $wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE option_key = %s", $setting['option_key'])
            );

            if (!$exists) {
                $wpdb->insert($table, [
                    'option_key' => $setting['option_key'],
                    'option_value' => maybe_serialize($setting['option_value']),
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),
                ]);
            }
        }
    }

    public static function seedCustomers()
    {
        global $wpdb;

        $customerTable = $wpdb->prefix . 'paygate_customers';
        $reservedTable = $wpdb->prefix . 'paygate_reserved_account_numbers';

        $customers = [
            [
                'firstname' => 'John',
                'surname' => 'Doe',
                'middlename' => 'M',
                'email' => 'john.doe@example.com',
                'phone' => '08012345678',
                'gender' => 'male',
                'dob' => '1990-01-01',
                'title' => 'mr',
                'address_line_1' => '123 Main Street',
                'address_line_2' => 'Apt 4B',
                'city' => 'Lagos',
                'state_id' => 1,
                'country_id' => 1,
            ],
            [
                'firstname' => 'Jane',
                'surname' => 'Smith',
                'middlename' => 'A',
                'email' => 'jane.smith@example.com',
                'phone' => '08098765432',
                'gender' => 'female',
                'dob' => '1992-05-20',
                'title' => 'ms',
                'address_line_1' => '456 Broad Street',
                'address_line_2' => '',
                'city' => 'Abuja',
                'state_id' => 2,
                'country_id' => 1,
            ],
            [
                'firstname' => 'David',
                'surname' => 'Okoro',
                'middlename' => 'T',
                'email' => 'david.okoro@example.com',
                'phone' => '08120000001',
                'gender' => 'male',
                'dob' => '1988-08-15',
                'title' => 'mr',
                'address_line_1' => '7 Unity Rd',
                'address_line_2' => '',
                'city' => 'Ibadan',
                'state_id' => 3,
                'country_id' => 1,
            ],
            [
                'firstname' => 'Linda',
                'surname' => 'Ibrahim',
                'middlename' => 'K',
                'email' => 'linda.ibrahim@example.com',
                'phone' => '07030000003',
                'gender' => 'female',
                'dob' => '1995-12-25',
                'title' => 'mrs',
                'address_line_1' => '10 Peace Ave',
                'address_line_2' => '',
                'city' => 'Kaduna',
                'state_id' => 4,
                'country_id' => 1,
            ],
            [
                'firstname' => 'Emeka',
                'surname' => 'Okafor',
                'middlename' => 'C',
                'email' => 'emeka.okafor@example.com',
                'phone' => '09090000005',
                'gender' => 'male',
                'dob' => '1985-03-10',
                'title' => 'mr',
                'address_line_1' => '22 Market Road',
                'address_line_2' => 'Suite 5',
                'city' => 'Enugu',
                'state_id' => 5,
                'country_id' => 1,
            ],
        ];

        foreach ($customers as $index => $customer) {
            $email = $customer['email'];

            $customerId = $wpdb->get_var(
                $wpdb->prepare("SELECT id FROM {$customerTable} WHERE email = %s", $email)
            );

            if (!$customerId) {
                $wpdb->insert($customerTable, [
                    'firstname'        => $customer['firstname'],
                    'surname'          => $customer['surname'],
                    'middlename'       => $customer['middlename'],
                    'email'            => $customer['email'],
                    'phone'            => $customer['phone'],
                    'gender'           => $customer['gender'],
                    'dob'              => $customer['dob'],
                    'title'            => $customer['title'],
                    'address_line_1'   => $customer['address_line_1'],
                    'address_line_2'   => $customer['address_line_2'],
                    'city'             => $customer['city'],
                    'state_id'         => $customer['state_id'],
                    'country_id'       => $customer['country_id'],
                    'created_at'       => current_time('mysql'),
                    'updated_at'       => current_time('mysql'),
                ]);

                $customerId = $wpdb->insert_id;
            }

            // 👇 Set first customer account number as 4571111111, others as 100000000X
            $accountNumber = $index === 0
                ? '4571111111'
                : str_pad((string)(1000000000 + $index + 1), 10, '0', STR_PAD_LEFT);

            $existingAcct = $wpdb->get_var(
                $wpdb->prepare("SELECT id FROM {$reservedTable} WHERE account_number = %s", $accountNumber)
            );

            if (!$existingAcct) {
                $wpdb->insert($reservedTable, [
                    'customer_id'     => $customerId,
                    'account_number'  => $accountNumber,
                    'bank_code'       => '999',
                    'bank_name'       => 'Test Bank',
                    'account_name'    => "{$customer['firstname']} {$customer['surname']}",
                    'created_at'      => current_time('mysql'),
                    'updated_at'      => current_time('mysql'),
                ]);
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
