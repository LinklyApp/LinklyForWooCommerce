<?php

class MementoPartsActions
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_menu_page'));


        $mementoSsoHelper = LinklyHelpers::instance()->getSsoHelper();

        if (!$mementoSsoHelper->isAuthenticated()) {
            add_action('woocommerce_before_checkout_form', [$this, 'buttons' ], 8);
            add_action('login_form', [$this, 'buttons' ]);
            add_action('woocommerce_login_form_end', [$this, 'buttons' ]);
        } elseif ($mementoSsoHelper->isAuthenticated()) {
            add_action('woocommerce_before_checkout_form', [$this, 'address_button'], 8);
        }
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
