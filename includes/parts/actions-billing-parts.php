<?php

function loginButton() {
    include_once __DIR__ . '/views/html-login-button.php';
}

add_action('woocommerce_before_checkout_form', 'loginButton', 8);

