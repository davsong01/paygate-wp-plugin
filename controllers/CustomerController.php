<?php

namespace controllers;

use controllers\PaygateController;

defined('ABSPATH') || exit;

class CustomerController {
    public function getCustomers()
    {
        check_ajax_referer('paygate_nonce', 'nonce');
        global $wpdb;
        $customers_table = $wpdb->prefix . 'paygate_customers';
        $accounts_table  = $wpdb->prefix . 'paygate_reserved_account_numbers';

        $results = $wpdb->get_results("
            SELECT c.*, a.bank_name, a.account_number, a.account_name
            FROM {$customers_table} c
            LEFT JOIN {$accounts_table} a ON a.customer_id = c.id
            ORDER BY c.created_at DESC
        ");

        wp_send_json_success($results);
    }

    public function saveCustomer()
    {
        check_ajax_referer('paygate_nonce', 'nonce');
        global $wpdb;

        $customers_table = $wpdb->prefix . 'paygate_customers';
        $accounts_table  = $wpdb->prefix . 'paygate_reserved_account_numbers';

        $email = sanitize_email($_POST['email']);
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $customers_table WHERE email = %s LIMIT 1", $email
        ));

        if ($exists) {
            wp_send_json_error(['message' => 'Customer with this email already exists.']);
        }

        $basicData = [
            'title'  => sanitize_text_field($_POST['title']),
            'dob'  => sanitize_text_field($_POST['dob']),
            'address_line_1'  => sanitize_text_field($_POST['address_line_1']),
            'address_line_2'  => sanitize_text_field($_POST['address_line_2']),
            'city'  => sanitize_text_field($_POST['city']),
            'state_id'  => sanitize_text_field($_POST['state_id']),
            'country_id'  => sanitize_text_field($_POST['country_id']),

            'firstname'    => sanitize_text_field($_POST['firstname']),
            'surname'    => sanitize_text_field($_POST['surname']),
            'middlename'    => sanitize_text_field($_POST['middlename']),
            'email'   => $email,
            'phone'   => sanitize_text_field($_POST['phone']),
            'gender'  => sanitize_text_field($_POST['gender']),
            'created_at' => current_time('mysql')
        ];
        
        $wpdb->insert($customers_table, $basicData);
        $customer_id = $wpdb->insert_id;


        $paygate = new PaygateController();

        $apiResponse = $paygate->generateReservedAccount('/v2/transact', $basicData);
        $data = $apiResponse['data']['provider_response'] ?? [];

        if (empty($data['account_number']) || empty($data['account_name']) || empty($data['bank_name'])) {
            wp_send_json_error(['message' => 'Customer created, but failed to get virtual account.']);
        }


        $wpdb->insert($accounts_table, [
            'customer_id'    => $customer_id,
            'bank_name'      => $data['bank_name'] ?? 'Fidelity',
            'account_number' => $data['account_number'],
            'account_name'   => $data['account_name'],
            'provider'       => 'paygateplus',
            'meta'           => maybe_serialize($data),
            'created_at'     => current_time('mysql')
        ]);

        wp_send_json_success(['message' => 'Customer created successfully']);
    }

    public function generateAccountNumber()
    {
        check_ajax_referer('paygate_nonce', 'nonce');

        $customerId = intval($_POST['customer_id']);
        if (!$customerId) {
            wp_send_json_error(['message' => 'Invalid customer ID.']);
        }

        global $wpdb;

        $customersTable = $wpdb->prefix . 'paygate_customers';
        $statesTable = $wpdb->prefix . 'paygate_states';
        $countriesTable = $wpdb->prefix . 'paygate_countries';
        $reservedAccountsTable = $wpdb->prefix . 'paygate_reserved_account_numbers';

        // 1. Fetch customer with joined state and country name
        $customer = $wpdb->get_row($wpdb->prepare("
            SELECT c.*, s.name AS state_name, co.name AS country_name
            FROM $customersTable c
            LEFT JOIN $statesTable s ON c.state_id = s.id
            LEFT JOIN $countriesTable co ON c.country_id = co.id
            WHERE c.id = %d
        ", $customerId));

        if (!$customer) {
            wp_send_json_error(['message' => 'Customer not found.']);
        }

        // 2. Prepare data to send to API
        $basicData = [
            'title'           => sanitize_text_field($customer->title),
            'dob'             => date('Y-m-d', strtotime($customer->dob)),
            'address_line_1'  => sanitize_text_field($customer->address_line_1),
            'address_line_2'  => sanitize_text_field($customer->address_line_2),
            'city'            => sanitize_text_field($customer->city),
            'state'           => sanitize_text_field($customer->state_name),
            'country'         => sanitize_text_field($customer->country_name),
            'firstname'       => sanitize_text_field($customer->firstname),
            'surname'         => sanitize_text_field($customer->surname),
            'middlename'      => sanitize_text_field($customer->middlename),
            'email'           => sanitize_email($customer->email),
            'phone'           => sanitize_text_field($customer->phone),
            'gender'          => sanitize_text_field($customer->gender),
            'created_at'      => current_time('mysql')
        ];

        // 3. Call Paygate API
        $paygate = new PaygateController();
        $apiResponse = $paygate->generateReservedAccount('/v2/transact', $basicData);

        if (is_wp_error($apiResponse)) {
            wp_send_json_error(['message' => $apiResponse->get_error_message()]);
        }

        $data = $apiResponse['data']['provider_response'] ?? [];

        if (empty($data['account_number']) || empty($data['account_name']) || empty($data['bank_name'])) {
            wp_send_json_error(['message' => 'API did not return valid account details.']);
        }

        // 5. Save the reserved account to DB
        $wpdb->insert($reservedAccountsTable, [
            'customer_id'    => $customerId,
            'bank_name'      => sanitize_text_field($data['bank_name']),
            'account_number' => sanitize_text_field($data['account_number']),
            'account_name'   => sanitize_text_field($data['account_name']),
            'provider'       => sanitize_text_field($data['provider'] ?? 'Unknown'),
            'meta'           => maybe_serialize($data),
            'created_at'     => current_time('mysql')
        ]);

        wp_send_json_success(['message' => 'Account number generated and saved successfully.']);
    }


    public function updateCustomer()
    {
        check_ajax_referer('paygate_nonce', 'nonce');
        global $wpdb;

        $customers_table = $wpdb->prefix . 'paygate_customers';

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        if (!$id) {
            wp_send_json_error(['message' => 'Invalid customer ID.']);
        }

        $email = sanitize_email($_POST['email']);

        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $customers_table WHERE email = %s AND id != %d LIMIT 1",
            $email,
            $id
        ));

        if ($exists) {
            wp_send_json_error(['message' => 'Another customer with this email already exists.']);
        }

        $data = [
            'title'  => sanitize_text_field($_POST['title']),
            'dob'  => sanitize_text_field($_POST['dob']),
            'address_line_1'  => sanitize_text_field($_POST['address_line_1']),
            'address_line_2'  => sanitize_text_field($_POST['address_line_2']),
            'city'  => sanitize_text_field($_POST['city']),
            'state_id'  => sanitize_text_field($_POST['state_id']),
            'country_id'  => sanitize_text_field($_POST['country_id']),

            'firstname'    => sanitize_text_field($_POST['firstname']),
            'surname'    => sanitize_text_field($_POST['surname']),
            'middlename'    => sanitize_text_field($_POST['middlename']),
            'email'   => $email,
            'phone'   => sanitize_text_field($_POST['phone']),
            'gender'  => sanitize_text_field($_POST['gender']),
            'updated_at' => current_time('mysql')
        ];


        $updated = $wpdb->update($customers_table, $data, ['id' => $id]);

        if ($updated === false) {
            wp_send_json_error(['message' => 'Failed to update customer.']);
        }

        wp_send_json_success(['message' => 'Customer updated successfully.']);
    }

    public function deleteCustomer()
    {
        check_ajax_referer('paygate_nonce', 'nonce');
        global $wpdb;
        $wpdb->delete($wpdb->prefix . 'paygate_customers', ['id' => intval($_POST['id'])]);
        wp_send_json_success(['message' => 'Customer deleted']);
    }

    public function getCountries()
    {
        global $wpdb;
        $table = $wpdb->prefix . 'paygate_countries';
        $results = $wpdb->get_results("SELECT id, name FROM $table ORDER BY name ASC");

        wp_send_json_success($results);
    }

    public function getStatesByCountry()
    {
        global $wpdb;

        $countryId = intval($_POST['country_id'] ?? 0);
        // error_log("Country ID: " . $countryId);

        $table = $wpdb->prefix . 'paygate_states';
        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT id, name FROM $table WHERE country_id = %d ORDER BY name ASC", $countryId)
        );

        wp_send_json_success($results);
    }

}