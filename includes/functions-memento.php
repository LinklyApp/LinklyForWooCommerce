<?php

function createOrUpdateMementoCustomer($data, $userId)
{
    $mappedCustomer = BCustomerToWCCustomerMapper::map($data);
    $customer = new WC_Customer($userId);
    $customer->set_props($mappedCustomer);
    $customer->save();

    login_memento_user($customer->get_id());
}

function login_memento_user($user_id)
{
    wp_clear_auth_cookie();
    wc_set_customer_auth_cookie($user_id);
}






