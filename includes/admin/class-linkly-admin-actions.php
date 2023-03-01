<?php

defined('ABSPATH') or exit;

class LinklyAdminActions
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_menu'], 9999);
        add_action('admin_enqueue_scripts', [$this, 'linkly_admin_style']);
        add_action('admin_init', [$this, 'handle_save_client_credentials']);

    }

    public function handle_save_client_credentials()
    {
        if (!isset($_REQUEST['page'])
            || $_REQUEST['page'] !== 'linkly-for-woocommerce'
            || empty($_POST)
        ) {
            return;
        }

        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'linkly_credentials' ) ) {
            throw new Exception('Invalid CSRF token');
        }

	    update_option('linkly_settings_language', sanitize_text_field($_POST['linkly_language']));
        update_option('linkly_settings_app_key', sanitize_text_field($_POST['linkly_client_id']));
        update_option('linkly_settings_app_secret', sanitize_text_field($_POST['linkly_client_secret']));
        update_option('linkly_settings_environment', sanitize_text_field($_POST['linkly_environment']));
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
