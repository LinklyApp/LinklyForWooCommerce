<?php
defined('ABSPATH') or exit;

/** @var $onlyLink */

$linklyHelpers = LinklyHelpers::instance();

/** @var $onlyLink */

// if the user is logged in and is not a linkly user
// TODO - put in LinklyHelpers
if ($linklyHelpers->getSsoHelper()->isAuthenticated()) {
    return;
}
// if the user is logged in and is not a linkly user
$buttonUrl = './?linkly_link_account_action=' . urlencode($_SERVER['REQUEST_URI']);
$buttonText = LinklyLanguageHelper::instance()->get('link-account-button');

?>
<div id="linkly-login-button">
    <div class="linkly-button">
        <a href="<?= $buttonUrl ?>"><span><?= $buttonText ?></span>
            <img src="<?= LINKLY_FOR_WOOCOMMERCE_PLUGIN_URL . "assets/images/logo-horizontal.svg" ?>"></a>
    </div>
    <hr>
</div>