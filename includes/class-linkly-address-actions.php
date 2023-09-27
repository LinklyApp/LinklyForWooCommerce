<?php

use Linkly\OAuth2\Client\Helpers\LinklySsoHelper;
use Linkly\OAuth2\Client\Provider\User\LinklyUser;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class LinklyAddressActions {
	/**
	 * @var LinklySsoHelper
	 */
	private LinklySsoHelper $ssoHelper;

	public function __construct( LinklySsoHelper $ssoHelper ) {
		$this->ssoHelper = $ssoHelper;

		add_action( 'init', [ $this, 'linkly_change_address_action' ] );
		add_action( 'init', [ $this, 'linkly_change_address_callback' ] );

		add_action( 'woocommerce_before_checkout_form', [ $this, 'linkly_check_and_update_addresses_if_changed' ] );
		add_action( 'woocommerce_before_edit_account_address_form', [
			$this,
			'linkly_check_and_update_addresses_if_changed'
		] );
	}

	/**
	 * The action to redirect to the Linkly SSO server to change the address
	 *
	 * @return void
	 * @throws Exception
	 */
	public function linkly_link_account_action() {
		if ( ! isset( $_GET['linkly_link_account_action'] ) ) {
			return;
		}

		// Sanitizing the URL before decoding.
		$sanitizedAccountActionUrl = sanitize_url( $_GET['linkly_link_account_action'] );
		$decodedAccountActionUrl = urldecode( $sanitizedAccountActionUrl );

		// Further validation if necessary.
		if ( filter_var($decodedAccountActionUrl, FILTER_VALIDATE_URL) === false ) {
			throw new Exception( 'Invalid URL' );
		}

		$_SESSION['url_to_return_to'] = esc_url_raw( get_site_url() . $decodedAccountActionUrl );

		$_SESSION['linkly_link_account'] = true;
		$this->ssoHelper->authorizeRedirect();
		exit;
	}
	/**
	 * The callback action after the address has been changed on the Linkly SSO server
	 *
	 * @return void
	 */
	public function linkly_change_address_callback(): void {
		if ( ! isset( $_GET['linkly_change_address_callback'] ) ) {
			return;
		}

		try {
			$this->linkly_check_and_update_addresses_if_changed();
		} catch ( Exception $e ) {
			error_log( $e->getMessage() );
		}

		$rawUrlToReturnTo = $_SESSION['url_to_return_to'];
		$sanitizedUrlToReturnTo = sanitize_url($rawUrlToReturnTo);

		wp_redirect( esc_url( $sanitizedUrlToReturnTo) );
		unset( $_SESSION['url_to_return_to'] );
		exit;
	}

	/**
	 * Checks if the address has been changed and updates it if it has
	 *
	 * @return void
	 * @throws Exception
	 */
	public function linkly_check_and_update_addresses_if_changed(): void {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$customer = new WC_Customer( get_current_user_id() );
		if ( ! linkly_is_wp_user_linkly_user( $customer->get_id() ) ) {
			return;
		}

		$addressData = [
			'billingAddressId'       => $customer->get_meta( 'linkly_billing_id' ),
			'billingAddressVersion'  => $customer->get_meta( 'linkly_billing_version' ),
			'shippingAddressId'      => $customer->get_meta( 'linkly_shipping_id' ),
			'shippingAddressVersion' => $customer->get_meta( 'linkly_shipping_version' ),
		];

		try {
			if ( ! $this->ssoHelper->hasAddressBeenChanged( $addressData ) ) {
				return;
			}

			$linklyUser = $this->ssoHelper->getUser();
			linkly_update_wc_customer( $linklyUser, $customer );
		} catch ( Exception $e ) {
			error_log( $e->getMessage() );
		}
	}
}

new LinklyAddressActions( LinklyHelpers::instance()->getSsoHelper() );
