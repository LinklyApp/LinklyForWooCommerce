<?php

defined('ABSPATH') or exit;

class MementoCustomer extends WC_Customer
{
    public function __construct($data = 0, $is_session = false)
    {
        parent::__construct($data, $is_session);

        $this->data = array_merge($this->data, [
            'memento' => [
                'user_guid' => null,
                'user_version' => null
            ]
        ]);
    }


    public function get_memento_user_guid($context = 'view')
    {
        return $this->get_address_prop('user_guid', 'memento', $context);
    }

    public function set_memento_user_guid($guid)
    {
        $this->set_address_prop('user_guid', 'memento', $guid);
    }

    public function get_memento_user_version($context = 'view')
    {
        return $this->get_address_prop('user_version', 'memento', $context);
    }

    public function set_memento_user_version($version)
    {
        $this->set_address_prop('user_version', 'memento', $version);
    }


}
