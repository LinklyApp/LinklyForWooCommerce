<?php

defined('ABSPATH') or exit;

class MementoAdmin
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'register_menu']);
    }

    public function register_menu()
    {
        add_options_page(
            'Memento for Woocommerce',
            'Memento for Woocommerce',
            'manage_options',
            'memento-for-woocommerce',
            [$this, 'admin_page']
        );
    }

    public function admin_page(){
        include_once __DIR__ . '/views/html-admin-page.php';
    }
}

new MementoAdmin();
