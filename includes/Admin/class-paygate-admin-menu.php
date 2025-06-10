<?php

if (!defined('ABSPATH')) exit;

class Paygate_Admin_Menu
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_menu']);
    }

    public function register_menu()
    {
        add_menu_page(
            'Paygate Plugin',
            'Paygate',
            'manage_options',
            'paygate_dashboard',
            [$this, 'render_settings_page'],
            'dashicons-admin-generic',
            60
        );

        add_submenu_page(
            'paygate_dashboard',
            'Paygate Settings',
            'Settings',
            'manage_options',
            'paygate_settings',
            [$this, 'render_settings_page']
        );

        add_submenu_page(
            'paygate_dashboard',
            'Paygate Shortcodes',
            'Shortcodes',
            'manage_options',
            'paygate_shortcodes',
            [$this, 'render_shortcode_page']
        );
    }

    public function render_settings_page()
    {
        require_once plugin_dir_path(__FILE__) . 'views/settings-page.php';
    }

    public function render_shortcode_page()
    {
        include_once plugin_dir_path(__FILE__) . 'views/shortcodes-page.php';
    }
}
