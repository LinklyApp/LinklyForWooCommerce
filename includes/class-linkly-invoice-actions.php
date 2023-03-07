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
        try {
            $order = wc_get_order($order_id);
            $customer = new WC_Customer($order->get_user_id());

            if ($customer->get_meta('linkly_user') !== true) {
                return;
            }

            $invoice = wcpdf_get_document('invoice', $order, true);
            $invoiceData = WCOrderToLinklyInvoiceMapper::mapInvoice($invoice);
            $this->linklyInvoiceHelper->sendInvoice($invoiceData);
            $order->add_meta_data('linkly_exported', gmdate("Y-m-d H:i:s") . ' +00:00');
            $order->save();
        } catch (LinklyProviderException $e) {
            error_log($e->getResponseBody());
        } catch (Exception $e) {
            error_log($order, $e->getMessage());
        }
    }
}

new LinklyInvoiceActions(LinklyHelpers::instance()->getInvoiceHelper());
