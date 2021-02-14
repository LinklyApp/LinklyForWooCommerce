<?php

function addStyles() {
    if( ! wp_style_is( 'billing-style', 'registered' ) )
    {
        wp_register_style( "billing-style", BILLING_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/css/style.css" );
    }

    wp_enqueue_style( "billing-style" );
}

add_action('wp_enqueue_scripts', 'addStyles');
add_action('login_enqueue_scripts', 'addStyles');

function updateUserMetaSSO($customer, $updated_props)
{
    $sso_props = ['sso_version' => 'sso_version'];
    $changed_props = $customer->get_changes();

    foreach ($sso_props as $meta_key => $prop) {
        if (!isset($changed_props['sso_version'])) {
            continue;
        }

        if (update_user_meta($customer->get_id(), $meta_key, $customer->{"get_$prop"}('edit'))) {
            $updated_props[] = $prop;
        }
    }
}

add_action('woocommerce_customer_object_updated_props', 'updateUserMetaSSO', 10 , 2);


function newCustomer() {
    $mockedCustomer = CustomerMock::mock();
    $mappedCustomer = BCustomerToWCCustomerMapper::map($mockedCustomer);

    $customer = new BillingCustomer();
    $customer->set_props($mappedCustomer);
    $customer->save();
    wc_set_customer_auth_cookie($customer->get_id());
}

function billing_login_action()
{
    if( isset($_GET['billing_login_action']) ) {
        newCustomer();
    }
}

add_action('init', 'billing_login_action');


