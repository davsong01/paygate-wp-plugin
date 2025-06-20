<?php

use Controllers\ProductController;

require_once dirname(__DIR__) . '/controllers/ProductController.php';

$controller = new ProductController();

add_action('wp_ajax_paygate_get_products', [$controller, 'getProducts']);
add_action('wp_ajax_paygate_save_product', [$controller, 'saveProduct']);
add_action('wp_ajax_paygate_delete_product', [$controller, 'deleteProduct']);
