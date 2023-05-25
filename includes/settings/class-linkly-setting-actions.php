<?php

defined('ABSPATH') or exit;

class LinklySettingActions
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'linkly_register_settings']);
        add_action('admin_init', [$this, 'linkly_button_settings']);
    }

    function linkly_register_settings()
    {
        add_option( 'linkly_settings_app_key');
        add_option( 'linkly_settings_app_secret');
        add_option( 'linkly_settings_environment', 'prod');
    }

	function linkly_button_settings()
	{
		add_option('linkly_button_style', 'primary');
	}
}

new LinklySettingActions();





