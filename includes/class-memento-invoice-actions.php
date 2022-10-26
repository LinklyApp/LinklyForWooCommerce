<?php

use Memento\OAuth2\Client\Helpers\MementoInvoiceHelper;
use Memento\OAuth2\Client\Provider\Exception\MementoProviderException;

class MementoInvoiceActions
{
    /**
     * @var MementoInvoiceHelper
     */
    private $mementoInvoiceHelper;

    public function __construct(MementoInvoiceHelper $mementoInvoiceHelper)
    {
        $this->mementoInvoiceHelper = $mementoInvoiceHelper;

        // TODO - change to status_completed
        add_action('woocommerce_order_status_processing', [$this, 'memento_get_invoice']);
    }

    function memento_get_invoice($order_id)
    {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();

        $invoice = wcpdf_get_document('invoice', $order, true);

        try {
            $invoiceData = WCOrderToMementoInvoiceMapper::mapInvoice($invoice);
        } catch (Exception $e) {
            dd($e->getMessage());
        }

        try {
            $response = $this->mementoInvoiceHelper->sendInvoice($invoiceData);
        } catch (MementoProviderException $e) {
            dd($e->getResponseBody());
        }
    }
}

new MementoInvoiceActions(LinklyHelpers::instance()->getInvoiceHelper());
