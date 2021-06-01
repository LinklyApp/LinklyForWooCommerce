<?php

function getMementoProvider()
{
    return new \League\OAuth2\Client\Provider\Memento([
        'clientId' => 'test-wp-plugin',
        'clientSecret' => 'secret',
        'redirectUri' => 'http://billing-wordpress.test?memento-callback',
        'environment' => 'local' // options are "prod", "beta", "local"
    ]);
}

function createOrUpdateMementoCustomer($data, $userId)
{
    $mappedCustomer = BCustomerToWCCustomerMapper::map($data);
    $customer = new MementoCustomer($userId);
    $customer->set_props($mappedCustomer);
    $customer->save();

    login_memento_user($customer->get_id());
}

function login_memento_user($user_id)
{
    wp_clear_auth_cookie();
    wc_set_customer_auth_cookie($user_id);
}






