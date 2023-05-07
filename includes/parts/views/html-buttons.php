<?php
defined('ABSPATH') or exit;
$buttonUrl = "";
$buttonText = "";
$linklyHelpers = LinklyHelpers::instance();
$buttonStyle = get_option('linkly_button_style');
$logoStyle = $buttonStyle === 'purple' ? 'light' : 'dark';

/** @var $onlyLink */

// if the user is logged in and is not a linkly user
// TODO - put in LinklyHelpers

// TODO - Extra else if die in db kijkt of ingelogde wp user een linkly user is

// TODO - Als wp in db gaat kijken of wp user is ingelogd, meteen kijken of deze user een linkly user is

if (is_user_logged_in() && $linklyHelpers->getSsoHelper()->isAuthenticated()) {
    $buttonUrl = './?linkly_change_address_action=' . urlencode($_SERVER['REQUEST_URI']);
    $buttonText = LinklyLanguageHelper::instance()->get('change-address-button');
} else if (is_user_logged_in() && !$linklyHelpers->getSsoHelper()->isAuthenticated()) {
    $buttonUrl = './?linkly_link_account_action=' . urlencode($_SERVER['REQUEST_URI']);
    $buttonText = LinklyLanguageHelper::instance()->get('link-account-button');
} else {
    $buttonUrl = './?linkly_login_action=' . urlencode($_SERVER['REQUEST_URI']);
    $buttonText = LinklyLanguageHelper::instance()->get('login-button');
}

?>
<div id="linkly-login-button">
    <div class="linkly-button <?= $buttonStyle ?>">
        <a href="<?= $buttonUrl ?>"><span><?= $buttonText ?></span>
            <img src="<?= LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/logo-horizontal-" . $logoStyle . ".svg" ?>"></a>
    </div>
    <hr>
</div>