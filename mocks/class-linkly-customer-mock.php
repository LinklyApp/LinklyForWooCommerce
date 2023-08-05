<?php


class LinklyCustomerMock
{
    public static function mock() {
        return [
            'email' => 'testing' . rand(1,9999) . '@thullner.nl',
            'firstName' => 'Mischa',
            'initials' => 'M.S.',
            'familyNameInfix' => 'van',
            'familyName' => 'Boeren',
            'street' => 'Velperbuitensingel',
            'houseNumber' => '9',
            'houseNumberSuffix' => '55',
            'postcode' => '6881CT',
            'city' => 'Arnhem',
            'country' => 'Netherlands',
            'country_code_short' => 'NL',
            'version' => 3,
        ];
    }
}
