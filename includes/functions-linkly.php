<?php

use Linkly\OAuth2\Client\Provider\User\LinklyUser;

function createOrUpdateLinklyCustomer(LinklyUser $linklyUser, $userId)
{
    $mappedCustomer = BCustomerToWCCustomerMapper::map($linklyUser);
    $customer = new WC_Customer($userId);

    $customer->set_props($mappedCustomer);
    $customer->save();

    login_linkly_user($customer);
}

function linkLinklyCustomer(LinklyUser $linklyUser, $wpUser)
{
    $mappedCustomer = BCustomerToWCCustomerMapper::map($linklyUser);
    $mappedCustomer['email'] = $wpUser->user_email;
    $customer = new WC_Customer($wpUser->id);
    $customer->set_props($mappedCustomer);
    $customer->save();
}
function login_linkly_user(WC_Customer $customer)
{
    wp_clear_auth_cookie();
    wc_set_customer_auth_cookie($customer->get_id());

    if (is_billing_address_equal_to_shipping_address($customer)) {
        add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );
    }
}

function is_billing_address_equal_to_shipping_address(WC_Customer $customer) : bool
{
    return $customer->get_billing_address_1() === $customer->get_shipping_address_1()
        && $customer->get_billing_first_name() === $customer->get_shipping_first_name()
        && $customer->get_billing_last_name() === $customer->get_shipping_last_name()
        ;
}






