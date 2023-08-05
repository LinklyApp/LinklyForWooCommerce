<?php

use Linkly\OAuth2\Client\Helpers\LinklySsoHelper;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class LinklyAuthActions
{
    /**
     * @var LinklySsoHelper
     */
    private LinklySsoHelper $ssoHelper;

    public function __construct(LinklySsoHelper $ssoHelper)
    {
        $this->ssoHelper = $ssoHelper;

        add_action('init', [$this, 'linkly_login_action']);
        add_action('init', [$this, 'linkly_link_account_action']);
        add_action('init', [$this, 'linkly_login_callback']);
        add_action('init', [$this, 'linkly_request_token_action']);
        add_action('init', [$this, 'linkly_request_token_callback']);

        add_action('wp_logout', [$this, 'linkly_logout']);
    }

	/**
	 * The action to redirect to the Linkly SSO server to link the webshop to Linkly
	 *
	 * @return void
	 */
    function linkly_request_token_action()
    {
        if (!isset($_GET['linkly_request_token'])) {
            return;
        }
        $_SESSION['url_to_return_to'] = get_site_url() . urldecode($_GET['linkly_request_token']);
        // $corsUrl is pure the domain name without the path if there is a port number it is included

        $corsUrl = parse_url(get_site_url(), PHP_URL_SCHEME) . '://' . parse_url(get_site_url(), PHP_URL_HOST);
        $port = parse_url(get_site_url(), PHP_URL_PORT);

        if ($port) {
            $corsUrl .= ':' . $port;
        }

        $params = [
            'return_url' => get_site_url() . '?linkly_request_token_callback',
            'clientName' => get_bloginfo('name'),
            'allowed_cors_origin' => $corsUrl,
            'post_logout_redirect_uri' => get_site_url(),
            'redirect_uri' => get_site_url() . '?linkly-callback',
        ];

	    $this->ssoHelper->linkClient($params);
        exit;
    }

	/**
	 * The callback action after the webshop has been linked to Linkly
	 *
	 * @return void
	 */
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

	/**
	 * The action to redirect to the Linkly SSO server to link the WordPress user to Linkly
	 *
	 * @return void
	 * @throws Exception
	 */
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

	/**
	 * The action to redirect to the Linkly SSO server to log in
	 *
	 * @return void
	 * @throws Exception
	 */
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

	/**
	 * The callback action after the user has logged in on the Linkly SSO server
	 *
	 * @return void
	 */
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
                linkly_attachWCCustomer($linklyUser, wp_get_current_user());
            } else {
                $user = get_user_by('email', $this->ssoHelper->getEmail());
                linkly_createOrUpdateCustomer($linklyUser, $user ?: null);
            }

			if ( str_contains( $_SESSION['url_to_return_to'], '/wp-login.php' ) ) {
				$_SESSION['url_to_return_to'] = get_site_url();
			}

            wp_redirect($_SESSION['url_to_return_to']);
            unset($_SESSION['url_to_return_to']);
            exit;
        } catch (Exception $e) {
            wp_clear_auth_cookie();
            linkly_dd($e);
        }
    }

	/**
	 * The action for the user to log out
	 *
	 * @return void
	 */
    function linkly_logout()
    {
        $this->ssoHelper->logout();
    }

}

new LinklyAuthActions(LinklyHelpers::instance()->getSsoHelper());
