<?php

use Linkly\OAuth2\Client\Provider\Exception\LinklyProviderException;
use Linkly\OAuth2\Client\Provider\User\LinklyUser;
use function Linkly\OAuth2\Client\Helpers\dd;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * @param LinklyUser $linklyUser
 * @param WC_Customer $customer
 *
 * @return void
 */
function linkly_update_wc_customer( LinklyUser $linklyUser, WC_Customer $customer ) {
	$mappedCustomer = LinklyCustomerToWCCustomerMapper::map( $linklyUser );
	$customer->set_props( $mappedCustomer );

	$customer->add_meta_data( 'linkly_user', true, true );
	$customer->update_meta_data( 'linkly_billing_id', $linklyUser->getBillingAddress()->getId() );
	$customer->update_meta_data( 'linkly_billing_version', $linklyUser->getBillingAddress()->getVersion() );
	$customer->update_meta_data( 'linkly_shipping_id', $linklyUser->getShippingAddress()->getId() );
	$customer->update_meta_data( 'linkly_shipping_version', $linklyUser->getShippingAddress()->getVersion() );

	$customer->save();
}

function linkly_is_pdf_invoices_plugin_active() {
	$pdf_invoices_plugin = 'woocommerce-pdf-invoices-packing-slips/woocommerce-pdf-invoices-packingslips.php';

	return is_plugin_active( $pdf_invoices_plugin );
}

/**
 * @param WC_Customer $customer
 *
 * @return void
 */
function linkly_login_user( WC_Customer $customer ) {
	wp_clear_auth_cookie();
	wc_set_customer_auth_cookie( $customer->get_id() );

	if ( linkly_is_billing_address_equal_to_shipping_address( $customer ) ) {
		add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );
	}
}

/**
 * @param int $userId
 *
 * @return bool
 */
function linkly_is_wp_user_linkly_user( int $userId ): bool {
	return get_user_meta( $userId, 'linkly_user', true );
}

/**
 * @param WC_Customer $customer
 *
 * @return bool
 */
function linkly_is_billing_address_equal_to_shipping_address( WC_Customer $customer ): bool {
	return $customer->get_billing_address_1() === $customer->get_shipping_address_1()
	       && $customer->get_billing_first_name() === $customer->get_shipping_first_name()
	       && $customer->get_billing_last_name() === $customer->get_shipping_last_name();
}

function linkly_dd( $variable ) {
	$variable_esc = esc_html( $variable );
	echo "<pre>";
	var_export( $variable_esc );
	echo "</pre>";
	die;
}
