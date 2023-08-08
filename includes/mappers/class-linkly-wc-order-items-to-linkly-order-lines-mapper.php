<?php

use WPO\WC\PDF_Invoices\Documents\Bulk_Document;
use WPO\WC\PDF_Invoices\Documents\Order_Document;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
class LinklyWCOrderItemsToLinklyOrderLinesMapper
{
	/**
	 * Generate the order lines from the order items
	 *
	 * @param WC_Order_Item[] $orderItems
	 *
	 * @return array
	 */
	public static function mapOrderItems(array $orderItems): array {
		$orderLines = [];
		$i = 1;
		foreach ($orderItems as $item) {
			$taxRatePercentage = current(WC_Tax::get_rates($item->get_tax_class(), WC()->customer)) ?
				current(WC_Tax::get_rates($item->get_tax_class(), WC()->customer))['rate'] : 0;
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

}