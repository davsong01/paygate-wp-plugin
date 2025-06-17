<?php

namespace PayGate\Core;

defined('ABSPATH') || exit;

class Loader
{
    // public function init()
    // {
    //     // Load menus
    //     require_once __DIR__ . '/MenuRegistrar.php';
    //     require_once __DIR__ . '/../controllers/ProductController.php';
    //     new \PayGate\Controllers\ProductController();
    //     (new MenuRegistrar())->register();
    //     add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    //     require_once plugin_dir_path(__FILE__) . '/../ajax/product-actions.php';
    // }

    public function init()
    {
        require_once __DIR__ . '/MenuRegistrar.php';
        (new MenuRegistrar())->register();

        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);

        add_action('admin_init', function () {
            require_once plugin_dir_path(__FILE__) . '/../ajax/product-actions.php';
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

        wp_localize_script('paygate-admin-js', 'PayGateVars', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('paygate_products')
        ]);
    }
}
