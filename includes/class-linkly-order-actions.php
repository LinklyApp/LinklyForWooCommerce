<?php

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Linkly\OAuth2\Client\Helpers\LinklyOrderHelper;
use Linkly\OAuth2\Client\Helpers\LinklySsoHelper;
use Linkly\OAuth2\Client\Provider\Exception\LinklyProviderException;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class LinklyOrderActions {
	/**
	 * @var LinklyOrderHelper
	 */
	private LinklyOrderHelper $linklyOrderHelper;

	/**
	 * @var string The status name for when the order is processing
	 */
	private string $processing_status_name = "Processing";

	/**
	 * @var string The status name for when the order is completed
	 */
	private string $completed_status_name = "Completed";

	public function __construct( LinklyOrderHelper $linklyOrderHelper ) {
		$this->linklyOrderHelper = $linklyOrderHelper;

		add_action( 'woocommerce_account_dashboard', [ $this, 'sync_current_wc_customer_invoices_to_linkly' ], 999 );
		add_action( 'woocommerce_after_account_orders', [ $this, 'sync_current_wc_customer_invoices_to_linkly' ], 999 );
		add_action( 'linkly_after_link_wc_account', [ $this, 'sync_current_wc_customer_invoices_to_linkly' ] );
		add_action( 'woocommerce_order_status_processing', [ $this, 'linkly_order_to_processing' ] );
		add_action( 'woocommerce_order_status_completed', [ $this, 'linkly_order_to_completed' ] );
	}

	function linkly_order_to_processing( $order_id ) {
		try {
			$order = wc_get_order( $order_id );

			$customer = new WC_Customer( $order->get_user_id() );

			if ( ! $customer->get_meta( 'linkly_user' ) ) {
				return;
			}

			$orderData = LinklyWCOrderToLinklyOrderMapper::mapOrder( $order, $this->processing_status_name );
			$this->linklyOrderHelper->sendOrder( $orderData );
			$order->add_meta_data( 'linkly_exported', gmdate( "Y-m-d H:i:s" ) . ' +00:00' );
			$order->save();
		} catch ( LinklyProviderException $e ) {
			linkly_dd( $e->getResponseBody() );
		} catch ( IdentityProviderException $e ) {
			linkly_dd( [ $e->getResponseBody() ] );
		}
	}

	function linkly_order_to_completed( $order_id ) {
		try {
			$order    = wc_get_order( $order_id );
			$customer = new WC_Customer( $order->get_user_id() );

			if ( ! $customer->get_meta( 'linkly_user' ) ) {
				return;
			}

			$orderData = LinklyWCOrderToLinklyOrderMapper::mapOrder( $order, $this->completed_status_name );
			$this->linklyOrderHelper->sendOrder( $orderData );

			if ( linkly_is_pdf_invoices_plugin_active() ) {
				$orderDocument = wcpdf_get_document( 'invoice', $order, true );
				$invoiceData   = LinklyWCInvoiceToLinklyInvoiceMapper::mapInvoice( $order, $orderDocument );
				$this->linklyOrderHelper->sendInvoice( $invoiceData );
			}

			$order->add_meta_data( 'linkly_exported', gmdate( "Y-m-d H:i:s" ) . ' +00:00' );
			$order->save();
		} catch ( LinklyProviderException $e ) {
			error_log( $e->getMessage() );
		} catch ( Exception $e ) {
			error_log( $order, $e->getMessage() );
		}
	}

	public function sync_current_wc_customer_invoices_to_linkly(): void {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$customer = new WC_Customer( get_current_user_id() );

		if ( ! $customer->get_meta( 'linkly_user' ) ) {
			return;
		}

		$this->sync_customer_orders_and_invoices( $customer );
	}


	/**
	 * @param WC_Customer $customer
	 *
	 * @return void
	 */
	private function sync_customer_orders_and_invoices( WC_Customer $customer ): void {
		$args = array(
			'limit'        => - 1, // Limit of orders to retrieve
			'meta_key'     => 'linkly_order_exported', // Postmeta key field
			'meta_compare' => 'NOT EXISTS',
			'customer_id'  => $customer->get_id(), // User ID
			'return'       => 'objects', // Possible values are ‘ids’ and ‘objects’.
		);

		$orders = wc_get_orders( $args );

		foreach ( $orders as $order ) {
			try {
				$orderData = LinklyWCOrderToLinklyOrderMapper::mapOrder( $order, $order->get_status() );
				$this->linklyOrderHelper->sendOrder( $orderData );
				$order->add_meta_data( 'linkly_order_exported', gmdate( "Y-m-d H:i:s" ) . ' +00:00' );
				$order->save();

				if ( $order->get_status() !== 'completed' || ! linkly_is_pdf_invoices_plugin_active() ) {
					continue;
				}

				$invoice     = wcpdf_get_document( 'invoice', $order, true );
				$invoiceData = LinklyWCInvoiceToLinklyInvoiceMapper::mapInvoice( $order, $invoice );
				$this->linklyOrderHelper->sendInvoice( $invoiceData );

				$order->add_meta_data( 'linkly_invoice_exported', gmdate( "Y-m-d H:i:s" ) . ' +00:00' );
				$order->save();
			} catch ( LinklyProviderException $e ) {
				dd( $e->getResponseBody() );
				error_log( json_encode( $e->getResponseBody() ) );
			} catch ( Exception $e ) {
				error_log( json_encode( $e->getMessage() ) );
			}
		}
	}

}

new LinklyOrderActions( LinklyHelpers::instance()->getOrderHelper() );
