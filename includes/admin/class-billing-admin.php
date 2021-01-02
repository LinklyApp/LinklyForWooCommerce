<?php

defined('ABSPATH') or exit;

require 'actions-billing-admin.php';

class BillingAdmin
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'registerMenu']);
    }

    public function registerMenu()
    {
        add_options_page(
            'Billing for Woocommerce',
            'Billing for Woocommerce',
            'manage_options',
            'billing-for-woocommerce',
            [$this, 'adminPage']
        );
    }

    public function adminPage(){
        include_once __DIR__ . '/views/html-admin-page.php';
    }
}

new BillingAdmin();
