<?php

class MementoParts{

    public function __construct()
    {
        add_action('woocommerce_before_checkout_form', [$this, 'login_button'], 8);
    }

    function login_button() {
        include_once __DIR__ . '/views/html-login-button.php';
    }
}

new MementoParts();
