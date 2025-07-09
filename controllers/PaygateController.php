<?php

namespace controllers;

use WP_Error;

class PaygateController
{
    protected static $settings = [];

    /**
     * Fetch a single setting from wp_paygate_settings
     */
    public function getSetting($key)
    {
        global $wpdb;
        $table = $wpdb->prefix . 'paygate_settings';

        if (empty(self::$settings)) {
            $results = $wpdb->get_results("SELECT option_key, option_value FROM {$table}");
            foreach ($results as $row) {
                self::$settings[$row->option_key] = $row->option_value;
            }
        }

        return self::$settings[$key] ?? null;
    }

    private function generateRequestRef()
    {
        return 'PG_' . uniqid() . '_' . time();
    }

    /**
     * Perform an API call to PayGate
     */
    public function generateReservedAccount($endpoint, $payload, $requestRef = null)
    {
        $apiKey    = $this->getSetting('api_key');
        $apiSecret = $this->getSetting('api_secret');
        $baseUrl   = rtrim($this->getSetting('base_url'), '/');

        if (!$apiKey || !$apiSecret || !$baseUrl) {
            return new WP_Error('missing_credentials', 'API credentials are not configured correctly.');
        }

        if (!$requestRef) {
            $requestRef = $this->generateRequestRef();
        }

        $signature = md5($requestRef . ';' . $apiSecret);
        $url = $baseUrl . '/' . ltrim($endpoint, '/');

        $headers = [
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey,
            'Signature'     => $signature,
        ];

        $payloadX['request_ref'] = $requestRef;
        $payloadX['request_type'] = 'open_account';
        $payloadX['auth'] = [
            "type" => null,
            "secure" => null,
            "auth_provider" => "FidelityVirtual",
            "route_mode" => null
        ];

        $payloadX['transaction'] = [
            "mock_mode" => $this->getSetting('application_mode') == 'development' ? "Inspect" : "Live",
            "transaction_ref" => $requestRef,
            "transaction_desc" => "Biopharma Generated",
            "transaction_ref_parent" => null,
            "amount" => 0,
            "customer" => [
                "customer_ref" => $payload['phone'] ?? '',
                "firstname" => $payload['firstname'],
                "surname" => $payload['surname'] ?? '',
                "email" => $payload['email'],
                "mobile_no" => $payload['phone'] ?? ''
            ],
            "meta" => [
                "a_key" => $this->getSetting('api_key'),
                "b_key" => $this->getSetting('secret_key')
            ],
            "details" =>[
                "name_on_account" => $payload['firstname'] . ' ' . ($payload['surname'] ?? ''),
                "middlename" => $payload['firstname'] ?? '',
                "dob" => !empty($payload['dob']) ? date('Y-m-d', strtotime($payload['dob'])) : null,
                "gender" => isset($payload['gender']) ? ucfirst(substr($payload['gender'], 0, 1)) : 'M',
                "title" => ucfirst($payload['title']),
                "address_line_1" => $payload['address_line_1'],
                "address_line_2" => $payload['address_line_2'],
                "city" => $payload['city'],
                "state" => $payload['state'],
                "country" => $payload['country']
            ],
        ];

        $response = wp_remote_post($url, [
            'headers' => $headers,
            'body'    => json_encode($payloadX),
            'timeout' => 20,
        ]);

        // error_log("PayGate Payload: " . json_encode($payloadX, JSON_PRETTY_PRINT));
        // error_log("PayGate Header: " . json_encode($headers, JSON_PRETTY_PRINT));
        // error_log("PayGate Payload: " . json_encode($response));

        if (is_wp_error($response)) {
            return $response;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        return $body;
    }
}
