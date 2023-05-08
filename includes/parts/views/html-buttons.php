<?php
defined('ABSPATH') or exit;
$linklyHelpers = LinklyHelpers::instance();

if (!isset($onlyLinkButton)) {
	$onlyLinkButton = false;
}
?>

<?=
$linklyHelpers
	->getLinklyButtonHelper()
	->generateButton(is_user_logged_in(),
		is_wp_user_linkly_user(wp_get_current_user()),
		LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL,
		urlencode($_SERVER['REQUEST_URI']),
		get_option('linkly_button_style'),
		$onlyLinkButton);
?>