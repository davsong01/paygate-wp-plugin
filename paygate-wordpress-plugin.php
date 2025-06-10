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

// Load activator
require_once plugin_dir_path(__FILE__) . 'includes/class-paygate-activator.php';

// Register activation hook
register_activation_hook(__FILE__, ['Paygate_Activator', 'activate']);
