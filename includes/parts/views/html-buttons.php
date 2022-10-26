<?php
defined( 'ABSPATH' ) or exit;
$buttonUrl      = "";
$buttonText     = "";
$linklyHelpers = LinklyHelpers::instance();
// if the user is logged in and is not a memento user
// TODO - put in MementoHelpers
if ( is_user_logged_in() && ! $linklyHelpers->getSsoHelper()->isAuthenticated() ) {
	$buttonUrl  = './?memento_link_account_action=' . urlencode( $_SERVER['REQUEST_URI'] );
	$buttonText = LinklyLanguageHelper::instance()->get( 'link-account-button' );
} else if ( ! $linklyHelpers->getSsoHelper()->isAuthenticated() ) {
	$buttonUrl  = './?memento_login_action=' . urlencode( $_SERVER['REQUEST_URI'] );
	$buttonText = LinklyLanguageHelper::instance()->get( 'login-button' );
} else if ( $linklyHelpers->getSsoHelper()->isAuthenticated() ) {
	$buttonUrl  = './?memento_change_address_action=' . urlencode( $_SERVER['REQUEST_URI'] );
    $buttonText = LinklyLanguageHelper::instance()->get( 'change-address-button' );
}

?>
<div class="linkly-button">
    <a href="<?= $buttonUrl ?>"><span><?= $buttonText ?></span>
        <img src="<?= MEMENTO_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/logo-horizontal.svg" ?>"></a>
</div>
