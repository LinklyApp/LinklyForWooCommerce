<?php

use WPO\WC\PDF_Invoices\Documents\Bulk_Document;
use WPO\WC\PDF_Invoices\Documents\Order_Document;

class WCOrderToLinklyOrderMapper
{
	/**
	 * Map the WC order to a Linkly order
	 *
	 * @param WC_Order $order
	 * @param string $statusName
	 *
	 * @return false|string
	 */
    public static function mapOrder(WC_Order $order, string $statusName)
    {
        return json_encode([
            'customerEmail' => $order->get_user()->user_email,
            'orderNumber' => $order->get_order_number(),
            'reference' => LinklyLanguageHelper::instance()->get('order_description', [get_bloginfo('name')]),
            'purchaseDate' => $order->get_date_created()->format('Y-m-d'),
			'billingAddress' => WCAddressToLinklyAddressMapper::mapBillingAddress($order),
			'shippingAddress' => WCAddressToLinklyAddressMapper::mapShippingAddress($order),
            'statusName' => $statusName,
            'countryCode' => $order->get_billing_country(),
            'taxExclusiveAmount' => $order->get_total() - $order->get_total_tax(),
            'taxAmount' => $order->get_total_tax(),
            'taxInclusiveAmount' => $order->get_total(),
            'prePaidAmount' => $order->get_date_paid() ? $order->get_total() : 0,
            'payableAmount' => $order->get_date_paid() ? 0 : $order->get_total(),
            'lines' => WCOrderItemsToLinklyOrderLinesMapper::mapOrderItems($order->get_items())
        ]);
    }
}
