<?php


function updateUserMetaSSO($customer, $updated_props)
{
    $sso_props = ['sso_version' => 'sso_version'];
    $changed_props = $customer->get_changes();

    foreach ($sso_props as $meta_key => $prop) {
        if (!isset($changed_props['sso_version'])) {
            continue;
        }

        if (update_user_meta($customer->get_id(), $meta_key, $customer->{"get_$prop"}('edit'))) {
            $updated_props[] = $prop;
        }
    }
}

function billingRegisterMenu()
{
    add_options_page(
        'Billing for Woocommerce',
        'Billing for Woocommerce',
        'manage_options',
        'billing-for-woocommerce',
        'billingAdminPage'
    );

    add_action('admin_init', 'billingRegisterSetting');
}

function billingRegisterSetting()
{
    register_setting('billing_options', 'billing_settings_app_key', 1);
    register_setting('billing_options', 'billing_settings_app_secret', 1);
}

/**
 * @param WC_Checkout $checkout
 * @return string
 */
function loginButton(WC_Checkout $checkout)
{
    echo "<div><a class='button button__billing' href=''>Sign in with Billing</a></div>";
}

function addStyles() {
    if( ! wp_style_is( 'billing-style', 'registered' ) )
    {
        wp_register_style( "billing-style", BILLING_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/css/style.css" );
    }

    wp_enqueue_style( "billing-style" );
}
