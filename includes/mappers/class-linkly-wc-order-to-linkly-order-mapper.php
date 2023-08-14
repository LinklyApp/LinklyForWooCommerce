<?php

use WPO\WC\PDF_Invoices\Documents\Bulk_Document;
use WPO\WC\PDF_Invoices\Documents\Order_Document;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class LinklyWCOrderToLinklyOrderMapper
{
	/**
	 * Map the WC order to a Linkly order
	 *
	 * @param WC_Order $order
	 * @param string $statusName
	 *
	 * @return false|string
	 */
    public static function mapOrder(WC_Order $order)
    {
		return json_encode([
            'customerEmail' => $order->get_user()->user_email,
            'orderNumber' => $order->get_order_number(),
            'reference' => 'Ordered at ' . get_bloginfo('name'),
            'purchaseDate' => $order->get_date_created()->format('Y-m-d'),
			'billingAddress' => LinklyWCAddressToLinklyAddressMapper::mapBillingAddress($order),
			'shippingAddress' => $order->has_shipping_address() ? LinklyWCAddressToLinklyAddressMapper::mapShippingAddress($order) : null,
            'statusName' => LinklyWCOrderStatusNameToLinklyMapper::mapStatusName($order->get_status()),
            'countryCode' => $order->get_billing_country(),
            'taxExclusiveAmount' => $order->get_total() - $order->get_total_tax(),
            'taxAmount' => $order->get_total_tax(),
            'taxInclusiveAmount' => $order->get_total(),
            'prePaidAmount' => $order->get_date_paid() ? $order->get_total() : 0,
            'payableAmount' => $order->get_date_paid() ? 0 : $order->get_total(),
            'lines' => LinklyWCOrderItemsToLinklyOrderLinesMapper::mapOrderItems($order->get_items())
        ]);
    }
}
