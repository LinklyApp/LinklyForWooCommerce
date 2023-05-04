<?php
defined('ABSPATH') or exit;
$buttonUrl = "";
$buttonText = "";
$linklyHelpers = LinklyHelpers::instance();

/** @var $onlyLink */

// if the user is logged in and is not a linkly user
// TODO - put in LinklyHelpers

// TODO - Vervang ssohelper isauthenticated
if (false) {
    return;
}
// if the user is logged in and is not a linkly user
$buttonUrl = './?linkly_link_account_action=' . urlencode($_SERVER['REQUEST_URI']);
$buttonText = LinklyLanguageHelper::instance()->get('link-account-button');
$logoStyle = get_option('linkly_button_style') === 'purple' ? 'light' : 'dark';

?>
<div id="linkly-login-button">
    <div class="linkly-button <?= get_option('linkly_button_style') ?>">
        <a href="<?= $buttonUrl ?>"><span><?= $buttonText ?></span>
            <img src="<?= LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/logo-horizontal-" . $logoStyle . ".svg" ?>"></a>
    </div>
    <hr>
</div>