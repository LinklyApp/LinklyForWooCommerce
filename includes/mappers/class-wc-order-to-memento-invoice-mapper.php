<?php


class WCOrderToMementoInvoiceMapper
{
    public static function map(WC_Order $order, $memento_user_email) {

        return json_encode([
            'customerEmail' => $memento_user_email,
            'invoiceNumber' => $order->get_order_number(),
            'orderNumber' => $order->get_order_number(),
            'reference' => 'Shopping at store',
            'countryCode' => $order->get_billing_country(),
            'issueDate' => $order->get_date_created()->format('Y-m-d'),
            'dueDate' => $order->get_date_created()->format('Y-m-d'),
            'paidAtDate' => $order->get_date_paid() ? $order->get_date_paid()->format('Y-m-d') : null,
            'taxExclusiveAmount' => (float) $order->get_total() - $order->get_total_tax(),
            'taxAmount' => (float) $order->get_total_tax(),
            'taxInclusiveAmount' => (float) $order->get_total(),
            'paidAmount' => (float) $order->get_date_paid() ? $order->get_total() : 0,
            'payableAmount' => (float) $order->get_date_paid() ? 0 : $order->get_total(),
            'lines' => self::generateInvoiceLines($order->get_items())
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
            $invoiceLine['totalAmount'] = $item->get_total();
            $invoiceLine['taxRatePercentage'] = $taxRatePercentage;
            $invoiceLines[] = $invoiceLine;

            $i++;
        }

        return $invoiceLines;
    }
}
