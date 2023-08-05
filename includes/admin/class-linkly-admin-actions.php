<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class LinklyAdminActions
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_menu'], 9999);
        add_action('admin_enqueue_scripts', [$this, 'linkly_admin_style']);
        add_action('admin_init', [$this, 'handle_save']);
    }

	public function handle_save()
	{
		if (!isset($_REQUEST['page'])
		    || $_REQUEST['page'] !== 'linkly-for-woocommerce'
		    || empty($_POST)
		) {
			return;
		}

		if (!current_user_can('manage_options')) {
			throw new Exception('User is not an admin');
		}

		if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'linkly_credentials' ) ) {
			$this->handle_save_client_credentials();
		} else if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'linkly_button_style' ) ) {
			$this->handle_save_button_style();
		} else {
			throw new Exception('Invalid CSRF token');
		}
	}

    public function handle_save_client_credentials()
    {
        update_option('linkly_settings_app_key', sanitize_text_field($_POST['linkly_client_id']));
        update_option('linkly_settings_app_secret', sanitize_text_field($_POST['linkly_client_secret']));
    }

	public function handle_save_button_style()
	{
		update_option('linkly_button_style', sanitize_text_field($_POST['linkly_button_style']));
	}

    public function register_menu()
    {
        $parent_slug = 'woocommerce';

        add_submenu_page(
            $parent_slug,
            'Linkly for WooCommerce',
            'Linkly WooCommerce',
            'manage_options',
            'linkly-for-woocommerce',
            [$this, 'admin_page']
        );
    }

    function linkly_admin_style() {
        if (!wp_style_is('linkly-admin-style', 'registered')) {
            wp_register_style("linkly-admin-style", LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/css/admin-style.css");
        }
	    if (!wp_style_is('linkly-style', 'registered')) {
		    wp_register_style("linkly-style", LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/css/style.css");
	    }

	    wp_enqueue_style("linkly-admin-style");
	    wp_enqueue_style("linkly-style");
    }

    public function admin_page(){
        include_once __DIR__ . '/views/html-admin-page.php';
    }
}

new LinklyAdminActions();
