<?php

defined('ABSPATH') or exit;

require 'actions-billing.php';
require 'admin/class-billing-admin.php';
require 'parts/actions-billing-parts.php';
require 'helpers/class-billing-helpers.php';
require 'class-billing-customer.php';
require BILLING_FOR_WOOCOMMERCE_ABS_PATH . '/mocks/class-customer-mock.php';

class Billing
{

    /** @var \Billing singleton instance */
    protected static $instance;

    public function __construct()
    {
    }



    /**
     * Gets the plugin singleton instance.
     *
     * @return \Billing the plugin singleton instance
     * @since 1.10.0
     *
     * @see \facebook_for_woocommerce()
     *
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

}

function billing()
{
    return Billing::instance();
}


