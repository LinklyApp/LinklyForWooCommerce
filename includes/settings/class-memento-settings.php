<?php

defined('ABSPATH') or exit;

class MementoSettings
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'memento_register_settings']);
    }

    function memento_register_settings()
    {
        add_option( 'memento_settings_app_key');
        add_option( 'memento_settings_app_secret');
        add_option( 'memento_settings_environment', 'beta');
    }
}

new MementoSettings();





