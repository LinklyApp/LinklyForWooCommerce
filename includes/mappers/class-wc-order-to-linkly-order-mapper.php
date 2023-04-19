<?php

use WPO\WC\PDF_Invoices\Documents\Bulk_Document;
use WPO\WC\PDF_Invoices\Documents\Order_Document;

class WCOrderToLinklyOrderMapper
{
    public static function mapOrder(WC_Order $order, string $statusName)
    {
        return json_encode([
            'customerEmail' => $order->get_user()->user_email,
            'orderNumber' => $order->get_order_number(),
            'reference' => LinklyLanguageHelper::instance()->get('order_description', [get_bloginfo('name')]),
            'purchaseDate' => $order->get_date_created()->format('Y-m-d'),
            'statusName' => $statusName,
            'countryCode' => $order->get_billing_country(),
            'taxExclusiveAmount' => $order->get_total() - $order->get_total_tax(),
            'taxAmount' => $order->get_total_tax(),
            'taxInclusiveAmount' => $order->get_total(),
            'prePaidAmount' => $order->get_date_paid() ? $order->get_total() : 0,
            'payableAmount' => $order->get_date_paid() ? 0 : $order->get_total(),
            'lines' => self::generateOrderLines($order->get_items())
        ]);
    }

    /**
     * @param WC_Order_Item[] $orderItems
     */
    private static function generateOrderLines(array $orderItems)
    {
        $orderLines = [];
        $i = 1;
        foreach ($orderItems as $item) {
            $taxRatePercentage = current(WC_Tax::get_rates($item->get_tax_class(), WC()->customer))['rate'];
            $orderLine['sequenceNumber'] = $i;
            $orderLine['name'] = $item->get_name();
            $orderLine['unitAmount'] = $item->get_total() / $item->get_quantity();
            $orderLine['quantity'] = $item->get_quantity();
            $orderLine['lineAmount'] = $item->get_total();
            $orderLine['taxRatePercentage'] = $taxRatePercentage;
            $orderLines[] = $orderLine;

            $i++;
        }

        return $orderLines;
    }
}
