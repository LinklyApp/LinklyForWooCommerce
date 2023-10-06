<?php

use Linkly\OAuth2\Client\Helpers\LinklySsoHelper;
use Linkly\OAuth2\Client\Provider\User\LinklyUser;
use function Linkly\OAuth2\Client\Helpers\dd;

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

		// Sanitize the URL before decoding.
		$sanitizedAccountActionPath = sanitize_url($_GET['linkly_link_account_action']);
		$decodedAccountActionPath = urldecode($sanitizedAccountActionPath);

		$_SESSION['url_to_return_to'] = esc_url_raw(get_site_url() . $decodedAccountActionPath);

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

		// Sanitize first.
		$sanitizedLoginActionPath = sanitize_url($_GET['linkly_login_action']);
		$decodedLoginActionUrl = urldecode($sanitizedLoginActionPath);

		$_SESSION['url_to_return_to'] = esc_url_raw(get_site_url() . $decodedLoginActionUrl);

		unset($_SESSION['linkly_link_account']);

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

			$sanitizedUrlToReturnTo = sanitize_url($_SESSION['url_to_return_to']);

			if ( str_contains( $sanitizedUrlToReturnTo, '/wp-login.php' ) ) {
				$sanitizedUrlToReturnTo = get_site_url();
			}

			wp_redirect( esc_url( $sanitizedUrlToReturnTo ) );
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
		if ( $currentUser->ID !== 0 && $linklyUser->getEmail() !== $currentUser->user_email ) {
			if( user_can( $currentUser, 'manage_options' ) ) {
				throw new Exception( 'When a WordPress account is connected to Linkly, the email gets updated to the Linkly email address. However, this action is not permissible for admin accounts. We kindly request that you update your WordPress email to match the one you use in Linkly. Once updated, linking your account will then be possible.
				' );
			}

			$currentUser->user_email = $linklyUser->getEmail();
			$response = wp_update_user( $currentUser );
			if ( is_wp_error( $response ) ) {
				throw new Exception( $response->get_error_message() );
			}
		}

		$customer = new WC_Customer( $currentUser->ID );
		linkly_update_wc_customer( $linklyUser, $customer );
		do_action( 'linkly_after_link_wc_account', $customer);
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
