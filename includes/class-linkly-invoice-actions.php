<?php

use Linkly\OAuth2\Client\Helpers\LinklyInvoiceHelper;
use Linkly\OAuth2\Client\Provider\Exception\LinklyProviderException;

class LinklyInvoiceActions
{
    /**
     * @var LinklyInvoiceHelper
     */
    private $linklyInvoiceHelper;

    public function __construct(LinklyInvoiceHelper $linklyInvoiceHelper)
    {
        $this->linklyInvoiceHelper = $linklyInvoiceHelper;

        // TODO - change to status_completed
        add_action('woocommerce_order_status_processing', [$this, 'linkly_get_invoice']);
    }

    function linkly_get_invoice($order_id)
    {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();

        $invoice = wcpdf_get_document('invoice', $order, true);

        try {
            $invoiceData = WCOrderToLinklyInvoiceMapper::mapInvoice($invoice);
        } catch (Exception $e) {
            dd($e->getMessage());
        }

        try {
            $response = $this->linklyInvoiceHelper->sendInvoice($invoiceData);
        } catch (LinklyProviderException $e) {
            dd($e->getResponseBody());
        }
    }
}

new LinklyInvoiceActions(LinklyHelpers::instance()->getInvoiceHelper());
