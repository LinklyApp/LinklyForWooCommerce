<?php


use WPO\WC\PDF_Invoices\Documents\Bulk_Document;
use WPO\WC\PDF_Invoices\Documents\Order_Document;

class WCOrderToLinklyInvoiceMapper
{
    public static function mapInvoice(Order_Document $invoice)
    {
        return json_encode([
            'customerEmail' => $invoice->order->get_user()->user_email,
            'invoiceNumber' => $invoice->get_number()->number,
            'orderNumber' => $invoice->get_order_number(),
            'reference' => 'Shopping at store',
            'countryCode' => $invoice->order->get_billing_country(),
            'issueDate' => $invoice->order->get_date_created()->format('Y-m-d'),
            'dueDate' => $invoice->order->get_date_created()->format('Y-m-d'),
            'paidAtDate' => $invoice->order->get_date_paid() ? $invoice->order->get_date_paid()->format('Y-m-d') : null,
            'taxExclusiveAmount' => (float) $invoice->order->get_total() - $invoice->order->get_total_tax(),
            'taxAmount' => (float) $invoice->order->get_total_tax(),
            'taxInclusiveAmount' => (float) $invoice->order->get_total(),
            'paidAmount' => (float) $invoice->order->get_date_paid() ? $invoice->order->get_total() : 0,
            'payableAmount' => (float) $invoice->order->get_date_paid() ? 0 : $invoice->order->get_total(),
            'lines' => self::generateInvoiceLines($invoice->order->get_items()),
            'file' => base64_encode($invoice->get_pdf()),
        ]);
    }

    /**
     * @param WC_Order_Item[] $items
     */
    private static function generateInvoiceLines(array $items)
    {
        $invoiceLines = [];
        $i = 1;
        foreach ($items as $item) {
            $taxRatePercentage = current(WC_Tax::get_rates( $item->get_tax_class(), WC()->customer ))['rate'];
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
