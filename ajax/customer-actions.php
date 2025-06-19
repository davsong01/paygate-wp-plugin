<?php

use PayGate\Controllers\CustomerController;

require_once dirname(__DIR__) . '/controllers/CustomerController.php';

$controller = new CustomerController();

add_action('wp_ajax_paygate_get_customers', [$controller, 'getCustomers']);
add_action('wp_ajax_paygate_save_customer', [$controller, 'saveCustomer']);
add_action('wp_ajax_paygate_delete_customer', [$controller, 'deleteCustomer']);
add_action('wp_ajax_paygate_update_customer', [$controller, 'updateCustomer']);
add_action('wp_ajax_paygate_get_countries', [$controller, 'getCountries']);
add_action('wp_ajax_paygate_get_states_by_country', [$controller, 'getStatesByCountry']);
