<?php

use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Linkly\OAuth2\Client\Helpers\LinklyOrderHelper;
use Linkly\OAuth2\Client\Helpers\LinklySsoHelper;
use Linkly\OAuth2\Client\Provider\Exception\LinklyProviderException;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

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

    /**
     * @var string The status name for when the order is processing
     */
    private string $processing_status_name = "Processing";

    /**
     * @var string The status name for when the order is completed
     */
    private string $completed_status_name = "Completed";

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

	        $customer = new WC_Customer($order->get_user_id());

	        if (!$customer->get_meta('linkly_user')) {
		        return;
	        }

            $orderData = LinklyWCOrderToLinklyOrderMapper::mapOrder($order, $this->processing_status_name);
            $this->linklyOrderHelper->sendOrder($orderData);
            $order->add_meta_data('linkly_exported', gmdate("Y-m-d H:i:s") . ' +00:00');
            $order->save();
        } catch (LinklyProviderException $e) {
            linkly_dd($e->getResponseBody());
        } catch (IdentityProviderException $e) {
            linkly_dd([$e->getResponseBody()]);
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

            $orderData = LinklyWCOrderToLinklyOrderMapper::mapOrder($order, $this->completed_status_name);
            $this->linklyOrderHelper->sendOrder($orderData);

			if (linkly_is_pdf_invoices_plugin_active()) {
				$orderDocument = wcpdf_get_document( 'invoice', $order, true );
				$invoiceData   = LinklyWCInvoiceToLinklyInvoiceMapper::mapInvoice( $order, $orderDocument );
				$this->linklyOrderHelper->sendInvoice( $invoiceData );
			}

            $order->add_meta_data('linkly_exported', gmdate("Y-m-d H:i:s") . ' +00:00');
            $order->save();
        } catch (LinklyProviderException $e) {
            error_log($e->getMessage());
        } catch (Exception $e) {
            error_log($order, $e->getMessage());
        }
    }

    public function sync_current_wc_customer_invoices_to_linkly(): void
    {
		// TODO - Vervang ssohelper isauthenticated
        if (is_user_logged_in()) {
            $customer = new WC_Customer(get_current_user_id());

	        if (!$customer->get_meta('linkly_user')) {
		        return;
	        }
            linkly_sync_customer_invoices($customer);
        }
    }
}

new LinklyOrderActions(LinklyHelpers::instance()->getInvoiceHelper(), LinklyHelpers::instance()->getSsoHelper());
