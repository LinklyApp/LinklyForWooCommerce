<?php

defined('ABSPATH') or exit;

require 'actions-billing-admin.php';

class BillingAdmin
{
    public function __construct()
    {
        $this->registerMenu();
    }

    private function registerMenu()
    {
//        add_options_page(
//            'Billing for Woocommerce',
//            'Billing for Woocommerce',
//            'manage_options',
//            'billing-for-woocommerce',
//            [$this, 'adminPage']
//        );

        add_action('admin_init', 'billingRegisterSetting');
    }

    private function adminPage(){
        include_once __DIR__ . '/views/html-admin-page.php';
    }
}

new BillingAdmin();
