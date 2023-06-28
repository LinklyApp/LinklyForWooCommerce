<?php

use Linkly\OAuth2\Client\Helpers\LinklySsoHelper;

class LinklyAddressActions
{
	/**
	 * @var LinklySsoHelper
	 */
	private LinklySsoHelper $ssoHelper;

	public function __construct(LinklySsoHelper $ssoHelper)
	{
		$this->ssoHelper = $ssoHelper;

		add_action('init', [$this, 'linkly_change_address_action']);
		add_action('init', [$this, 'linkly_change_address_callback']);

		add_action('woocommerce_before_checkout_form', [$this, 'linkly_check_and_update_addresses_if_changed']);
		add_action('woocommerce_before_edit_account_address_form', [$this, 'linkly_check_and_update_addresses_if_changed']);
	}

	/**
	 * The action to redirect to the Linkly SSO server to change the address
	 *
	 * @return void
	 * @throws Exception
	 */
	function linkly_change_address_action(): void {
		if (!isset($_GET['linkly_change_address_action'])) {
			return;
		}

		$_SESSION['url_to_return_to'] = get_site_url() . urldecode($_GET['linkly_change_address_action']);
		$params = [
			'clientId' => get_option('linkly_settings_app_key'),
			'redirect_uri' => get_site_url() . '?linkly_change_address_callback'
		];

		$this->ssoHelper->changeAddress($params);
		exit;
	}

	/**
	 * The callback action after the address has been changed on the Linkly SSO server
	 *
	 * @return void
	 */
	public function linkly_change_address_callback(): void {
		if (!isset($_GET['linkly_change_address_callback'])) {
			return;
		}

		try {
			$this->linkly_check_and_update_addresses_if_changed();
		} catch (Exception $e) {
			error_log($e->getMessage());
		}

		wp_redirect($_SESSION['url_to_return_to']);
		unset($_SESSION['url_to_return_to']);
		exit;
	}

	/**
	 * Checks if the address has been changed and updates it if it has
	 *
	 * @return void
	 * @throws Exception
	 */
	public function linkly_check_and_update_addresses_if_changed(): void {
		if (!is_user_logged_in()) {
			return;
		}

		$customer = new WC_Customer(get_current_user_id());
		if (!$customer->get_meta('linkly_user')) {
			return;
		}

		$addressData = [
			'billingAddressId' => $customer->get_meta('linkly_billing_id'),
			'billingAddressVersion' => $customer->get_meta('linkly_billing_version'),
			'shippingAddressId' => $customer->get_meta('linkly_shipping_id'),
			'shippingAddressVersion' => $customer->get_meta('linkly_shipping_version'),
		];

		try {
			if(!$this->ssoHelper->hasAddressBeenChanged($addressData))
			{
				return;
			}

			$linklyUser = $this->ssoHelper->getUser();
			$mappedCustomer = BCustomerToWCCustomerMapper::map($linklyUser);
			$customer->set_props($mappedCustomer);

			$customer->add_meta_data('linkly_user', true, true);
			$customer->update_meta_data('linkly_billing_id', $linklyUser->getBillingAddress()->getId());
			$customer->update_meta_data('linkly_billing_version', $linklyUser->getBillingAddress()->getVersion());
			$customer->update_meta_data('linkly_shipping_id', $linklyUser->getShippingAddress()->getId());
			$customer->update_meta_data('linkly_shipping_version', $linklyUser->getShippingAddress()->getVersion());

			$customer->save();
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
	}
}

new LinklyAddressActions(LinklyHelpers::instance()->getSsoHelper());
