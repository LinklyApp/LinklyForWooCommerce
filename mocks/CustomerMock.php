<?php


class CustomerMock
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
            'houseNumberSuffix' => '54',
            'postcode' => '6881CT',
            'city' => 'Arnhem',
            'country' => 'Netherlands',
            'version' => 3,
        ];
    }
}
