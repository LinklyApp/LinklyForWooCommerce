<?php

require 'admin/BillingForWooCommerceAdmin.php';
require 'actions/ActionCallbacks.php';
require 'BillingCustomer.php';
require 'mocks/CustomerMock.php';
require 'helpers/BCustomerToWCCustomerMapper.php';

class Billing
{

    /** @var \Billing singleton instance */
    protected static $instance;

    public function __construct()
    {
        $this->addActions();
//        $this->newCustomer();
    }

    public function newCustomer() {
        $mockedCustomer = CustomerMock::mock();
        $mappedCustomer = BCustomerToWCCustomerMapper::map($mockedCustomer);

        $customer = new BillingCustomer();
        $customer->set_props($mappedCustomer);
        $customer->save();
        $customer->login();
    }

    private function addActions() {
        add_action('woocommerce_customer_object_updated_props', 'updateUserMetaSSO', 10 , 2);
        add_action('admin_menu', 'billingRegisterMenu');
        add_action('woocommerce_before_checkout_form', 'loginButton', 8);
        add_action('wp_enqueue_scripts', 'addStyles');
        add_action('login_enqueue_scripts', 'addStyles');

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


