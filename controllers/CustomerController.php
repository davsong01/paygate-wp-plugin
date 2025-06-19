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
            // "customer_ref": "2348033000989",
            // "firstname": "John",
            // "surname": "Doe",
            // "email": "john@doe.com",
            // "mobile_no": "2348033000989"
            // "middlename": "Jane",
            // "dob": "2005-05-13",
            // "gender": "M",
            // "title": "Mr",
            // "address_line_1": "23, Okon street, Ikeja",
            // "address_line_2": "Ikeja",
            // "city": "Mushin",
            // "state": "Lagos State",
            // "country": "Nigeria"


            'title'  => sanitize_text_field($_POST['title']),
            'dob'  => sanitize_text_field($_POST['dob']),
            'address_line_1'  => sanitize_text_field($_POST['adrress']),
            'address_line_2'  => sanitize_text_field($_POST['adrress']),
            'city'  => sanitize_text_field($_POST['city']),
            'state'  => sanitize_text_field($_POST['state']),
            'country'  => sanitize_text_field($_POST['country']),

            'name'    => sanitize_text_field($_POST['name']),
            'email'   => $email,
            'phone'   => sanitize_text_field($_POST['phone']),
            'gender'  => sanitize_text_field($_POST['gender']),
            'address' => sanitize_text_field($_POST['address']),
            'created_at' => current_time('mysql')
        ];
        
        $wpdb->insert($customers_table, $basicData);
        $customer_id = $wpdb->insert_id;

        $accountPayload = [
            'customer_name'    => $data['name'],
            'email'            => $data['email'],
            'phone_number'     => $data['phone'],
            'bvn'              => '12345678901', // optional if you don't have BVN
            'transaction_ref'  => 'CUST_' . time(),
            'preferred_bank'   => 'Fidelity' // optional or based on API docs
        ];

        $paygate = new PaygateController();
        $apiResponse = $paygate->callPayGateApi('/v2/transact', $accountPayload);

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

        // ✅ Check if email belongs to another customer
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $customers_table WHERE email = %s AND id != %d LIMIT 1",
            $email,
            $id
        ));

        if ($exists) {
            wp_send_json_error(['message' => 'Another customer with this email already exists.']);
        }

        $data = [
            'name'       => sanitize_text_field($_POST['name']),
            'email'      => $email,
            'phone'      => sanitize_text_field($_POST['phone']),
            'gender'     => sanitize_text_field($_POST['gender']),
            'address'    => sanitize_text_field($_POST['address']),
            'updated_at' => current_time('mysql'),
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

}