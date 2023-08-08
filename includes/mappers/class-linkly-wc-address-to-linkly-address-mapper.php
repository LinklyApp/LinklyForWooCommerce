<?php

use WPO\WC\PDF_Invoices\Documents\Bulk_Document;
use WPO\WC\PDF_Invoices\Documents\Order_Document;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class LinklyWCAddressToLinklyAddressMapper {
	/**
	 * Map the billing address from the order
	 *
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public static function mapBillingAddress( WC_Order $order ): array {
		$billingAddress = [
			'firstName'     => $order->get_billing_first_name(),
			'lastName'      => $order->get_billing_last_name(),
			'address1'      => $order->get_billing_address_1(),
			'address2'      => $order->get_billing_address_2(),
			'companyName'   => $order->get_billing_company(),
			'phoneNumber'   => $order->get_billing_phone(),
			'postcode'      => $order->get_billing_postcode(),
			'city'          => $order->get_billing_city(),
			'extraInfo'     => $order->get_customer_order_notes() ? $order->get_customer_order_notes()[0] : '',
			'countryAlpha2' => $order->get_billing_country(),
		];

		return $billingAddress;
	}

	/**
	 * Map the shipping address from the order
	 *
	 * @param WC_Order $order
	 *
	 * @return array
	 */
	public static function mapShippingAddress( WC_Order $order ): array {

		$shippingAddress = [
			'firstName'     => $order->get_shipping_first_name(),
			'lastName'      => $order->get_shipping_last_name(),
			'address1'      => $order->get_shipping_address_1(),
			'address2'      => $order->get_shipping_address_2(),
			'companyName'   => $order->get_shipping_company(),
			'phoneNumber'   => $order->get_shipping_phone(),
			'postcode'      => $order->get_shipping_postcode(),
			'city'          => $order->get_shipping_city(),
			'extraInfo'     => $order->get_customer_order_notes() ? $order->get_customer_order_notes()[0] : '',
			'countryAlpha2'  => $order->get_shipping_country(),
		];

		return $shippingAddress;
	}
}