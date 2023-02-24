<?php

function createOrUpdateLinklyCustomer($data, $userId)
{
    $mappedCustomer = BCustomerToWCCustomerMapper::map($data);
    $customer = new WC_Customer($userId);

    $customer->set_props($mappedCustomer);
    $customer->save();

    login_linkly_user($customer->get_id());
}

function linkLinklyCustomer($data, $wpUser)
{
    $mappedCustomer = BCustomerToWCCustomerMapper::map($data);
    $mappedCustomer['email'] = $wpUser->user_email;
    $customer = new WC_Customer($wpUser->id);
    $customer->set_props($mappedCustomer);
    $customer->save();
}
function login_linkly_user($user_id)
{
    wp_clear_auth_cookie();
    wc_set_customer_auth_cookie($user_id);
}






