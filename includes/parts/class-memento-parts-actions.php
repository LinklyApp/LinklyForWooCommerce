<?php

class MementoPartsActions
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_menu_page'));

        add_action('woocommerce_before_checkout_form', [$this, 'buttons'], 8);
        add_action('woocommerce_after_checkout_form', [$this, 'addJavascript'], 8);
        add_action('login_form', [$this, 'buttons']);
        add_action('login_footer', [$this, 'addJavascript']);
        add_action('woocommerce_login_form_end', [$this, 'buttons']);
        add_action('woocommerce_login_form_end', [$this, 'addJavascript']);

    }

    function addJavascript()
    {
        include_once __DIR__ . '/views/buttonToTop.php';
    }

    function buttons()
    {
        include_once __DIR__ . '/views/html-buttons.php';
    }

    function add_menu_page()
    {

    }
}

new MementoPartsActions();
