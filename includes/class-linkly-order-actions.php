<?php

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Linkly\OAuth2\Client\Helpers\LinklyOrderHelper;
use Linkly\OAuth2\Client\Helpers\LinklySsoHelper;
use Linkly\OAuth2\Client\Provider\Exception\LinklyProviderException;
use function Linkly\OAuth2\Client\Helpers\dd;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class LinklyOrderActions {
	/**
	 * @var LinklyOrderHelper
	 */
	private LinklyOrderHelper $linklyOrderHelper;

	private LinklyHelpers $linklyHelpers;


	public function __construct( LinklyHelpers $linklyHelpers ) {
		$this->linklyHelpers     = $linklyHelpers;
		$this->linklyOrderHelper = $linklyHelpers->getOrderHelper();

		add_action( 'woocommerce_account_dashboard', [ $this, 'sync_current_wc_customer_invoices_to_linkly' ], 999 );
		add_action( 'woocommerce_after_account_orders', [ $this, 'sync_current_wc_customer_invoices_to_linkly' ], 999 );
		add_action( 'linkly_after_link_wc_account', [ $this, 'sync_current_wc_customer_invoices_to_linkly' ] );
		add_action( 'woocommerce_order_status_changed', [ $this, 'linkly_order_status_changed' ] );
		add_action( 'woocommerce_order_status_completed', [ $this, 'linkly_order_to_completed' ] );
	}

	public function linkly_order_status_changed( $order_id ) {
		$this->send_linkly_order_and_invoice_to_linkly( $order_id );
	}

	public function sync_current_wc_customer_invoices_to_linkly(): void {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$customer = new WC_Customer( get_current_user_id() );

		if ( ! linkly_is_wp_user_linkly_user( $customer->get_id() ) ) {
			return;
		}

		$this->sync_customer_orders_and_invoices( $customer );
	}

	/**
	 * @param WC_Customer $customer
	 *
	 * @return void
	 */
	/**
	 * @param WC_Customer $customer
	 *
	 * @return void
	 */
	private function sync_customer_orders_and_invoices( WC_Customer $customer ): void {
		// Base arguments
		$args = array(
			'limit'       => - 1, // Limit of orders to retrieve
			'customer_id' => $customer->get_id(), // User ID
			'return'      => 'objects', // Possible values are ‘ids’ and ‘objects’.
			'meta_query'  => array(
				array(
					'key'     => 'linkly_order_exported',
					'compare' => 'NOT EXISTS', // This ensures the 'linkly_order_exported' meta key does not exist
				),
			)
		);

		if ( linkly_is_pdf_invoices_plugin_active() ) {
			$args['meta_query']['relation'] = 'OR'; // This means only one of the arrays needs to be true
			$args['meta_query'][]           = array(
				'key'     => 'linkly_invoice_exported',
				'compare' => 'NOT EXISTS', // This ensures the 'invoice_exported' meta key does not exist
			);
		}

		$orders = wc_get_orders( $args );

		foreach ( $orders as $order ) {
			$this->send_linkly_order_and_invoice_to_linkly( $order->get_id() );
		}
	}

	/**
	 * @param int $order_id
	 * @param string $status_name
	 * @param bool $handle_invoice
	 *
	 * @return void
	 */
	private function send_linkly_order_and_invoice_to_linkly( $order_id ) {
		try {
			if ( ! $this->linklyHelpers->isConnected() ) {
				return;
			}

			$order    = wc_get_order( $order_id );
			$customer = new WC_Customer( $order->get_user_id() );

			if ( ! linkly_is_wp_user_linkly_user( $customer->get_id() ) ) {
				return;
			}

			if ( ! $order->get_meta( 'linkly_order_exported' ) ) {
				$orderData = LinklyWCOrderToLinklyOrderMapper::mapOrder( $order );
				$this->linklyOrderHelper->sendOrder( $orderData );
				$order->add_meta_data( 'linkly_order_exported', gmdate( "Y-m-d H:i:s" ) . ' +00:00' );
			}


			if ( linkly_is_pdf_invoices_plugin_active() && $order->get_status() === 'completed' && !$order->get_meta( 'linkly_invoice_exported' ) ) {
				$orderDocument = wcpdf_get_document( 'invoice', $order, true );
				$invoiceData   = LinklyWCInvoiceToLinklyInvoiceMapper::mapInvoice( $order, $orderDocument );
				$this->linklyOrderHelper->sendInvoice( $invoiceData );
				$order->add_meta_data( 'linkly_invoice_exported', gmdate( "Y-m-d H:i:s" ) . ' +00:00' );
			}

			$order->save();
		} catch ( LinklyProviderException $e ) {
			error_log( json_encode( $e->getResponseBody() ) );
		} catch ( IdentityProviderException $e ) {
			error_log( json_encode( $e->getResponseBody() ) );
		} catch ( Exception $e ) {
			error_log( json_encode( $e->getMessage() ) );
		}
	}

}

new LinklyOrderActions( LinklyHelpers::instance() );
