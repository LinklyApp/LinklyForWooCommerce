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

function newCustomer($data)
{
//    $mockedCustomer = CustomerMock::mock();
    $mappedCustomer = BCustomerToWCCustomerMapper::map($data);

    $customer = new MementoCustomer();
    $customer->set_props($mappedCustomer);
    $customer->save();

    login_memento_user($customer->get_id());
}


function login_memento_user($user_id)
{
    wp_clear_auth_cookie();
    wp_set_current_user ( $user_id );
    wp_set_auth_cookie  ( $user_id );
}






