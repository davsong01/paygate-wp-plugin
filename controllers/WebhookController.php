<?php

namespace controllers;

use WP_REST_Request;
use WP_Error;

class WebhookController
{
    public function handle(WP_REST_Request $request)
    {
        global $wpdb;

        $payload = json_decode($request->get_body(), true);

        if (empty($payload['request_ref']) || empty($payload['details'])) {
            return new WP_Error('invalid_payload', 'Missing required fields in webhook payload.', ['status' => 400]);
        }

        $requestRef     = sanitize_text_field($payload['request_ref']);
        $requestType    = sanitize_text_field($payload['request_type'] ?? 'transaction_notification');
        $mockMode       = sanitize_text_field($payload['mock_mode'] ?? 'unknown');
        $transactionRef = sanitize_text_field($payload['details']['transaction_ref'] ?? '');
        $status         = sanitize_text_field($payload['details']['status'] ?? 'pending');
        $totalAmount    = $payload['details']['amount'] ? floatval($payload['details']['amount'])/100 : 0.00;
        $accountNumber  = sanitize_text_field($payload['details']['meta']['cr_account'] ?? '');
        $originator = isset($payload['details']['meta']) ? [
            'originator_cbn_code'       => $payload['details']['meta']['originator_cbn_code'] ?? null,
            'originator_bank_name'      => $payload['details']['meta']['originator_bank_name'] ?? null,
            'originator_account_name'   => $payload['details']['meta']['originator_account_name'] ?? null,
            'originator_account_number' => $payload['details']['meta']['originator_account_number'] ?? null,
        ] : [];

        // Lookup customer_id from reserved account
        $reservedTable = $wpdb->prefix . 'paygate_reserved_account_numbers';
        $customerId = $wpdb->get_var(
            $wpdb->prepare("SELECT customer_id FROM {$reservedTable} WHERE account_number = %s LIMIT 1", $accountNumber)
        );

        // Prepare shared data
        $transactionsTable = $wpdb->prefix . 'paygate_transactions';

        $data = [
            'customer_id'        => $customerId ?: null,
            'request_ref'        => $requestRef,
            'transaction_ref'    => $transactionRef,
            'request_type'       => $requestType,
            'status'             => $status,
            'raw_dump'           => json_encode($payload),
            'query_dump'         => null,
            'total_amount'       => $totalAmount,
            'account_number'     => $accountNumber,
            'mock_mode'          => $mockMode,
            'originator_details' => json_encode($originator),
            'updated_at'         => current_time('mysql'),
        ];

        // Check if transaction_ref already exists
        $existingTransaction = $wpdb->get_row(
            $wpdb->prepare("SELECT id, status FROM {$transactionsTable} WHERE transaction_ref = %s LIMIT 1", $transactionRef)
        );

        if ($existingTransaction) {
            if (strtolower($status) === 'successful') {
                // ✅ Already successful, avoid duplication
                return rest_ensure_response([
                    'success' => true,
                    'message' => 'Transaction already recorded as successful. Skipping.'
                ]);
            } else {
                // 🔁 Update status if not successful (e.g., retry or failure)
                $updated = $wpdb->update(
                    $transactionsTable,
                    $data,
                    ['id' => $existingTransaction->id]
                );

                if ($updated === false) {
                    return new WP_Error('db_update_error', 'Failed to update transaction.', ['status' => 500]);
                }

                return rest_ensure_response([
                    'success' => true,
                    'message' => 'Transaction updated successfully.'
                ]);
            }
        }

        // New transaction insert
        $data['created_at'] = current_time('mysql');
        $inserted = $wpdb->insert($transactionsTable, $data);

        if ($inserted === false) {
            return new WP_Error('db_insert_error', 'Failed to insert transaction.', ['status' => 500]);
        }

        return rest_ensure_response(['success' => true, 'message' => 'Transaction saved successfully.']);
    }
}
