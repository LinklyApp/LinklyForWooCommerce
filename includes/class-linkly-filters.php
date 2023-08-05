<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class LinklyFilters
{
    public function __construct()
    {
        add_action('woocommerce_checkout_before_customer_details', [$this, 'get_filter_for_billing_address_equal_to_shipping_address'], 1);
    }

    public function get_filter_for_billing_address_equal_to_shipping_address()
    {
        $current_user_id = get_current_user_id();
        if (!$current_user_id) {
            return;
        }

        $customer = new WC_Customer(get_current_user_id());

        if ($this->is_billing_address_equal_to_shipping_address($customer)) {
            add_filter('woocommerce_ship_to_different_address_checked', '__return_false');
        } else {
            add_filter('woocommerce_ship_to_different_address_checked', '__return_true');
        }
    }

    public function is_billing_address_equal_to_shipping_address(WC_Customer $customer): bool
    {
        return $customer->get_billing_address_1() === $customer->get_shipping_address_1()
            && $customer->get_billing_first_name() === $customer->get_shipping_first_name()
            && $customer->get_billing_last_name() === $customer->get_shipping_last_name();
    }
}

new LinklyFilters();
