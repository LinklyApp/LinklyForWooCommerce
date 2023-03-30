<?php

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Linkly\OAuth2\Client\Helpers\LinklyOrderHelper;
use Linkly\OAuth2\Client\Helpers\LinklySsoHelper;
use Linkly\OAuth2\Client\Provider\Exception\LinklyProviderException;

class LinklyOrderActions
{
    /**
     * @var LinklyOrderHelper
     */
    private $linklyOrderHelper;

    /**
     * @var LinklySsoHelper
     */
    private $linklySsoHelper;

    public function __construct(LinklyOrderHelper $linklyOrderHelper,
                                LinklySsoHelper   $linklySsoHelper)
    {
        $this->linklyOrderHelper = $linklyOrderHelper;
        $this->linklySsoHelper = $linklySsoHelper;

        add_action('woocommerce_account_dashboard', [$this, 'sync_current_wc_customer_invoices_to_linkly'], 999);
        add_action('woocommerce_after_account_orders', [$this, 'sync_current_wc_customer_invoices_to_linkly'], 999);
        // TODO - change to status_completed
        add_action('woocommerce_order_status_processing', [$this, 'linkly_order_to_processing']);
        add_action('woocommerce_order_status_completed', [$this, 'linkly_order_to_completed']);
    }

    function linkly_order_to_processing($order_id)
    {
        try {
            $order = wc_get_order($order_id);
            $orderData = WCOrderToLinklyInvoiceMapper::mapOrder($order);
            $this->linklyOrderHelper->sendOrder($orderData);
            $order->add_meta_data('linkly_exported', gmdate("Y-m-d H:i:s") . ' +00:00');
            $order->save();
        } catch (LinklyProviderException $e) {
            dd($e->getResponseBody());
        } catch (IdentityProviderException $e) {
            dd([$e->getResponseBody()]);
        }
    }

    function linkly_order_to_completed($order_id)
    {
        try {
            $order = wc_get_order($order_id);
            $customer = new WC_Customer($order->get_user_id());

            if (!$customer->get_meta('linkly_user')) {
                return;
            }

            $orderDocument = wcpdf_get_document('invoice', $order, true);
            $orderData = WCOrderToLinklyInvoiceMapper::mapOrder($order, $orderDocument);
            $this->linklyOrderHelper->sendOrder($orderData);
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

new LinklyOrderActions(LinklyHelpers::instance()->getInvoiceHelper(), LinklyHelpers::instance()->getSsoHelper());
