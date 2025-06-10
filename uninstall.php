<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Optional: Clean up specific WordPress options, if used.
// delete_option('paygate_example_option');
// delete_option('paygate_version');

// Tables will be preserved to retain transaction history and admin configuration.
