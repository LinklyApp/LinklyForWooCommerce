<?php

use Linkly\OAuth2\Client\Helpers\LinklyInvoiceHelper;
use Linkly\OAuth2\Client\Helpers\LinklySsoHelper;
use Linkly\OAuth2\Client\Provider\Exception\LinklyProviderException;

class LinklyInvoiceActions
{
    /**
     * @var LinklyInvoiceHelper
     */
    private $linklyInvoiceHelper;

    /**
     * @var LinklySsoHelper
     */
    private $linklySsoHelper;

    public function __construct(LinklyInvoiceHelper $linklyInvoiceHelper,
                                LinklySsoHelper     $linklySsoHelper)
    {
        $this->linklyInvoiceHelper = $linklyInvoiceHelper;
        $this->linklySsoHelper = $linklySsoHelper;

        add_action('woocommerce_account_dashboard', [$this, 'sync_current_wc_customer_invoices_to_linkly'], 999);
        add_action('woocommerce_after_account_orders', [$this, 'sync_current_wc_customer_invoices_to_linkly'], 999);
        // TODO - change to status_completed
        add_action('woocommerce_order_status_processing', [$this, 'linkly_get_invoice']);
    }

    function linkly_get_invoice($order_id)
    {
        try {
            $order = wc_get_order($order_id);
            $customer = new WC_Customer($order->get_user_id());

            if (!$customer->get_meta('linkly_user')) {
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

    public function sync_current_wc_customer_invoices_to_linkly(): void
    {
        if (is_user_logged_in() && $this->linklySsoHelper->isAuthenticated()) {
            $customer = new WC_Customer(get_current_user_id());
            sync_customer_invoices_with_linkly($customer);
        }
    }
}

new LinklyInvoiceActions(LinklyHelpers::instance()->getInvoiceHelper(), LinklyHelpers::instance()->getSsoHelper());
