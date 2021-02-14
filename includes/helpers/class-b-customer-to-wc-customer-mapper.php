<?php

class BCustomerToWCCustomerMapper
{
    public static function map($data)
    {
        return [
            'email' => $data['email'],
            'first_name' => $data['firstName'],
            'last_name' => self::createLastName($data['familyNameInfix'], $data['familyName']),

            'billing_email' => $data['email'],
            'billing_first_name' => $data['firstName'],
            'billing_last_name' => self::createLastName($data['familyNameInfix'], $data['familyName']),
            'billing_address_1' => self::createAddress($data['billing']['street'], $data['billing']['houseNumber'],
                $data['billing']['houseNumberSuffix']),
            'billing_city' => $data['billing']['city'],
            'billing_postcode' => $data['billing']['postcode'],
            'billing_country' => $data['billing']['country']['alpha2'],

            'shipping_first_name' => $data['firstName'],
            'shipping_last_name' => self::createLastName($data['familyNameInfix'], $data['familyName']),
            'shipping_address_1' => self::createAddress($data['shipping']['street'], $data['shipping']['houseNumber'],
                $data['shipping']['houseNumberSuffix']),
            'shipping_city' => $data['shipping']['city'],
            'shipping_postcode' => $data['shipping']['postcode'],
            'shipping_country' => $data['shipping']['country']['alpha2'],

            'sso_version' => $data['version'],
        ];
    }

    private static function createLastName($familyNameInfix, $familyName)
    {
        $lastName = '';
        if ($familyNameInfix) {
            $lastName .= $familyNameInfix . ' ';
        }
        $lastName .= $familyName;
        return $lastName;
    }

    private static function createAddress($street, $houseNumber, $houseNumberSuffix)
    {
        $address = $street . ' ' . $houseNumber;

        if ($houseNumberSuffix) {
            $houseNumberContainsNumbers = !!preg_match('~[0-9]~', $houseNumberSuffix);
            if ($houseNumberContainsNumbers) {
                $address .= '-';
            }
            $address .= $houseNumberSuffix;
        }

        return $address;
    }
}


