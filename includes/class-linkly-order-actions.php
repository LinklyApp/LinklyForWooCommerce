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

	public function linkly_order_to_processing( $order_id ) {
		$this->send_linkly_order_and_invoice_to_linkly( $order_id, $this->processing_status_name );
	}

	public function linkly_order_to_completed( $order_id ) {
		$this->send_linkly_order_and_invoice_to_linkly( $order_id, $this->completed_status_name, true );
	}

	public function sync_current_wc_customer_invoices_to_linkly(): void {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$customer = new WC_Customer( get_current_user_id() );

		if ( ! linkly_is_wp_user_linkly_user($customer->get_id()) ) {
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
		$args = array(
			'limit'        => -1, // Limit of orders to retrieve
			'meta_key'     => 'linkly_order_exported', // Postmeta key field
			'meta_compare' => 'NOT EXISTS',
			'customer_id'  => $customer->get_id(), // User ID
			'return'       => 'objects', // Possible values are ‘ids’ and ‘objects’.
		);

		$orders = wc_get_orders( $args );

		foreach ( $orders as $order ) {
			$status_name = $order->get_status();
			$handle_invoice = ($status_name === 'completed');

			$this->send_linkly_order_and_invoice_to_linkly( $order->get_id(), $status_name, $handle_invoice);

			if ($handle_invoice && ! $order->get_meta('linkly_invoice_exported')) {
				$order->add_meta_data( 'linkly_invoice_exported', gmdate( "Y-m-d H:i:s" ) . ' +00:00' );
				$order->save();
			}
		}
	}

	/**
	 * @param int $order_id
	 * @param string $status_name
	 * @param bool $handle_invoice
	 *
	 * @return void
	 */
	private function send_linkly_order_and_invoice_to_linkly( $order_id, $status_name, $handle_invoice = false ) {
		try {
			$order    = wc_get_order( $order_id );
			$customer = new WC_Customer( $order->get_user_id() );

			if ( ! linkly_is_wp_user_linkly_user($customer->get_id()) ) {
				return;
			}

			$orderData = LinklyWCOrderToLinklyOrderMapper::mapOrder( $order, $status_name );
			$this->linklyOrderHelper->sendOrder( $orderData );

			if ( $handle_invoice && linkly_is_pdf_invoices_plugin_active() ) {
				$orderDocument = wcpdf_get_document( 'invoice', $order, true );
				$invoiceData   = LinklyWCInvoiceToLinklyInvoiceMapper::mapInvoice( $order, $orderDocument );
				$this->linklyOrderHelper->sendInvoice( $invoiceData );
			}

			$order->add_meta_data( 'linkly_exported', gmdate( "Y-m-d H:i:s" ) . ' +00:00' );
			$order->save();
		} catch ( LinklyProviderException $e ) {
			error_log( json_encode($e->getResponseBody()) );
		} catch ( IdentityProviderException $e ) {
			error_log( json_encode($e->getResponseBody()) );
		} catch ( Exception $e ) {
			error_log( json_encode($e->getMessage()) );
		}
	}

}

new LinklyOrderActions( LinklyHelpers::instance()->getOrderHelper() );
