<?php

class Billing
{

    /** @var \Billing singleton instance */
    protected static $instance;

    public function __construct()
    {
        $email = 'testing@thullner.nl';
        $username = 'billing_testing';
        $password = 'haha';

        $id = $this->sync_billing_customer( $email, $username, $password );
    }

    public function sync_billing_customer($user_email, $first_name, $last_name)
    {
        if (empty($user_email) || !is_email($user_email)) {
            throw new \Exception('registration-error-invalid-email');
        }

        if (email_exists($user_email)) {
            throw new \Exception('registration-error-email-exists');
        }

        $username = wc_create_new_customer_username($user_email);

        // Handle password creation.
        $password = wp_generate_password();
        $password_generated = true;

        // Use WP_Error to handle registration errors.
        $errors = new \WP_Error();

        do_action('woocommerce_register_post', $username, $user_email, $errors);

        $errors = apply_filters('woocommerce_registration_errors', $errors, $username, $user_email);

        if ($errors->get_error_code()) {
            throw new \Exception($errors->get_error_code());
        }

        $new_customer_data = apply_filters(
            'woocommerce_new_customer_data',
            array(
                'is_checkout_block_customer_signup' => true,
                'user_login' => $username,
                'user_pass' => $password,
                'user_email' => $user_email,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'role' => 'customer',
            )
        );

        $customer_id = wp_insert_user($new_customer_data);

        if (is_wp_error($customer_id)) {
            throw $this->map_create_account_error($customer_id);
        }

        // Set account flag to remind customer to update generated password.
        update_user_option($customer_id, 'default_password_nag', true, true);

        do_action('woocommerce_created_customer', $customer_id, $new_customer_data, $password_generated);

        return $customer_id;
    }

    private function update_user_meta( $customer ) {
        $updated_props = array();
        $changed_props = $customer->get_changes();

        $meta_key_to_props = array(
            'paying_customer' => 'is_paying_customer',
            'first_name'      => 'first_name',
            'last_name'       => 'last_name',
        );

        foreach ( $meta_key_to_props as $meta_key => $prop ) {
            if ( ! array_key_exists( $prop, $changed_props ) ) {
                continue;
            }

            if ( update_user_meta( $customer->get_id(), $meta_key, $customer->{"get_$prop"}( 'edit' ) ) ) {
                $updated_props[] = $prop;
            }
        }

        $billing_address_props = array(
            'billing_first_name' => 'billing_first_name',
            'billing_last_name'  => 'billing_last_name',
            'billing_company'    => 'billing_company',
            'billing_address_1'  => 'billing_address_1',
            'billing_address_2'  => 'billing_address_2',
            'billing_city'       => 'billing_city',
            'billing_state'      => 'billing_state',
            'billing_postcode'   => 'billing_postcode',
            'billing_country'    => 'billing_country',
            'billing_email'      => 'billing_email',
            'billing_phone'      => 'billing_phone',
        );

        foreach ( $billing_address_props as $meta_key => $prop ) {
            $prop_key = substr( $prop, 8 );

            if ( ! isset( $changed_props['billing'] ) || ! array_key_exists( $prop_key, $changed_props['billing'] ) ) {
                continue;
            }

            if ( update_user_meta( $customer->get_id(), $meta_key, $customer->{"get_$prop"}( 'edit' ) ) ) {
                $updated_props[] = $prop;
            }
        }

        $shipping_address_props = array(
            'shipping_first_name' => 'shipping_first_name',
            'shipping_last_name'  => 'shipping_last_name',
            'shipping_company'    => 'shipping_company',
            'shipping_address_1'  => 'shipping_address_1',
            'shipping_address_2'  => 'shipping_address_2',
            'shipping_city'       => 'shipping_city',
            'shipping_state'      => 'shipping_state',
            'shipping_postcode'   => 'shipping_postcode',
            'shipping_country'    => 'shipping_country',
        );

        foreach ( $shipping_address_props as $meta_key => $prop ) {
            $prop_key = substr( $prop, 9 );

            if ( ! isset( $changed_props['shipping'] ) || ! array_key_exists( $prop_key, $changed_props['shipping'] ) ) {
                continue;
            }

            if ( update_user_meta( $customer->get_id(), $meta_key, $customer->{"get_$prop"}( 'edit' ) ) ) {
                $updated_props[] = $prop;
            }
        }

        do_action( 'woocommerce_customer_object_updated_props', $customer, $updated_props );
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

