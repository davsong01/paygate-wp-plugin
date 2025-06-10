<?php

namespace Paygate\Includes;

defined('ABSPATH') || exit;

class Loader
{
    public static function init()
    {
        // Admin Hooks
        if (is_admin()) {
            require_once PGWP_PATH . 'includes/Admin/AdminHooks.php';
            \Paygate\Includes\Admin\AdminHooks::init();
        }

        // Frontend Shortcodes
        require_once PGWP_PATH . 'includes/Frontend/Shortcodes.php';
        \Paygate\Includes\Frontend\Shortcodes::init();

        // Common Services
        require_once PGWP_PATH . 'includes/Helpers/functions.php';
    }
}
