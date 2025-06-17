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
            'manage_options',
            'paygate_dashboard',
            [$this, 'renderDashboard'],
            'dashicons-cart',
            56
        );

        add_submenu_page(
            'paygate_dashboard',
            'Products',
            'Products',
            'manage_options',
            'paygate_products',
            [$this, 'renderProducts']
        );

        add_submenu_page(
            'paygate_dashboard',
            'Customers',
            'Customers',
            'manage_options',
            'paygate_customers',
            [$this, 'renderCustomers']
        );

        add_submenu_page(
            'paygate_dashboard',
            'Transactions',
            'Transactions',
            'manage_options',
            'paygate_transactions',
            [$this, 'renderTransactions']
        );

        add_submenu_page(
            'paygate_dashboard',
            'Settings',
            'Settings',
            'manage_options',
            'paygate_settings',
            [$this, 'renderSettings']
        );
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

    public function renderTransactions()
    {
        include_once plugin_dir_path(__FILE__) . '/../templates/transactions/list.php';
    }

    public function renderSettings()
    {
        include_once plugin_dir_path(__FILE__) . '/../templates/settings/index.php';
    }
}
