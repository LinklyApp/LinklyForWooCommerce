<?php
if (!function_exists('get_user_id_for_memento_guid')) {
    function get_user_id_for_memento_guid($guid)
    {
        // TODO Check if non valid guid doesn't return a non null
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

if (!function_exists('dd')) {
    function dd($variable)
    {
        echo "<pre>";
        var_export($variable);
        echo "</pre>";
        die;
    }
}

if (!function_exists('session_start_if_none')) {
    function session_start_if_none(){
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
}

