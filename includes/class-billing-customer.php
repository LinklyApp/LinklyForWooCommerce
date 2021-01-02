<?php

defined('ABSPATH') or exit;

class BillingCustomer extends WC_Customer
{
    public function __construct($data = 0, $is_session = false)
    {
        parent::__construct($data, $is_session);

        $this->data = array_merge($this->data, [
            'sso_version' => null
        ]);
    }

    public function set_sso_version( $version ) {
        $this->set_prop( 'sso_version', $version );
    }

    public function get_sso_version( $context = 'view' ) {
        return $this->get_prop( 'sso_version', $context );
    }
}
