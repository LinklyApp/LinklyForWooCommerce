<?php
defined('ABSPATH') or exit;
$linklyHelpers = LinklyHelpers::instance();

$buttonUrl = "";
$buttonText = "";
$showButton = true;
$buttonStyle = get_option('linkly_button_style');
$logoStyle = $buttonStyle === 'primary' ? 'light' : 'dark';

if (!isset($onlyLinkButton)) {
	$onlyLinkButton = false;
}

if (is_user_logged_in() && is_wp_user_linkly_user(get_current_user_id()) && !$onlyLinkButton) {
    $buttonUrl = './?linkly_change_address_action=' . urlencode($_SERVER['REQUEST_URI']);
    $buttonText = LinklyLanguageHelper::instance()->get('change-address-button');
} else if (is_user_logged_in() && !is_wp_user_linkly_user(get_current_user_id())) {
    $buttonUrl = './?linkly_link_account_action=' . urlencode($_SERVER['REQUEST_URI']);
    $buttonText = LinklyLanguageHelper::instance()->get('link-account-button');
} else if (!$onlyLinkButton){
    $buttonUrl = './?linkly_login_action=' . urlencode($_SERVER['REQUEST_URI']);
    $buttonText = LinklyLanguageHelper::instance()->get('login-button');
} else {
    $showButton = false;
}
?>
<?=
!$showButton ?
    ""
    :
    "<div id='linkly-login-button'>
        <div class='linkly-button " . $buttonStyle . "'>
            <a href='" . $buttonUrl . "'><span>" .$buttonText . "</span>
                <img src=" . LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL .  "assets/images/logo-horizontal-" . $logoStyle . "'.svg' alt='Linkly'></a>
        </div>
    </div>"
?>