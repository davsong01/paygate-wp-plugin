<?php

namespace PayGate\Core;

defined('ABSPATH') || exit;

class MenuRegistrar
{
    public function register()
    {
        add_action('admin_menu', [$this, 'addMenus']);
    }

    public function addMenus()
    {
        add_menu_page(
            'PayGate Dashboard',
            'PayGate',
            'view_paygate',
            'paygate_dashboard',
            [$this, 'renderDashboard'],
            'dashicons-cart',
            56
        );

        add_submenu_page(
            'paygate_dashboard',
            'Customers',
            'Customers',
            'view_paygate',
            'paygate_customers',
            [$this, 'renderCustomers']
        );

        add_submenu_page(
            'paygate_dashboard',
            'Inflows',
            'Inflows',
            'view_paygate',
            'paygate_inflows',
            [$this, 'renderInflows']
        );


        if (current_user_can('administrator')) {
            add_submenu_page(
                'paygate_dashboard',
                'Settings',
                'Settings',
                'view_paygate',
                'paygate_settings',
                function () {
                    (new \controllers\SettingsController())->render();
                }
            );
        }
    }

    public function renderDashboard()
    {
        echo '<h2>PayGate Accountant Dashboard</h2>';
        echo '<p>Welcome. Use the sidebar to manage Products, Customers, Transactions, and Settings and more</p>';
    }

    public function renderProducts()
    {
        include_once plugin_dir_path(__FILE__) . '/../templates/products/list.php';
    }

    public function renderCustomers()
    {
        include_once plugin_dir_path(__FILE__) . '/../templates/customers/list.php';
    }

    public function renderInflows()
    {
        include_once plugin_dir_path(__FILE__) . '/../templates/inflows/index.php';
    }

    public function renderSettings()
    {
        include_once plugin_dir_path(__FILE__) . '/../templates/settings/index.php';
    }
}
