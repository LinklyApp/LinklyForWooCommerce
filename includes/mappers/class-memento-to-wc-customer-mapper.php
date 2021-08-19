<?php


use League\OAuth2\Client\Provider\User\MementoUser;

class BCustomerToWCCustomerMapper
{
    public static function map(MementoUser $user)
    {
        return [
            'email' => $user->getEmail(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getFamilyNameWithInfix(),

            'billing_email' => $user->getEmail(),
            'billing_first_name' => $user->getBillingAddress()->getFirstName(),
            'billing_last_name' => $user->getBillingAddress()->getFamilyNameWithInfix(),
            'billing_address_1' => $user->getBillingAddress()->getStreetAddress(),
            'billing_city' => $user->getBillingAddress()->getCity(),
            'billing_postcode' => $user->getBillingAddress()->getPostcode(),
            'billing_country' => $user->getBillingAddress()->getCountry()->getAlpha2(),

            'shipping_email' => $user->getEmail(),
            'shipping_first_name' => $user->getShippingAddress()->getFirstName(),
            'shipping_last_name' => $user->getShippingAddress()->getFamilyNameWithInfix(),
            'shipping_address_1' => $user->getShippingAddress()->getStreetAddress(),
            'shipping_city' => $user->getShippingAddress()->getCity(),
            'shipping_postcode' => $user->getShippingAddress()->getPostcode(),
            'shipping_country' => $user->getShippingAddress()->getCountry()->getAlpha2(),

            'memento_user_guid' => $user->getId(),
            'memento_user_version' => 2,
        ];
    }
}


