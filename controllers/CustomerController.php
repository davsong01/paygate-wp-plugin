<?php

namespace PayGate\Controllers;

use PayGate\Controllers\PaygateController;

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
            ORDER BY c.id DESC
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

            'name'    => sanitize_text_field($_POST['name']),
            'email'   => $email,
            'phone'   => sanitize_text_field($_POST['phone']),
            'gender'  => sanitize_text_field($_POST['gender']),
            'created_at' => current_time('mysql')
        ];
        
        $wpdb->insert($customers_table, $basicData);
        $customer_id = $wpdb->insert_id;

        
        $paygate = new PaygateController();
        $apiResponse = $paygate->callPayGateApi('/v2/transact', $basicData);

        if (!$apiResponse['success'] || empty($apiResponse['data']['data']['account_number'])) {
            wp_send_json_error(['message' => 'Customer created, but failed to get virtual account.']);
        }

        $accountDetails = $apiResponse['data']['data'];


        $wpdb->insert($accounts_table, [
            'customer_id'    => $customer_id,
            'bank_name'      => $accountDetails['bank_name'] ?? 'Fidelity',
            'account_number' => $accountDetails['account_number'],
            'account_name'   => $accountDetails['account_name'],
            'provider'       => 'paygateplus',
            'meta'           => maybe_serialize($accountDetails),
            'created_at'     => current_time('mysql')
        ]);

        wp_send_json_success(['message' => 'Customer created successfully']);
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

            'name'    => sanitize_text_field($_POST['name']),
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
        error_log("Country ID: " . $countryId);

        $table = $wpdb->prefix . 'paygate_states';
        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT id, name FROM $table WHERE country_id = %d ORDER BY name ASC", $countryId)
        );

        wp_send_json_success($results);
    }

}