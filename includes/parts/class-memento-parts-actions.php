<?php

class MementoPartsActions
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_menu_page'));


        $mementoSsoHelper = MementoHelpers::instance()->getSsoHelper();

        if (!$mementoSsoHelper->isAuthenticated()) {
            add_action('woocommerce_before_checkout_form', [$this, 'login_button'], 8);
            add_action('login_form', [$this, 'login_button']);
            add_action('woocommerce_login_form_end', [$this, 'login_button']);
        } elseif ($mementoSsoHelper->isAuthenticated()) {
            add_action('woocommerce_before_checkout_form', [$this, 'address_button'], 8);
        }
    }

    function login_button()
    {
        include_once __DIR__ . '/views/html-login-button.php';
    }

    function address_button()
    {
        include_once __DIR__ . '/views/html-address-button.php';
    }
}

new MementoPartsActions();
