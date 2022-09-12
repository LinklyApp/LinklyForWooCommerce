<?php

use Memento\OAuth2\Client\Helpers\MementoInvoiceHelper;
use Memento\OAuth2\Client\Provider\Exception\MementoProviderException;
use Memento\OAuth2\Client\Provider\Invoice\MementoInvoice;
use Memento\OAuth2\Client\Provider\MementoProvider;

class MementoInvoiceActions
{
    /**
     * @var MementoInvoiceHelper
     */
    private $mementoInvoiceHelper;

    public function __construct(MementoInvoiceHelper $mementoInvoiceHelper)
    {
        $this->mementoInvoiceHelper = $mementoInvoiceHelper;
        add_action('woocommerce_order_status_processing', [$this, 'memento_get_order']);
    }

    function memento_get_order($order_id)
    {
        $order = wc_get_order($order_id);
        $user_id = $order->get_user_id();
        $customer = new WC_Customer($user_id);

        $memento_user_email= $customer->get_email();
        $invoiceData = WCOrderToMementoInvoiceMapper::map($order, $memento_user_email);

        try {
            $response = $this->mementoInvoiceHelper->sendInvoice($invoiceData);
        } catch (MementoProviderException $e) {
            dd($e->getResponseBody());
        }
    }
}

new MementoInvoiceActions(MementoHelpers::instance()->getInvoiceHelper());
