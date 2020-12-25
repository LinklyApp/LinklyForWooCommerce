<?php

require 'BillingCustomer.php';

class Billing
{

    /** @var \Billing singleton instance */
    protected static $instance;

    public function __construct()
    {
        add_action('woocommerce_customer_object_updated_props', 'updateUserMetaSSO', 10 , 2);

        $email = rand(0,9999) . 'testing@thullner.nl';
        $first_name = 'Mischa';
        $last_name = 'Thullner';
        $password = wp_generate_password();
        $customer = new BillingCustomer();

        $customer->set_first_name($first_name);
        $customer->set_last_name($last_name);
        $customer->set_email($email);
        $customer->set_password($password);
        $customer->set_billing_location('Netherlands', $state = '', $postcode = '1234AB', $city = 'Rotterdam' );
        $customer->set_billing_address_1("Kalverstraat 22" );
        $customer->set_sso_version(2);
        $customer->save();

//        $customer_data_store = new WC_Customer_Data_Store();
//        $customer_data_store->create($customer);
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

function updateUserMetaSSO($customer, $updated_props)
{
    $sso_props = ['sso_version' => 'sso_version'];
    $changed_props = $customer->get_changes();

    foreach ( $sso_props as $meta_key => $prop ) {
        if ( ! isset( $changed_props['sso_version'] )) {
            continue;
        }

        if ( update_user_meta( $customer->get_id(), $meta_key, $customer->{"get_$prop"}( 'edit' ) ) ) {
            $updated_props[] = $prop;
        }
    }

    var_dump($changed_props);
    var_dump($updated_props);

    return $updated_props;
}

