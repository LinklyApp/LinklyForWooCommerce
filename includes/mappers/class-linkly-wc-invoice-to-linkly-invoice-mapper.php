<?php

use WPO\WC\PDF_Invoices\Documents\Order_Document;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly
class LinklyWCInvoiceToLinklyInvoiceMapper {
	/**
	 * Map the invoice from the WC order
	 *
	 * @param WC_Order $order
	 * @param Order_Document $orderDocument
	 *
	 * @return string
	 */
	public static function mapInvoice( WC_Order $order, Order_Document $orderDocument ) {
		$linklyInvoice = [
			'customerEmail'      => $order->get_user()->user_email,
			'invoiceNumber'      => $orderDocument->get_number()->number,
			'orderNumber'        => $order->get_order_number(),
			'reference'          => 'Ordered at ' . get_bloginfo( 'name' ),
			'billingAddress'     => LinklyWCAddressToLinklyAddressMapper::mapBillingAddress( $order ),
			'issueDate'          => $orderDocument->order->get_date_created()->format( 'Y-m-d' ),
			'dueDate'            => $orderDocument->order->get_date_created()->format( 'Y-m-d' ),
			'paidAtDate'         => $orderDocument->order->get_date_paid() ? $orderDocument->order->get_date_paid()->format( 'Y-m-d' ) : null,
			'taxExclusiveAmount' => $orderDocument->order->get_total() - $orderDocument->order->get_total_tax(),
			'taxAmount'          => $orderDocument->order->get_total_tax(),
			'taxInclusiveAmount' => $orderDocument->order->get_total(),
			'prePaidAmount'      => $orderDocument->order->get_date_paid() ? $orderDocument->order->get_total() : 0,
			'payableAmount'      => $orderDocument->order->get_date_paid() ? 0 : $orderDocument->order->get_total(),
			'statusName'         => $orderDocument->order->get_date_paid() ? 'paid' : 'open',
			'lines'              => LinklyWCOrderItemsToLinklyOrderLinesMapper::mapOrderItems( $order->get_items() ),
			'files'              => [ self::mapFile( $orderDocument ) ],
		];

		if ( $order->shipping_total > 0 ) {
			$linklyInvoice['lines'][] = [
				'sequenceNumber'    => count( $linklyInvoice['lines'] ) + 1,
				'name'              => __( 'shipping.costs', 'linkly-for-woocommerce' ),
				'unitAmountExclTax' => $order->get_shipping_total(),
				'quantity'          => 1,
				'lineAmountExclTax' => $order->get_shipping_total(),
				'taxRatePercentage' => $order->get_shipping_tax() / $order->get_shipping_total() * 100,
			];
		}

		return json_encode( $linklyInvoice );
	}

	private static function mapFile( Order_Document $orderDocument ) {
		return [
			'fileBase64' => base64_encode( $orderDocument->get_pdf() ),
			'mimeType'   => 'application/pdf',
			'name'       => $orderDocument->get_filename(),
			'isMain'     => true
		];
	}
}
