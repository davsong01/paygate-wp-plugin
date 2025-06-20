<?php

namespace Controllers;

defined('ABSPATH') || exit;

class ProductController
{
    public function getProducts()
    {
        check_ajax_referer('paygate_nonce', 'nonce');

        global $wpdb;
        $table = $wpdb->prefix . 'paygate_products';
        $products = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC");

        wp_send_json_success($products);
    }

    public function saveProduct()
    {
        check_ajax_referer('paygate_nonce', 'nonce');

        global $wpdb;
        $table = $wpdb->prefix . 'paygate_products';

        $id      = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $name    = sanitize_text_field($_POST['name']);
        $sku     = sanitize_text_field($_POST['sku']);
        $price   = floatval($_POST['price']);
        $stock   = intval($_POST['in_stock']);
        $status  = ($_POST['status'] === 'active') ? 'active' : 'inactive';

        $baseSlug = sanitize_title($name);
        $slug = $baseSlug;
        $slugModified = false;

        if (!$id) {
            // Check for conflict and generate a unique slug
            $i = 1;
            while ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE slug = %s", $slug))) {
                $slug = $baseSlug . '-' . $i;
                $i++;
                $slugModified = true;
            }
        } else {
            // Editing: only change if conflict with another record
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table WHERE slug = %s AND id != %d LIMIT 1",
                $slug,
                $id
            ));

            if ($existing) {
                $i = 1;
                $newSlug = $baseSlug;
                while ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE slug = %s AND id != %d", $newSlug, $id))) {
                    $newSlug = $baseSlug . '-' . $i;
                    $i++;
                    $slugModified = true;
                }
                $slug = $newSlug;
            }
        }

        $data = [
            'name'       => $name,
            'slug'       => $slug,
            'sku'        => $sku,
            'price'      => $price,
            'in_stock'   => $stock,
            'status'     => $status,
            'updated_at' => current_time('mysql')
        ];

        if ($id) {
            $wpdb->update($table, $data, ['id' => $id]);
            wp_send_json_success([
                'message'        => 'Product updated successfully.',
                'slug_modified'  => $slugModified,
                'final_slug'     => $slug
            ]);
        } else {
            $data['created_at'] = current_time('mysql');
            $wpdb->insert($table, $data);
            wp_send_json_success([
                'message'        => 'Product created successfully.',
                'slug_modified'  => $slugModified,
                'final_slug'     => $slug
            ]);
        }
    }



    public function deleteProduct()
    {
        check_ajax_referer('paygate_nonce', 'nonce');

        global $wpdb;
        $table = $wpdb->prefix . 'paygate_products';

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;

        if ($id) {
            $wpdb->delete($table, ['id' => $id]);
            wp_send_json_success(['message' => 'Product deleted']);
        }

        wp_send_json_error(['message' => 'Invalid product ID']);
    }
}
