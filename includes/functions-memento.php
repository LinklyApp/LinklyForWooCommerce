<?php

function getMementoProvider()
{
    return new \League\OAuth2\Client\Provider\MementoProvider([
        'clientId' => get_option('memento_settings_app_key'), // 'test-wp-plugin'
        'clientSecret' => get_option('memento_settings_app_secret'), // 'secret',
        'redirectUri' => rtrim(get_site_url() . '?memento-callback'),
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






