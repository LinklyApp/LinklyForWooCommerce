<?php

use WPO\WC\PDF_Invoices\Documents\Bulk_Document;
use WPO\WC\PDF_Invoices\Documents\Order_Document;

class WCInvoiceToLinklyInvoiceMapper
{
	/**
	 * Map the invoice from the WC order
	 *
	 * @param WC_Order $order
	 * @param Order_Document $orderDocument
	 *
	 * @return string
	 */
    public static function mapInvoice(WC_Order $order, Order_Document $orderDocument)
    {
        // TODO - get the correct invoice status according to orderDocument data
        $invoiceBaseStatus = "paid";

        return json_encode([
	        'customerEmail' => $order->get_user()->user_email,
            'invoiceNumber' => $orderDocument->get_number()->number,
            'orderNumber' => $order->get_order_number(),
            'reference' => LinklyLanguageHelper::instance()->get('order_description', [get_bloginfo('name')]),
			'billingAddress' => WCAddressToLinklyAddressMapper::mapBillingAddress($order),
            'issueDate' => $orderDocument->order->get_date_created()->format('Y-m-d'),
            'dueDate' => $orderDocument->order->get_date_created()->format('Y-m-d'),
            'paidAtDate' => $orderDocument->order->get_date_paid() ? $orderDocument->order->get_date_paid()->format('Y-m-d') : null,
            'taxExclusiveAmount' => $orderDocument->order->get_total() - $orderDocument->order->get_total_tax(),
            'taxAmount' => $orderDocument->order->get_total_tax(),
            'taxInclusiveAmount' => $orderDocument->order->get_total(),
            'prePaidAmount' => $orderDocument->order->get_date_paid() ? $orderDocument->order->get_total() : 0,
            'payableAmount' => $orderDocument->order->get_date_paid() ? 0 : $orderDocument->order->get_total(),
            'statusName' => $invoiceBaseStatus,
            'lines' => WCOrderItemsToLinklyOrderLinesMapper::mapOrderItems($order->get_items()),
            'pdf' => base64_encode($orderDocument->get_pdf()),
        ]);
    }
}
