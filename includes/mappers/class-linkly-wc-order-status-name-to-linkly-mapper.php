<?php

use WPO\WC\PDF_Invoices\Documents\Bulk_Document;
use WPO\WC\PDF_Invoices\Documents\Order_Document;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

class LinklyWCOrderStatusNameToLinklyMapper {
	/**
	 * Map the WC statusName to a Linkly statusName
	 *
	 * @param string $statusName
	 *
	 * @return false|string
	 */
	public static function mapStatusName( string $statusName ) {
		$linklyStatusName = $statusName;  // Start with the original value as default

		switch ($statusName) {
			case 'pending':
			case 'on-hold':
				$linklyStatusName = 'processing';
				break;

			case 'failed':
			case 'trash':
				$linklyStatusName = 'cancelled';
				break;

			// The following cases are redundant as they would set the value
			// to the same as the current value. They're included for clarity
			// but can be omitted.
			case 'refunded':
			case 'completed':
			case 'cancelled':
			case 'processing':
				// No change required.
				break;

			// Optionally, handle any other unexpected values.
			default:
				// Handle or log unexpected status
				break;
		}

		return $linklyStatusName;
	}
}
