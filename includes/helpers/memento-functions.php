<?php
if (!function_exists('get_user_id_for_memento_guid')) {
    function get_user_id_for_memento_guid($guid)
    {
        if (!$guid) {
            return null;
        }

        return get_users(array(
            'meta_key' => 'memento_user_guid',
            'meta_value' => $guid,
            'fields' => 'ids'
        ))[0];
    }
}

if (!function_exists('get_payload_from_token')) {
    function get_payload_from_token($token)
    {
        $firstSeparator = strpos($token, '.');
        $secondSeparator = strpos($token, '.', $firstSeparator + 1);
        $payload = substr($token, $firstSeparator + 1, $secondSeparator - $firstSeparator - 1);
        return json_decode(base64_decode($payload));
    }
}

