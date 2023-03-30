<?php


use WPO\WC\PDF_Invoices\Documents\Bulk_Document;
use WPO\WC\PDF_Invoices\Documents\Order_Document;

class WCOrderToLinklyInvoiceMapper
{
    public static function mapOrder(WC_Order $order, Order_Document $orderDocument = null)
    {

        $response = json_encode([
            'customerEmail' => $order->get_user()->user_email,
            'orderNumber' => $order->get_order_number(),
            'reference' => LinklyLanguageHelper::instance()->get('order_description', [get_bloginfo('name')]),
            'countryCode' => $order->get_billing_country(),
            'taxExclusiveAmount' => $order->get_total() - $order->get_total_tax(),
            'taxAmount' => $order->get_total_tax(),
            'taxInclusiveAmount' => $order->get_total(),
            'prePaidAmount' => $order->get_date_paid() ? $order->get_total() : 0,
            'payableAmount' => $order->get_date_paid() ? 0 : $order->get_total(),
            'lines' => self::generateOrderLines($order->get_items()),
            'invoice' => $orderDocument != null ? self::getInvoice($orderDocument) : null
        ]);

        return $response;
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

    private static function getInvoice(Order_Document $orderDocument){
        return [
            'invoiceNumber' => $orderDocument->get_number()->number,
            'reference' => LinklyLanguageHelper::instance()->get('order_description', [get_bloginfo('name')]),
            'issueDate' => $orderDocument->order->get_date_created()->format('Y-m-d'),
            'dueDate' => $orderDocument->order->get_date_created()->format('Y-m-d'),
            'paidAtDate' => $orderDocument->order->get_date_paid() ? $orderDocument->order->get_date_paid()->format('Y-m-d') : null,
            'taxExclusiveAmount' => $orderDocument->order->get_total() - $orderDocument->order->get_total_tax(),
            'taxAmount' => $orderDocument->order->get_total_tax(),
            'taxInclusiveAmount' => $orderDocument->order->get_total(),
            'prePaidAmount' => (float)$orderDocument->order->get_date_paid() ? $orderDocument->order->get_total() : 0,
            'payableAmount' => (float)$orderDocument->order->get_date_paid() ? 0 : $orderDocument->order->get_total(),
            'pdf' => base64_encode($orderDocument->get_pdf()),
        ];
    }
}
