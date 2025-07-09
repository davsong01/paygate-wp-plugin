<?php

namespace PayGate\Core;

defined('ABSPATH') || exit;


class Loader
{
    public function init()
    {
        $this->restrictAccess();
        
        require_once __DIR__ . '/MenuRegistrar.php';
        require_once dirname(__DIR__) . '/controllers/WebhookController.php';
        require_once dirname(__DIR__) . '/controllers/SettingsController.php';

        (new MenuRegistrar())->register();

        require_once plugin_dir_path(__FILE__) . '/../ajax/product-actions.php';
        require_once plugin_dir_path(__FILE__) . '/../ajax/customer-actions.php';
        require_once plugin_dir_path(__FILE__) . '/../ajax/inflow-actions.php';


        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);

        add_action('rest_api_init', function () {
            register_rest_route('paygate/v1', '/webhook', [
                'methods'  => 'POST',
                'callback' => [new \controllers\WebhookController(), 'handle'],
                'permission_callback' => '__return_true'
            ]);
        });

        add_action('admin_post_paygate_save_settings', [new \controllers\SettingsController(), 'save']);
    }

    public function restrictAccess()
    {
        add_action('admin_init', function () {
            if (
                is_admin() &&
                isset($_GET['page']) &&
                strpos($_GET['page'], 'paygate') !== false &&
                !current_user_can('view_paygate')
            ) {
                wp_die('You do not have sufficient permissions to access this page.');
            }
        });
    }

    public function enqueueAssets()
    {
        wp_enqueue_style('paygate-datatables', 'https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css');
        wp_enqueue_style('paygate-admin-style', plugins_url('../assets/css/admin.css', __FILE__));
        wp_enqueue_style('paygate-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');

        wp_enqueue_script('paygate-bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', [], null, true);
        wp_enqueue_script('paygate-datatables', 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', ['jquery'], null, true);


        wp_enqueue_script('paygate-admin-js', plugins_url('../assets/js/admin.js', __FILE__), ['jquery'], null, true);
        wp_enqueue_script('paygate-admin-products-js', plugins_url('../assets/js/admin-products.js', __FILE__), ['jquery'], null, true);
        wp_enqueue_script('paygate-admin-customers-js', plugins_url('../assets/js/admin-customers.js', __FILE__), ['jquery'], null, true);
        wp_enqueue_script('paygate-admin-inflows-js', plugins_url('../assets/js/admin-inflows.js', __FILE__), ['jquery'], null, true);

        // ✅ Localize PayGateVars for both
        $vars = [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('paygate_nonce'),
        ];

        wp_localize_script('paygate-admin-js', 'PayGateVars', $vars);
        wp_localize_script('paygate-admin-products-js', 'PayGateVars', $vars);
        wp_localize_script('paygate-admin-customers-js', 'PayGateVars', $vars);
        wp_localize_script('paygate-admin-inflows-js', 'PayGateVars', $vars);
    }
}
