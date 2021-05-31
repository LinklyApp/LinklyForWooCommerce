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
        register_setting('memento_options', 'memento_settings_app_key', 1);
        register_setting('memento_options', 'memento_settings_app_secret', 1);
    }
}

new MementoSettings();





