<?php

defined('ABSPATH') or exit;

class MementoAdminActions
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_menu']);
        add_action('admin_enqueue_scripts', [$this, 'memento_admin_style']);
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

        if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'memento_credentials' ) ) {
            throw new Exception('Invalid CSRF token');
        }

	    update_option('memento_settings_language', sanitize_text_field($_POST['memento_language']));
        update_option('memento_settings_app_key', sanitize_text_field($_POST['memento_client_id']));
        update_option('memento_settings_app_secret', sanitize_text_field($_POST['memento_client_secret']));
        update_option('memento_settings_environment', sanitize_text_field($_POST['memento_environment']));
    }

    public function register_menu()
    {

        add_options_page(
            'Linkly for Woocommerce',
            'Linkly for Woocommerce',
            'manage_options',
            'linkly-for-woocommerce',
            [$this, 'admin_page']
        );
    }

    function memento_admin_style() {
        if (!wp_style_is('memento-admin-style', 'registered')) {
            wp_register_style("memento-admin-style", LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/css/admin-style.css");
        }
	    if (!wp_style_is('memento-style', 'registered')) {
		    wp_register_style("memento-style", LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/css/style.css");
	    }

	    wp_enqueue_style("memento-admin-style");
	    wp_enqueue_style("memento-style");
    }

    public function admin_page(){
        include_once __DIR__ . '/views/html-admin-page.php';
    }
}

new MementoAdminActions();
