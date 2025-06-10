<?php

namespace Paygate\Includes\Admin;

class AdminHooks
{
    public static function init()
    {
        add_action('admin_menu', [self::class, 'add_menu']);
    }

    public static function add_menu()
    {
        add_menu_page(
            __('Paygate Settings', 'paygate-wordpress-plugin'),
            'Paygate',
            'manage_options',
            'paygate-settings',
            [self::class, 'render_settings'],
            'dashicons-admin-generic',
            60
        );
    }

    public static function render_settings()
    {
        include PGWP_PATH . 'templates/admin/settings-page.php';
    }
}
