<?php

use Linkly\OAuth2\Client\Helpers\LinklySsoHelper;

class LinklyAuthActions
{
    /**
     * @var LinklySsoHelper
     */
    private $ssoHelper;

    public function __construct(LinklySsoHelper $ssoHelper)
    {
        $this->ssoHelper = $ssoHelper;

        add_action('init', [$this, 'linkly_login_action']);
        add_action('init', [$this, 'linkly_link_account_action']);
        add_action('init', [$this, 'linkly_login_callback']);
        add_action('init', [$this, 'linkly_request_token_action']);
        add_action('init', [$this, 'linkly_request_token_callback']);

        add_action('woocommerce_before_checkout_form', [$this, 'linkly_check_and_update_addresses_if_changed']);
        add_action('woocommerce_before_edit_account_address_form', [$this, 'linkly_check_and_update_addresses_if_changed']);

        add_action('wp_logout', [$this, 'linkly_logout']);
    }

    function linkly_request_token_action()
    {
        if (!isset($_GET['linkly_request_token'])) {
            return;
        }
        $_SESSION['url_to_return_to'] = get_site_url() . urldecode($_GET['linkly_request_token']);
        // $corsUrl is pure the domain name without the path if there is a port number it is included

        $url = $this->getBaseUrl();
        $corsUrl = parse_url(get_site_url(), PHP_URL_SCHEME) . '://' . parse_url(get_site_url(), PHP_URL_HOST);
        $port = parse_url(get_site_url(), PHP_URL_PORT);

        if ($port) {
            $corsUrl .= ':' . $port;
        }

        $params = [
            'redirect_uri' => get_site_url() . '?linkly_request_token_callback',
            'clientName' => get_bloginfo('name'),
            'oauth_cors_uri' => $corsUrl,
            'oauth_post_logout_redirect_uri' => get_site_url(),
            'oauth_redirect_uri' => get_site_url() . '?linkly-callback',
        ];
        $url .= '/external-api/clients?' . http_build_query($params);
        wp_redirect($url);
        exit;
    }

    function linkly_request_token_callback()
    {
        if (!isset($_GET["linkly_request_token_callback"])) {
            return;
        }

        update_option('linkly_settings_app_key', sanitize_text_field($_GET["client_id"]));
        update_option('linkly_settings_app_secret', sanitize_text_field($_GET["client_secret"]));

        wp_redirect(admin_url('admin.php?page=linkly-for-woocommerce'));
        exit;

    }

    function linkly_link_account_action()
    {
        if (!isset($_GET['linkly_link_account_action'])) {
            return;
        }
        $_SESSION['url_to_return_to'] = get_site_url() . urldecode($_GET['linkly_login_action']);
        $_SESSION['linkly_link_account'] = true;
        $this->ssoHelper->authorize();
        exit;
    }

    function linkly_login_action()
    {
        if (!isset($_GET['linkly_login_action'])) {
            return;
        }

        $_SESSION['url_to_return_to'] = get_site_url() . urldecode($_GET['linkly_login_action']);
        unset($_SESSION['linkly_link_account']);

        $this->ssoHelper->authorize();
        exit;
    }

    function linkly_login_callback()
    {
        if (!isset($_GET['linkly-callback'])) {
            return;
        }

        try {
            $this->ssoHelper->callback();
            $linklyUser = $this->ssoHelper->getUser();
            if (isset($_SESSION['linkly_link_account'])) {
                unset($_SESSION['linkly_link_account']);
                attachWCCustomerToLinkly($linklyUser, wp_get_current_user());
            } else {
                $user = get_user_by('email', $this->ssoHelper->getEmail());
                createOrUpdateLinklyCustomer($linklyUser, $user ?: null);
            }

			if ( str_contains( $_SESSION['url_to_return_to'], '/wp-login.php' ) ) {
				$_SESSION['url_to_return_to'] = get_site_url();
			}

            wp_redirect($_SESSION['url_to_return_to']);
            unset($_SESSION['url_to_return_to']);
            exit;
        } catch (Exception $e) {
            wp_clear_auth_cookie();
            dd($e);
        }
    }

    public function linkly_check_and_update_addresses_if_changed()
    {
        if (!is_user_logged_in()) {
            return;
        }

        $customer = new WC_Customer(get_current_user_id());
        if (!$customer->get_meta('linkly_user')) {
            return;
        }

        $addressData = [
            'billingAddressId' => $customer->get_meta('linkly_billing_id'),
            'billingAddressVersion' => $customer->get_meta('linkly_billing_version'),
            'shippingAddressId' => $customer->get_meta('linkly_shipping_id'),
            'shippingAddressVersion' => $customer->get_meta('linkly_shipping_version'),
        ];

        try {
            if(!$this->ssoHelper->hasAddressBeenChanged($addressData))
            {
                return;
            }

            $linklyUser = $this->ssoHelper->getUser();
            $mappedCustomer = BCustomerToWCCustomerMapper::map($linklyUser);
            $customer->set_props($mappedCustomer);

            $customer->add_meta_data('linkly_user', true, true);
            $customer->update_meta_data('linkly_billing_id', $linklyUser->getBillingAddress()->getId());
            $customer->update_meta_data('linkly_billing_version', $linklyUser->getBillingAddress()->getVersion());
            $customer->update_meta_data('linkly_shipping_id', $linklyUser->getShippingAddress()->getId());
            $customer->update_meta_data('linkly_shipping_version', $linklyUser->getShippingAddress()->getVersion());

            $customer->save();
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    function linkly_logout()
    {
        $this->ssoHelper->logout();
    }

    private function getBaseUrl()
    {
        $env = get_option('linkly_settings_environment');
        if ($env === 'local') {
            return LinklyHelpers::instance()->getLinklyProvider()->localDomain;
        }
        if ($env === 'beta') {
            return LinklyHelpers::instance()->getLinklyProvider()->betaDomain;
        }

        return LinklyHelpers::instance()->getLinklyProvider()->domain;

    }
}

new LinklyAuthActions(LinklyHelpers::instance()->getSsoHelper());
