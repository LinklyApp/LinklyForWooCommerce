<?php

use WPO\WC\PDF_Invoices\Documents\Bulk_Document;
use WPO\WC\PDF_Invoices\Documents\Order_Document;

class WCInvoiceToLinklyInvoiceMapper
{
    public static function mapInvoice(WC_Order $order, Order_Document $orderDocument)
    {
        // TODO - get the correct invoice status according to orderDocument data
        $invoiceBaseStatus = "Open";

        return json_encode([
	        'customerEmail' => $order->get_user()->user_email,
            'invoiceNumber' => $orderDocument->get_number()->number,
            'orderNumber' => $order->get_order_number(),
            'reference' => LinklyLanguageHelper::instance()->get('order_description', [get_bloginfo('name')]),
            'issueDate' => $orderDocument->order->get_date_created()->format('Y-m-d'),
            'dueDate' => $orderDocument->order->get_date_created()->format('Y-m-d'),
            'paidAtDate' => $orderDocument->order->get_date_paid() ? $orderDocument->order->get_date_paid()->format('Y-m-d') : null,
            'taxExclusiveAmount' => $orderDocument->order->get_total() - $orderDocument->order->get_total_tax(),
            'taxAmount' => $orderDocument->order->get_total_tax(),
            'taxInclusiveAmount' => $orderDocument->order->get_total(),
            'prePaidAmount' => $orderDocument->order->get_date_paid() ? $orderDocument->order->get_total() : 0,
            'payableAmount' => $orderDocument->order->get_date_paid() ? 0 : $orderDocument->order->get_total(),
            'statusName' => $invoiceBaseStatus,
            'lines' => self::generateInvoiceLines($order->get_items()),
            'pdf' => base64_encode($orderDocument->get_pdf()),
        ]);
    }

	/**
	 * @param WC_Order_Item[] $orderItems
	 */
	private static function generateInvoiceLines(array $orderItems)
	{
		$invoiceLines = [];
		$i = 1;
		foreach ($orderItems as $item) {
			$taxRatePercentage = current(WC_Tax::get_rates($item->get_tax_class(), WC()->customer))['rate'];
			$invoiceLine['sequenceNumber'] = $i;
			$invoiceLine['name'] = $item->get_name();
			$invoiceLine['unitAmount'] = $item->get_total() / $item->get_quantity();
			$invoiceLine['quantity'] = $item->get_quantity();
			$invoiceLine['lineAmount'] = $item->get_total();
			$invoiceLine['taxRatePercentage'] = $taxRatePercentage;
			$invoiceLines[] = $invoiceLine;

			$i++;
		}

		return $invoiceLines;
	}
}
