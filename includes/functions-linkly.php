<?php

use Linkly\OAuth2\Client\Provider\Exception\LinklyProviderException;
use Linkly\OAuth2\Client\Provider\User\LinklyUser;

function createOrUpdateLinklyCustomer(LinklyUser $linklyUser, WP_User $currentUser = null)
{
    $mappedCustomer = BCustomerToWCCustomerMapper::map($linklyUser);
    $customer = new WC_Customer($currentUser->ID);

    $customer->set_props($mappedCustomer);

    $customer->add_meta_data('linkly_user', true, true);
    $customer->update_meta_data('linkly_billing_id', $linklyUser->getBillingAddress()->getId());
    $customer->update_meta_data('linkly_billing_version', $linklyUser->getBillingAddress()->getVersion());
    $customer->update_meta_data('linkly_shipping_id', $linklyUser->getShippingAddress()->getId());
    $customer->update_meta_data('linkly_shipping_version', $linklyUser->getShippingAddress()->getVersion());

    $customer->save();

    login_linkly_user($customer);
}

function attachWCCustomerToLinkly(LinklyUser $linklyUser, WP_User $currentUser)
{
    $mappedCustomer = BCustomerToWCCustomerMapper::map($linklyUser);

    if ($currentUser->ID !== 0 && $linklyUser->getEmail() !== $currentUser->user_email) {
        $currentUser->user_email = $linklyUser->getEmail();
        $response = wp_update_user($currentUser);
        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }
    }

    $customer = new WC_Customer($currentUser->ID);

    $customer->set_props($mappedCustomer);
    $customer->add_meta_data('linkly_user', true);
    $customer->save();

    sync_customer_invoices_with_linkly($customer);
}

/**
 * @param WC_Customer $customer
 * @return void
 */
function sync_customer_invoices_with_linkly(WC_Customer $customer): void
{
    $args = array(
        'limit' => -1, // Limit of orders to retrieve
        'meta_key' => 'linkly_exported', // Postmeta key field
        'meta_compare' => 'NOT EXISTS',
        'customer_id' => $customer->get_id(), // User ID
        'return' => 'objects', // Possible values are ‘ids’ and ‘objects’.
    );

    $orders = wc_get_orders($args);

    $linklyOrderHelper = LinklyHelpers::instance()->getInvoiceHelper();

    foreach ($orders as $order) {
        try {
            $invoice = wcpdf_get_document('invoice', $order, true);
            $invoiceData = WCInvoiceToLinklyInvoiceMapper::mapInvoice($order, $invoice);
            $linklyOrderHelper->sendInvoice($invoiceData);
            $order->add_meta_data('linkly_exported', gmdate("Y-m-d H:i:s") . ' +00:00');
            $order->save();
        } catch (LinklyProviderException $e) {
            error_log($e->getResponseBody());
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }
}

function login_linkly_user(WC_Customer $customer)
{
    wp_clear_auth_cookie();
    wc_set_customer_auth_cookie($customer->get_id());

    if (is_billing_address_equal_to_shipping_address($customer)) {
        add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );
    }
}

function is_billing_address_equal_to_shipping_address(WC_Customer $customer) : bool
{
    return $customer->get_billing_address_1() === $customer->get_shipping_address_1()
        && $customer->get_billing_first_name() === $customer->get_shipping_first_name()
        && $customer->get_billing_last_name() === $customer->get_shipping_last_name()
        ;
}






