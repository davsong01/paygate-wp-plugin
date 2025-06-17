<?php

namespace PayGate\Controllers;

defined('ABSPATH') || exit;

class CustomerController {
    public function getCustomers()
    {
        check_ajax_referer('paygate_customers', 'nonce');
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
        check_ajax_referer('paygate_customers', 'nonce');
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

        $data = [
            'name'    => sanitize_text_field($_POST['name']),
            'email'   => $email,
            'phone'   => sanitize_text_field($_POST['phone']),
            'gender'  => sanitize_text_field($_POST['gender']),
            'address' => sanitize_text_field($_POST['address']),
            'created_at' => current_time('mysql')
        ];

        $wpdb->insert($customers_table, $data);
        $customer_id = $wpdb->insert_id;

        // 🔁 Call reserved account API
        $account = $this->getReservedAccount($data); // implement this

        if (!$account) {
            wp_send_json_error(['message' => 'Customer created, but failed to generate reserved account.']);
        }

        // Save reserved account
        $wpdb->insert($accounts_table, [
            'customer_id'    => $customer_id,
            'bank_name'      => $account['bank_name'],
            'account_number' => $account['account_number'],
            'account_name'   => $account['account_name'],
            'provider'       => $account['provider'],
            'meta'           => maybe_serialize($account['meta']),
            'created_at'     => current_time('mysql')
        ]);

        wp_send_json_success(['message' => 'Customer created successfully']);
    }

    private function getReservedAccount($customer)
    {
        // Simulated response – replace with actual API call
        return [
            'bank_name'      => 'Sample Bank',
            'account_number' => '1234567890',
            'account_name'   => $customer['name'],
            'provider'       => 'SampleProvider',
            'meta'           => ['ref_id' => uniqid()]
        ];
    }

    public function deleteCustomer()
    {
        check_ajax_referer('paygate_customers', 'nonce');
        global $wpdb;
        $wpdb->delete($wpdb->prefix . 'paygate_customers', ['id' => intval($_POST['id'])]);
        wp_send_json_success(['message' => 'Customer deleted']);
    }

}