<?php

class MementoCustomerDataStore
{
    public function __construct()
    {
        add_action('woocommerce_customer_object_updated_props', [$this, 'update_user_meta_sso'], 10, 2);
    }


    function update_user_meta_sso($customer, $updated_props)
    {
        $sso_props = [
            'memento_user_guid' => 'memento_user_guid',
            'memento_user_version' => 'memento_user_version'
        ];
        $changed_props = $customer->get_changes();

        foreach ($sso_props as $meta_key => $prop) {
            // TODO Check if this registers
            $prop_key = substr($prop, 8);

            if (!isset($changed_props['memento']) || !array_key_exists($prop_key, $changed_props['memento'])) {
                continue;
            }

            if (update_user_meta($customer->get_id(), $meta_key, $customer->{"get_$prop"}('edit'))) {
                $updated_props[] = $prop;
            }
        }
    }
}

new MementoCustomerDataStore();
