<?php

defined('ABSPATH') or exit;

class BillingCustomer extends WC_Customer
{
    public function __construct($data = 0, $is_session = false)
    {
        parent::__construct($data, $is_session);

        $this->data = array_merge($this->data, [
            'thullner_user_version' => null,
            'thullner_user_guid' => null
        ]);
    }

    public function get_sso_version($context = 'view')
    {
        return $this->get_prop('thullner_user_version', $context);
    }

    public function set_sso_version($version)
    {
        $this->set_prop('thullner_user_version', $version);
    }

    public function get_thullner_guid($context = 'view')
    {
        return $this->get_prop('thullner_user_guid', $context);
    }

    public function set_thullner_guid($guid)
    {
        $this->set_prop('thullner_user_version', $guid);
    }
}
