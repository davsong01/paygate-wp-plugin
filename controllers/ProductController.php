<?php

namespace PayGate\Controllers;

defined('ABSPATH') || exit;

class ProductController
{
    public function getProducts()
    {
        check_ajax_referer('paygate_products', 'nonce');
        global $wpdb;
        $table = $wpdb->prefix . 'paygate_products';
        $products = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC");
        wp_send_json_success($products);
    }

    public function saveProduct()
    {
        check_ajax_referer('paygate_products', 'nonce');
        global $wpdb;
        $table = $wpdb->prefix . 'paygate_products';

        $data = [
            'name'     => sanitize_text_field($_POST['name']),
            'slug'     => sanitize_title($_POST['slug']),
            'price'    => floatval($_POST['price']),
            'in_stock' => intval($_POST['in_stock']),
            'status'   => $_POST['status'] === 'active' ? 'active' : 'inactive'
        ];

        if (!empty($_POST['id'])) {
            $wpdb->update($table, $data, ['id' => intval($_POST['id'])]);
        } else {
            $wpdb->insert($table, $data);
        }

        wp_send_json_success(['message' => 'Saved']);
    }

    public function deleteProduct()
    {
        check_ajax_referer('paygate_products', 'nonce');
        global $wpdb;
        $wpdb->delete($wpdb->prefix . 'paygate_products', ['id' => intval($_POST['id'])]);
        wp_send_json_success(['message' => 'Deleted']);
    }
}
