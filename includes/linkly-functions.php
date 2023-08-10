<?php

use Linkly\OAuth2\Client\Provider\Exception\LinklyProviderException;
use Linkly\OAuth2\Client\Provider\User\LinklyUser;
use function Linkly\OAuth2\Client\Helpers\dd;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

/**
 * @param LinklyUser $linklyUser
 * @param WP_User|null $currentUser
 *
 * @return void
 * @throws Exception
 */
function linkly_createOrUpdateCustomer( LinklyUser $linklyUser, WP_User $currentUser = null ) {
	$mappedCustomer = LinklyCustomerToWCCustomerMapper::map( $linklyUser );
	$customer       = new WC_Customer( $currentUser->ID );

	$customer->set_props( $mappedCustomer );

	$customer->add_meta_data( 'linkly_user', true, true );
	$customer->update_meta_data( 'linkly_billing_id', $linklyUser->getBillingAddress()->getId() );
	$customer->update_meta_data( 'linkly_billing_version', $linklyUser->getBillingAddress()->getVersion() );
	$customer->update_meta_data( 'linkly_shipping_id', $linklyUser->getShippingAddress()->getId() );
	$customer->update_meta_data( 'linkly_shipping_version', $linklyUser->getShippingAddress()->getVersion() );

	$customer->save();

	linkly_login_user( $customer );
}

/**
 * @param LinklyUser $linklyUser
 * @param WP_User $currentUser
 *
 * @return void
 * @throws Exception
 */
function linkly_attachWCCustomer( LinklyUser $linklyUser, WP_User $currentUser ) {
	$mappedCustomer = LinklyCustomerToWCCustomerMapper::map( $linklyUser );

	if ( $currentUser->ID !== 0 && $linklyUser->getEmail() !== $currentUser->user_email && ! user_can( $currentUser, 'manage_options' ) ) {
		$currentUser->user_email = $linklyUser->getEmail();
		$response                = wp_update_user( $currentUser );
		if ( is_wp_error( $response ) ) {
			throw new Exception( $response->get_error_message() );
		}
	}

	$customer = new WC_Customer( $currentUser->ID );

	$customer->set_props( $mappedCustomer );
	$customer->add_meta_data( 'linkly_user', true, true );
	$customer->save();

	linkly_sync_customer_invoices( $customer );
}

/**
 * @param WC_Customer $customer
 *
 * @return void
 */
function linkly_sync_customer_invoices( WC_Customer $customer ): void {
	$args = array(
		'limit'        => - 1, // Limit of orders to retrieve
		'meta_key'     => 'linkly_order_exported', // Postmeta key field
		'meta_compare' => 'NOT EXISTS',
		'customer_id'  => $customer->get_id(), // User ID
		'return'       => 'objects', // Possible values are ‘ids’ and ‘objects’.
	);

	$orders = wc_get_orders( $args );

	$linklyOrderHelper = LinklyHelpers::instance()->getInvoiceHelper();

	foreach ( $orders as $order ) {
		try {
			$orderData = LinklyWCOrderToLinklyOrderMapper::mapOrder( $order, $order->get_status());
			$linklyOrderHelper->sendOrder( $orderData );
			$order->add_meta_data( 'linkly_order_exported', gmdate( "Y-m-d H:i:s" ) . ' +00:00' );
			$order->save();

			if ( $order->get_status() !== 'completed' || ! linkly_is_pdf_invoices_plugin_active() ) {
				continue;
			}

			$invoice     = wcpdf_get_document( 'invoice', $order, true );
			$invoiceData = LinklyWCInvoiceToLinklyInvoiceMapper::mapInvoice( $order, $invoice );
			$linklyOrderHelper->sendInvoice( $invoiceData );

			$order->add_meta_data( 'linkly_invoice_exported', gmdate( "Y-m-d H:i:s" ) . ' +00:00' );
			$order->save();
		} catch ( LinklyProviderException $e ) {
			dd( $e->getResponseBody());
			error_log( json_encode($e->getResponseBody()) );
		} catch ( Exception $e ) {
			error_log( json_encode($e->getMessage()) );
		}
	}
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

/**
 * @return string
 */
function linkly_getBaseUrl() {
	$env = get_option( 'linkly_settings_environment' );
	if ( $env === 'local' ) {
		return LinklyHelpers::instance()->getLinklyProvider()->localDomain;
	}
	if ( $env === 'beta' ) {
		return LinklyHelpers::instance()->getLinklyProvider()->betaDomain;
	}

	return LinklyHelpers::instance()->getLinklyProvider()->domain;
}

function linkly_dd( $variable ) {
	$variable_esc = esc_html( $variable );
	echo "<pre>";
	var_export( $variable_esc );
	echo "</pre>";
	die;
}
