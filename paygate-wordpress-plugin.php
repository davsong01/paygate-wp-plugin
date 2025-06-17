<?php

/**
 * Plugin Name: Paygate WordPress Plugin
 * Plugin URI: https://example.com/paygate-plugin
 * Description: A Paygate payment integration plugin for WordPress.
 * Version: 1.0.0
 * Author: David Oghi
 * Text Domain: paygate-wordpress-plugin
 * Domain Path: /languages
 */


defined('ABSPATH') || exit;

if (! defined('PAYGATE_PATH')) {
    define('PAYGATE_PATH', plugin_dir_path(__FILE__));
}

require_once __DIR__ . '/core/Loader.php';
(new \PayGate\Core\Loader())->init();

register_activation_hook(__FILE__, 'paygate_plugin_activate');

function paygate_plugin_activate()
{
    require_once __DIR__ . '/database/install.php';
    \PayGate\Database\Installer::install();
}
