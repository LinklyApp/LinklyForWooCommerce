<?php

class BCustomerToWCCustomerMapper
{
    public static function map($data)
    {
        return [
            'email' => $data['email'],
            'first_name' => $data['firstName'],
            'last_name' => self::createLastName($data['familyNameInfix'], $data['familyName']),
            'billing_address_1' => self::createAddress($data['street'], $data['houseNumber'], $data['houseNumberSuffix']),
            'billing_postcode' => $data['postcode'],
            'billing_country' => $data['country'],
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


