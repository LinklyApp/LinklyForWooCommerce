<?php

class LinklyPartsActions
{

    public function __construct()
    {
        add_action('woocommerce_before_checkout_form', [$this, 'buttons'], 8);
        add_action('login_form', [$this, 'buttons']);
        add_action('woocommerce_login_form_end', [$this, 'buttons']);
        add_action('woocommerce_before_edit_account_form', [$this, 'buttons']);
        add_action('woocommerce_before_edit_account_address_form', [$this, 'buttons']);

        add_action('woocommerce_account_dashboard', [$this, 'linkAccountButton']);
        add_action('woocommerce_before_account_orders', [$this, 'linkAccountButton']);

        add_action('woocommerce_after_checkout_form', [$this, 'addJavascript']);
        add_action('login_footer', [$this, 'addJavascript']);
        add_action('woocommerce_login_form_end', [$this, 'addJavascript']);
    }

    function addJavascript()
    {
        include_once __DIR__ . '/views/button-to-top.php';
    }

    function buttons()
    {
        include_once __DIR__ . '/views/html-buttons.php';
    }

    function linkAccountButton()
    {
        include_once __DIR__ . '/views/html-link-account-button.php';
    }
}

new LinklyPartsActions();
