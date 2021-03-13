<?php

function addStyles()
{
    if (!wp_style_is('billing-style', 'registered')) {
        wp_register_style("billing-style", BILLING_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/css/style.css");
    }

    wp_enqueue_style("billing-style");
}

add_action('wp_enqueue_scripts', 'addStyles');
add_action('login_enqueue_scripts', 'addStyles');

function updateUserMetaSSO($customer, $updated_props)
{
    $sso_props = [
        'thullner_user_version' => 'thullner_user_version',
        'thullner_user_guid' => 'thullner_user_guid',
    ];
    $changed_props = $customer->get_changes();

    foreach ($sso_props as $meta_key => $prop) {
        // TODO Check if this registers
        if (!isset($changed_props['thullner'])) {
            continue;
        }

        if (update_user_meta($customer->get_id(), $meta_key, $customer->{"get_$prop"}('edit'))) {
            $updated_props[] = $prop;
        }
    }
}

add_action('woocommerce_customer_object_updated_props', 'updateUserMetaSSO', 10, 2);


function newCustomer($data)
{
//    $mockedCustomer = CustomerMock::mock();
    $mappedCustomer = BCustomerToWCCustomerMapper::map($data);

    $customer = new BillingCustomer();
    $customer->set_props($mappedCustomer);
    $customer->save();
    wc_set_customer_auth_cookie($customer->get_id());
}

function billing_login_action()
{
    if (isset($_GET['billing_login_action'])) {
        require BILLING_FOR_WOOCOMMERCE_ABS_PATH . '/vendor/autoload.php';

        $provider = new \League\OAuth2\Client\Provider\Billing([
            'clientId' => 'plugin',
            'clientSecret' => 'secret',
            'redirectUri' => 'https://billing-wordpress.test?callback-billing',
        ]);

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_GET['code'])) {

            // If we don't have an authorization code then get one
            $authUrl = $provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $provider->getState();
            $prev_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            $_SESSION['url_to_return_to'] = $prev_url;
            header('Location: ' . $authUrl);
            exit;
// Check given state against previously stored one to mitigate CSRF attack
        }
    }
}

add_action('init', 'billing_login_action');


function billing_login_callback()
{
    if (isset($_GET['callback-billing'])) {
        require BILLING_FOR_WOOCOMMERCE_ABS_PATH . '/vendor/autoload.php';

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $provider = new \League\OAuth2\Client\Provider\Billing([
            'clientId' => 'plugin',
            'clientSecret' => 'secret',
            'redirectUri' => 'https://billing-wordpress.test?callback-billing',
        ]);

        if (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {

            var_dump($_SESSION);
            echo 'Session state: ' . $_SESSION['oauth2state'];
            echo 'Redirected state: ' . $_GET['state'];

            echo 'test';

//            unset($_SESSION['oauth2state']);
            exit('Invalid state');
        }

        // Try to get an access token (using the authorization code grant)

        $token = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code'],
        ]);

        // Optional: Now you have a token you can look up a users profile data
        try {

            // We got an access token, let's now get the user's details
            $user = $provider->getResourceOwner($token);

            newCustomer($user);

            wp_redirect( $_SESSION['url_to_return_to']);
            exit;

        } catch (Exception $e) {

            var_dump($e);
            // Failed to get user details
            exit('Oh dear...');
        }
    }
}

add_action('init', 'billing_login_callback');


function billing_get_order($order_id)
{
    $order = wc_get_order( $order_id );

    $bInvoice = WCOrderToBInvoiceMapper::map($order);
}


add_action( 'woocommerce_order_status_processing', 'billing_get_order');



