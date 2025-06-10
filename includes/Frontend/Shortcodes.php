<?php

namespace Paygate\Includes\Frontend;

class Shortcodes
{
    public static function init()
    {
        add_shortcode('paygate_form', [self::class, 'render_form']);
    }

    public static function render_form()
    {
        ob_start();
        include PGWP_PATH . 'templates/frontend/payment-form.php';
        return ob_get_clean();
    }
}
