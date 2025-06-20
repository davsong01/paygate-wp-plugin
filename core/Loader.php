<?php

namespace PayGate\Core;

defined('ABSPATH') || exit;

class Loader
{
    public function init()
    {
        $this->restrictAccess();
        
        require_once __DIR__ . '/MenuRegistrar.php';
        (new MenuRegistrar())->register();

        require_once plugin_dir_path(__FILE__) . '/../ajax/product-actions.php';
        require_once plugin_dir_path(__FILE__) . '/../ajax/customer-actions.php';

        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);

        // add_action('wp_ajax_paygate_save_customer', [$this, 'saveCustomer']);
        // add_action('wp_ajax_paygate_update_customer', [$this, 'updateCustomer']);
    }



    public function enqueueAssets()
    {
        wp_enqueue_style('paygate-datatables', 'https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css');
        wp_enqueue_style('paygate-admin-style', plugins_url('../assets/css/admin.css', __FILE__));
        wp_enqueue_style('paygate-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');

        wp_enqueue_script('paygate-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', [], null, true);
        wp_enqueue_script('paygate-datatables', 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', ['jquery'], null, true);

        // ✅ Enqueue both JS files safely
        wp_enqueue_script('paygate-admin-products-js', plugins_url('../assets/js/admin-products.js', __FILE__), ['jquery'], null, true);
        wp_enqueue_script('paygate-admin-customers-js', plugins_url('../assets/js/admin-customers.js', __FILE__), ['jquery'], null, true);

        // ✅ Localize PayGateVars for both
        $vars = [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('paygate_nonce'),
        ];

        wp_localize_script('paygate-admin-products-js', 'PayGateVars', $vars);
        wp_localize_script('paygate-admin-customers-js', 'PayGateVars', $vars);
    }

    public function restrictAccess()
    {
        add_action('admin_init', function () {
            if (
                is_admin() &&
                isset($_GET['page']) &&
                strpos($_GET['page'], 'paygate') !== false &&
                !current_user_can('manage_paygate')
            ) {
                wp_die('You do not have sufficient permissions to access this page.');
            }
        });
    }
}
