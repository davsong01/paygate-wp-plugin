<?php

namespace PayGate\Core;

defined('ABSPATH') || exit;

class Loader
{
    public function init()
    {
        // Load menus
        require_once __DIR__ . '/MenuRegistrar.php';
        (new MenuRegistrar())->register();
    }
}
