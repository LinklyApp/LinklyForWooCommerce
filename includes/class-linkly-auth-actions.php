<?php

use Linkly\OAuth2\Client\Helpers\LinklySsoHelper;
use Linkly\OAuth2\Client\Provider\User\LinklyUser;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class LinklyAuthActions {
	/**
	 * @var LinklySsoHelper
	 */
	private LinklySsoHelper $ssoHelper;

	public function __construct( LinklySsoHelper $ssoHelper ) {
		$this->ssoHelper = $ssoHelper;

		add_action( 'init', [ $this, 'linkly_login_action' ] );
		add_action( 'init', [ $this, 'linkly_link_account_action' ] );
		add_action( 'init', [ $this, 'linkly_login_callback' ] );

		add_action( 'wp_logout', [ $this, 'linkly_logout' ] );
	}


	/**
	 * The action to redirect to the Linkly SSO server to link the WordPress user to Linkly
	 *
	 * @return void
	 * @throws Exception
	 */
	public function linkly_link_account_action() {
		if ( ! isset( $_GET['linkly_link_account_action'] ) ) {
			return;
		}

		$decodedAccountActionUrl      = urldecode( $_GET['linkly_link_account_action'] );
		$sanitizedAccountActionUrl    = filter_var( $decodedAccountActionUrl, FILTER_SANITIZE_URL );
		$_SESSION['url_to_return_to'] = esc_url( get_site_url() . $sanitizedAccountActionUrl );

		$_SESSION['linkly_link_account'] = true;
		$this->ssoHelper->authorizeRedirect();
		exit;
	}

	/**
	 * The action to redirect to the Linkly SSO server to log in
	 *
	 * @return void
	 * @throws Exception
	 */
	public function linkly_login_action() {
		if ( ! isset( $_GET['linkly_login_action'] ) ) {
			return;
		}

		$decodedLoginActionUrl        = urldecode( $_GET['linkly_login_action'] );
		$sanitizedLoginActionUrl      = filter_var( $decodedLoginActionUrl, FILTER_SANITIZE_URL );
		$_SESSION['url_to_return_to'] = esc_url( get_site_url() . $sanitizedLoginActionUrl );

		unset( $_SESSION['linkly_link_account'] );

		$this->ssoHelper->authorizeRedirect();
		exit;
	}

	/**
	 * The callback action after the user has logged in on the Linkly SSO server
	 *
	 * @return void
	 */
	public function linkly_login_callback() {
		if ( ! isset( $_GET['linkly_callback'] ) ) {
			return;
		}

		try {
			$this->ssoHelper->callback();
			$linklyUser = $this->ssoHelper->getUser();
			if ( isset( $_SESSION['linkly_link_account'] ) ) {
				unset( $_SESSION['linkly_link_account'] );
				$this->attach_wc_customer_to_linkly( $linklyUser, wp_get_current_user() );
			} else {
				$user = get_user_by( 'email', $this->ssoHelper->getEmail() );
				$this->create_or_update_customer( $linklyUser, $user ?: null );
			}

			if ( str_contains( $_SESSION['url_to_return_to'], '/wp-login.php' ) ) {
				$_SESSION['url_to_return_to'] = get_site_url();
			}

			wp_redirect( esc_url( $_SESSION['url_to_return_to'] ) );
			unset( $_SESSION['url_to_return_to'] );
			exit;
		} catch ( Exception $e ) {
			wp_clear_auth_cookie();
			linkly_dd( $e );
		}
	}

	/**
	 * @param LinklyUser $linklyUser
	 * @param WP_User|null $currentUser
	 *
	 * @return void
	 * @throws Exception
	 */
	private function create_or_update_customer( LinklyUser $linklyUser, WP_User $currentUser = null ) {
		$customer = new WC_Customer( $currentUser->ID );
		linkly_update_wc_customer( $linklyUser, $customer );

		linkly_login_user( $customer );
	}

	/**
	 * @param LinklyUser $linklyUser
	 * @param WP_User $currentUser
	 *
	 * @return void
	 * @throws Exception
	 */
	private function attach_wc_customer_to_linkly( LinklyUser $linklyUser, WP_User $currentUser ) {
		// Prevent admin email from being changed
		if ( $currentUser->ID !== 0 && $linklyUser->getEmail() !== $currentUser->user_email && ! user_can( $currentUser, 'manage_options' ) ) {
			$currentUser->user_email = $linklyUser->getEmail();
			$response = wp_update_user( $currentUser );
			if ( is_wp_error( $response ) ) {
				throw new Exception( $response->get_error_message() );
			}
		}

		$customer = new WC_Customer( $currentUser->ID );
		linkly_update_wc_customer( $linklyUser, $customer );
		do_action( 'linkly_after_link_wc_account', $customer);
		linkly_sync_customer_invoices( $customer );
	}



	/**
	 * The action for the user to log out
	 *
	 * @return void
	 */
	public function linkly_logout() {
		$this->ssoHelper->logout();
	}

}

new LinklyAuthActions( LinklyHelpers::instance()->getSsoHelper() );
